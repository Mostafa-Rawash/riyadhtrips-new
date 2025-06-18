(function ($) {

    new Vue({

        el: "#bravo_tour_book_app",

        data: {

            id: "",

            extra_price: [],

            person_types: [],

            packages: [],

            message: {

                content: "",

                type: false,

            },

            html: "",

            onSubmit: false,

            start_date: "",

            start_date_html: "",

            step: 1,

            guests: 0,

            price: 0,

            total_price_before_fee: 0,

            total_price_fee: 0,

            max_guests: 10,

            start_date_obj: "",

            duration: 0,

            allEvents: [],

            buyer_fees: [],

            discount_by_people: [],

            discount_by_people_output: [],

            selected_time_slot: null,

            time_slot_id: null,

            start_time: null,

            loading_time_slots: false,

            available_time_slots: [],

            sold_out_slots: [],

            show_sold_out_slots: false,

            is_form_enquiry_and_book: false,

            enquiry_type: "book",

            enquiry_is_submit: false,

            enquiry_name: "",

            enquiry_email: "",

            enquiry_phone: "",

            enquiry_travel_from: "",

            enquiry_adult: "",

            enquiry_children: "",

            enquiry_note: "",

            // 🔥 Enhanced Time slots properties
            enable_time_slots: false,
            time_slots_debug_mode: false,
            time_slots_last_update: null,
            time_slots_retry_count: 0,
            time_slots_max_retries: 3,
            time_slots_force_update: false,
            start_date_formatted: "",

        },

        watch: {

            extra_price: {

                handler: function f() {

                    this.step = 1;

                },

                deep: true,

            },

            guests() {

                this.step = 1;

                // 🔥 ENHANCED: Reload time slots when guest count changes
                if (this.enable_time_slots && this.start_date) {
                    // console.log('📊 Guest count changed, reloading time slots');
                    var me = this;
                    me.time_slots_retry_count = 0; // Reset retry count
                    setTimeout(function() {
                        me.loadTimeSlotsWithRetry(me.start_date);
                    }, 300);
                }

            },

            person_types: {

                handler: function f() {

                    this.step = 1;

                    // 🔥 ENHANCED: Reload time slots when person types change
                    if (this.enable_time_slots && this.start_date) {
                        // console.log('👥 Person types changed, reloading time slots');
                        var me = this;
                        me.time_slots_retry_count = 0; // Reset retry count
                        setTimeout(function() {
                            me.loadTimeSlotsWithRetry(me.start_date);
                        }, 300);
                    }

                },

                deep: true,

            },

            start_date() {

                this.step = 1;

                var me = this;

                var startDate = new Date(me.start_date).getTime();

                for (var ix in me.allEvents) {

                    var item = me.allEvents[ix];

                    var cur_date = new Date(item.start).getTime();

                    if (cur_date === startDate) {

                        if (item.person_types != null) {

                            me.person_types = item.person_types.map(function(pt) {

                                return {

                                    ...pt,

                                    display_price: window.bravo_format_money ? window.bravo_format_money(pt.price) : '�' + pt.price

                                };

                            });

                        } else {

                            me.person_types = null;

                        }

                        me.max_guests = parseInt(item.max_guests);

                        me.price = parseFloat(item.price);

                    }

                }

                // 🔥 ENHANCED: Load time slots when date changes with better error handling
                if (me.enable_time_slots && me.start_date) {
                    // console.log('📅 Date changed, loading time slots for:', me.start_date);
                    me.time_slots_retry_count = 0; // Reset retry count
                    setTimeout(function() {
                        me.loadTimeSlotsWithRetry(me.start_date);
                    }, 100);
                }

            },

            packages: {

                handler: function f() {

                    this.step = 1;

                },

                deep: true,

            },

            selected_time_slot: {

                handler: function(val) {

                    if (val) {

                        this.time_slot_id = val.id;

                        this.start_time = val.start_time;

                        this.step = 1; // Trigger recalculation

                    }

                },

                deep: true

            }

        },

        computed: {

            total_price: function () {

                var me = this;

                // Check if selected guests exceed time slot capacity
                if (me.enable_time_slots && me.selected_time_slot) {
                    var total_guests = this.getGuestCount();
                    if (total_guests > me.selected_time_slot.remaining_capacity) {
                        me.clearTimeSlot();
                        toastr.error('Selected number of guests exceeds available capacity. Time slot selection has been cleared.');
                        return 0;
                    }
                }

                if (me.start_date !== "") {

                    var total = 0;

                    var total_guests = this.getGuestCount();

                    var startDate = new Date(me.start_date).getTime();
                    
                    // Find the selected date event from allEvents
                    var selectedEvent = null;
                    for (var i = 0; i < me.allEvents.length; i++) {
                        var event = me.allEvents[i];
                        var eventDate = new Date(event.start).getTime();
                        if (eventDate === startDate) {
                            selectedEvent = event;
                            break;
                        }
                    }
                    
                    // Use person types from the selected event if available
                    if (selectedEvent && selectedEvent.person_types != null && selectedEvent.person_types.length > 0) {
                        for (var ix in selectedEvent.person_types) {
                            var person_type = selectedEvent.person_types[ix];
                            var personPrice = parseFloat(person_type.price);
                            var personNumber = 0;
                            for (var jx in me.person_types) {
                                if (me.person_types[jx].name === person_type.name) {
                                    personNumber = parseInt(me.person_types[jx].number);
                                    break;
                                }
                            }
                            total += personPrice * personNumber;
                            total_guests += personNumber;
                        }
                    } else if (me.person_types != null && me.person_types.length > 0) {
                        for (var ix in me.person_types) {
                            var person_type = me.person_types[ix];
                            var personPrice = parseFloat(person_type.price);
                            total += personPrice * parseInt(person_type.number);
                            total_guests += parseInt(person_type.number);
                        }
                    } else {
                        // for default when no person types are defined
                        total_guests = me.guests;
                        // Use price from selected event if available, otherwise use me.price
                        var price = selectedEvent ? parseFloat(selectedEvent.price) : me.price;
                        total += me.guests * price;
                    }

                    // Add package prices if selected
                    if (me.packages != null && me.packages.length > 0) {
                        for (var ix in me.packages) {
                            var packages = me.packages[ix];
                            // Only add price if package is selected (enable property is true or 1)
                            if (packages.enable == 1 || packages.enable === true) {
                                total += parseFloat(packages.price);
                                // console.log("Package selected:", packages.name, "Price:", packages.price);
                            }
                        }
                    }


                    for (var ix in me.extra_price) {

                        var item = me.extra_price[ix];

                        if (!item.price) continue;

                        var type_total = 0;

                        if (item.enable == 1) {

                            switch (item.type) {

                                case "one_time":

                                    type_total += parseFloat(item.price);

                                    break;

                                case "per_hour":

                                    if (me.duration > 0) {

                                        type_total +=

                                            parseFloat(item.price) *

                                            parseFloat(me.duration);

                                    }

                                    break;

                                case "per_day":

                                    if (me.duration > 0) {

                                        type_total +=

                                            parseFloat(item.price) *

                                            Math.ceil(

                                                parseFloat(me.duration) / 24

                                            );

                                    }

                                    break;

                            }

                            if (typeof item.per_person !== "undefined") {

                                type_total = type_total * total_guests;

                            }

                            total += type_total;

                        }

                    }

                    let discount_by_people = [];

                    if (me.discount_by_people) {

                        me.discount_by_people.forEach((type) => {

                            if (

                                type.from <= total_guests &&

                                (!type.to || type.to >= total_guests)

                            ) {

                                let type_total = 0;

                                switch (type.type) {

                                    case "fixed":

                                        type_total = type.amount;

                                        break;

                                    case "percent":

                                        type_total =

                                            (total / 100) * type.amount;

                                        break;

                                }

                                total -= type_total;

                                type.total = type_total;

                                discount_by_people.push(type);

                            }

                        });

                    }

                    me.discount_by_people_output = discount_by_people;



                    this.total_price_before_fee = total;



                    var total_fee = 0;

                    for (var ix in me.buyer_fees) {

                        var item = me.buyer_fees[ix];



                        if (!item.price) continue;



                        //for Fixed

                        var fee_price = parseFloat(item.price);



                        //for Percent

                        if (

                            typeof item.unit !== "undefined" &&

                            item.unit === "percent"

                        ) {

                            fee_price = (total / 100) * fee_price;

                        }



                        if (typeof item.per_person !== "undefined") {

                            fee_price = fee_price * total_guests;

                        }

                        total_fee += fee_price;

                    }

                    total += total_fee;

                    this.total_price_fee = total_fee;



                    return total;

                }

                return 0;

            },

            total_price_html: function () {

                if (!this.total_price) return "";

                return window.bravo_format_money(this.total_price);

            },

            daysOfWeekDisabled() {

                var res = [];



                for (var k in this.open_hours) {

                    if (

                        typeof this.open_hours[k].enable == "undefined" ||

                        this.open_hours[k].enable != 1

                    ) {

                        if (k == 7) {

                            res.push(0);

                        } else {

                            res.push(k);

                        }

                    }

                }



                return res;

            },

            pay_now_price: function () {

                if (this.is_deposit_ready) {

                    var total_price_depossit = 0;



                    var tmp_total_price = this.total_price;

                    var deposit_fomular = this.deposit_fomular;

                    if (deposit_fomular === "deposit_and_fee") {

                        tmp_total_price = this.total_price_before_fee;

                    }



                    switch (this.deposit_type) {

                        case "percent":

                            total_price_depossit =

                                (tmp_total_price * this.deposit_amount) / 100;

                            break;

                        default:

                            total_price_depossit = this.deposit_amount;

                    }

                    if (deposit_fomular === "deposit_and_fee") {

                        total_price_depossit =

                            total_price_depossit + this.total_price_fee;

                    }



                    return total_price_depossit;

                }

                return this.total_price;

            },

            pay_now_price_html: function () {

                return window.bravo_format_money(this.pay_now_price);

            },

            is_deposit_ready: function () {

                if (this.deposit && this.deposit_amount) return true;

                return false;

            },

        },

        created: function () {

            for (var k in bravo_booking_data) {

                this[k] = bravo_booking_data[k];

            }

        },

        mounted() {

            var me = this;

            var options = {

                singleDatePicker: true,

                showCalendar: false,

                sameDate: true,

                autoApply: true,

                disabledPast: true,

                dateFormat: bookingCore.date_format,

                enableLoading: true,

                showEventTooltip: true,

                classNotAvailable: ["disabled", "off"],

                disableHightLight: true,

                minDate: this.minDate,

                opens: bookingCore.rtl ? "right" : "left",

                locale: {

                    direction: bookingCore.rtl ? "rtl" : "ltr",

                    firstDay: daterangepickerLocale.first_day_of_week,

                },

                isInvalidDate: function (date) {

                    for (var k = 0; k < me.allEvents.length; k++) {

                        var item = me.allEvents[k];

                        if (item.start == date.format("YYYY-MM-DD")) {

                            return item.active ? false : true;

                        }

                    }

                    return false;

                },

                addClassCustom: function (date) {

                    for (var k = 0; k < me.allEvents.length; k++) {

                        var item = me.allEvents[k];

                        if (

                            item.start == date.format("YYYY-MM-DD") &&

                            item.classNames !== undefined

                        ) {

                            var class_names = "";

                            for (var i = 0; i < item.classNames.length; i++) {

                                var classItem = item.classNames[i];

                                class_names += " " + classItem;

                            }

                            return class_names;

                        }

                    }

                    return "";

                },

            };



            if (typeof daterangepickerLocale == "object") {

                options.locale = _.merge(daterangepickerLocale, options.locale);

            }

            this.$nextTick(function () {

                $(this.$refs.start_date)

                    .daterangepicker(options)

                    .on("apply.daterangepicker", function (ev, picker) {

                        me.start_date = picker.startDate.format("YYYY-MM-DD");

                        me.start_date_html = picker.startDate.format(

                            bookingCore.date_format

                        );

                    })

                    .on("update-calendar", function (e, obj) {

                        me.fetchEvents(

                            obj.leftCalendar.calendar[0][0],

                            obj.leftCalendar.calendar[5][6]

                        );

                    });

            });

        },

        methods: {

            handleTotalPrice: function () {},

            // 🔥 ENHANCED: Select time slot with better validation and feedback
            selectTimeSlot(slot) {
                if (!slot) {
                    console.warn('⚠️ Invalid slot provided to selectTimeSlot');
                    return;
                }
                
                // Validate slot availability for current guest count
                const guests = this.getGuestCount();
                if (slot.remaining_capacity < guests) {
                    this.showTimeSlotError('This time slot only has ' + slot.remaining_capacity + ' spots available, but you need ' + guests + ' spots.');
                    return;
                }
                
                // Clear any previous slot
                this.clearTimeSlot();
                
                // Direct assignment since properties are declared upfront
                this.selected_time_slot = slot;
                this.time_slot_id = slot.id;
                this.start_time = slot.start_time;
                
                // console.log('✅ Time slot selected:', {
                //     id: slot.id,
                //     time: slot.formatted_time,
                //     remaining_capacity: slot.remaining_capacity
                // });
                
                // Force step update to trigger price recalculation
                this.step = 1;
                
                // Show success feedback
                this.showTimeSlotSuccess('Time slot ' + slot.formatted_time + ' selected successfully!');
            },
            
            // 🔥 ENHANCED: Clear time slot with logging
            clearTimeSlot() {
                // console.log('🗽 Clearing time slot selection');
                this.selected_time_slot = null;
                this.time_slot_id = null;
                this.start_time = null;
                this.step = 1;
            },
            
            formatTime(time) {
                if (!time) return '';
                return moment(time, 'HH:mm:ss').format('HH:mm');
            },

            fetchEvents(start, end) {

                var me = this;

                var data = {

                    start: start.format("YYYY-MM-DD"),

                    end: end.format("YYYY-MM-DD"),

                    id: bravo_booking_data.id,

                    for_single: 1,

                };

                console.log(data);



                $.ajax({

                    url: bravo_booking_i18n.load_dates_url,

                    dataType: "json",

                    type: "get",

                    data: data,

                    beforeSend: function () {

                        $(".daterangepicker").addClass("loading");

                    },

                    success: function (json) {

                        me.allEvents = json;

                        var drp = $(me.$refs.start_date).data(

                            "daterangepicker"

                        );

                        drp.allEvents = json;

                        drp.renderCalendar("left");

                        if (!drp.singleDatePicker) {

                            drp.renderCalendar("right");

                        }
                        console.log(json);

                        $(".daterangepicker").removeClass("loading");

                    },

                    error: function (e) {

                        console.log(e);

                        console.log("Can not get availability");

                    },

                });

            },

            calculateTotal() {

                if (this.selectedPersonType) {

                    this.totalAmount =

                        parseFloat(this.selectedPersonType.display_price) *

                        parseInt(this.selectedPersonType.number || 1);

                } else {

                    this.totalAmount = 0;

                }

            },

            formatMoney: function (m) {

                return window.bravo_format_money(m);

            },


            getGuestCount: function() {
                let totalGuests = 0;
                
                // Check if person types exist and have values
                if (this.person_types && this.person_types.length) {
                    totalGuests = this.person_types.reduce((total, type) => {
                        const count = parseInt(type.number) || parseInt(type.min) || 0;
                        return total + count;
                    }, 0);
                    
                    // If total is 0, set default values based on min requirements
                    if (totalGuests === 0) {
                        this.person_types.forEach(type => {
                            const minRequired = parseInt(type.min) || 0;
                            if (minRequired > 0) {
                                type.number = minRequired;
                                totalGuests += minRequired;
                            }
                        });
                    }
                }
                
                // If still no guests from person types, use regular guests field
                if (totalGuests === 0) {
                    totalGuests = parseInt(this.guests) || 1;
                    
                    // Ensure guests field has a default value if it's 0 or undefined
                    if (!this.guests || this.guests === 0) {
                        this.guests = 1;
                        totalGuests = 1;
                    }
                }
                
                return totalGuests;
            },

            // 🔥 ENHANCED: Main time slots loading method with retry mechanism
            loadTimeSlotsWithRetry: function(date) {
                var me = this;
                
                // Increment retry count
                me.time_slots_retry_count++;
                
                if (me.time_slots_retry_count > me.time_slots_max_retries) {
                    // console.error('❌ Max retries reached for time slots loading');
                    me.showTimeSlotError('Unable to load time slots after multiple attempts. Please refresh the page.');
                    return;
                }
                
                console.log('🔄 Loading time slots (attempt ' + me.time_slots_retry_count + '/' + me.time_slots_max_retries + ')');
                me.loadTimeSlots(date);
            },
            
            // 🔥 ENHANCED: Core time slots loading method
            loadTimeSlots: function(date) {
                if (!this.enable_time_slots) {
                    console.log('⚠️ Time slots are disabled for this tour');
                    return;
                }
                
                if (!date) {
                    console.log('⚠️ No date provided for time slots loading');
                    return;
                }
                
                console.log('📡 Loading time slots for:', date, 'Tour ID:', this.id);
                
                var me = this;
                
                // Set loading state
                me.loading_time_slots = true;
                me.time_slots_last_update = new Date().toISOString();
                
                // Clear existing slots
                me.available_time_slots = [];
                me.sold_out_slots = [];
                
                const guests = me.getGuestCount();
                
                // Try multiple API URLs as fallback
                const apiUrls = [
                    (typeof bravo_booking_i18n !== 'undefined' && bravo_booking_i18n.load_time_slots_url) || null,
                    '/api/tour/time-slots/available',
                    '/api/tour/time-slots/fallback'
                ].filter(url => url);
                
                // console.log('🌐 Trying API URLs:', apiUrls);
                
                let currentUrlIndex = 0;
                
                function tryNextUrl() {
                    if (currentUrlIndex >= apiUrls.length) {
                        // console.error('❌ All API URLs failed');
                        me.loading_time_slots = false;
                        me.showTimeSlotError('Unable to load time slots. Please try again or contact support.');
                        return;
                    }
                    
                    const apiUrl = apiUrls[currentUrlIndex];
                    // console.log('🔗 Trying URL ' + (currentUrlIndex + 1) + ':', apiUrl);
                    
                    $.ajax({
                        url: apiUrl,
                        method: 'GET',
                        dataType: 'json',
                        timeout: 10000, // 10 second timeout
                        data: {
                            tour_id: me.id,
                            date: date,
                            guests: guests,
                            _t: Date.now(),
                            debug: me.time_slots_debug_mode ? 1 : 0
                        },
                        success: function(response) {
                            // console.log('✅ Time slots response from URL ' + (currentUrlIndex + 1) + ':', response);
                            
                            me.loading_time_slots = false;
                            me.time_slots_retry_count = 0; // Reset retry count on success
                            
                            if (response && response.success && response.data && response.data.time_slots) {
                                me.processTimeSlotsResponse(response.data.time_slots, guests);
                            } else {
                                // console.log('⚠️ Invalid response format:', response);
                                me.showTimeSlotWarning('No time slots available for the selected date.');
                            }
                        },
                        error: function(xhr, status, error) {
                            // console.error('❌ Time slots error for URL ' + (currentUrlIndex + 1) + ':', {
                            //     status: xhr.status,
                            //     statusText: xhr.statusText,
                            //     error: error,
                            //     responseText: xhr.responseText
                            // });
                            
                            currentUrlIndex++;
                            if (currentUrlIndex < apiUrls.length) {
                                // console.log('🔄 Trying next URL...');
                                setTimeout(tryNextUrl, 1000); // Wait 1 second before trying next URL
                            } else {
                                me.loading_time_slots = false;
                                
                                // Different error messages based on status
                                if (xhr.status === 404) {
                                    me.showTimeSlotError('Time slots feature not available. Please contact support.');
                                } else if (xhr.status === 500) {
                                    me.showTimeSlotError('Server error loading time slots. Please try again later.');
                                } else if (status === 'timeout') {
                                    me.showTimeSlotError('Request timed out. Please check your connection and try again.');
                                } else {
                                    me.showTimeSlotError('Failed to load time slots. Please try again or contact support.');
                                }
                            }
                        }
                    });
                }
                
                // Start trying URLs
                tryNextUrl();
            },
            
            // 🔥 NEW: Process time slots response with GUARANTEED Vue reactivity
           // 🛠️ ENHANCED processTimeSlotsResponse method - Replace the existing success call:
processTimeSlotsResponse: function(slots, requestedGuests) {
    var me = this;
    
    // console.log('🛠️ Processing', slots.length, 'time slots for', requestedGuests, 'guests');
    
    // Filter available slots
    const availableSlots = slots.filter(function(slot) {
        return !slot.is_sold_out && slot.remaining_capacity > 0;
    });
    
    // Filter sold out slots
    const soldOutSlots = slots.filter(function(slot) {
        return slot.is_sold_out || slot.remaining_capacity === 0;
    });
    
    // Filter slots that can accommodate requested guests
    const bookableSlots = availableSlots.filter(function(slot) {
        return slot.remaining_capacity >= requestedGuests;
    });
    
    // ✅ CRITICAL: Update Vue data with proper reactivity
    this.$set(this, 'available_time_slots', availableSlots);
    this.$set(this, 'sold_out_slots', soldOutSlots);
    
    // console.log('📊 Time slots processed:', {
    //     total: slots.length,
    //     available: availableSlots.length,
    //     soldOut: soldOutSlots.length,
    //     bookableForGuests: bookableSlots.length,
    //     requestedGuests: requestedGuests
    // });
    
    // 🔥 CRITICAL: Force Vue update with multiple strategies
    this.$forceUpdate();
    
    // Force re-render with nextTick
    this.$nextTick(function() {
        // console.log('🔄 Vue nextTick completed - slots should be visible now');
        // console.log('🔍 Current available_time_slots:', me.available_time_slots.length);
        // console.log('🔍 Current sold_out_slots:', me.sold_out_slots.length);
        
        // ✅ ENHANCED: Show appropriate success message with UI update
        if (availableSlots.length === 0) {
            me.showTimeSlotWarning('No time slots available for the selected date.');
        } else if (bookableSlots.length === 0) {
            me.showTimeSlotWarning('No time slots can accommodate ' + requestedGuests + ' guests. Available slots have limited capacity.');
        } else {
            // 🎯 THIS IS WHERE THE SUCCESS MESSAGE SHOULD UPDATE THE UI
            me.showTimeSlotSuccess('Found ' + bookableSlots.length + ' available time slot' + (bookableSlots.length === 1 ? '' : 's') + ' for your group!');
            
            // Additional UI enhancement for successful loading
            setTimeout(function() {
                // Highlight the available slots
                const slotElements = document.querySelectorAll('.time-slot-available, .time-slot:not(.sold-out)');
                slotElements.forEach(function(element, index) {
                    setTimeout(function() {
                        element.classList.add('animate-in');
                    }, index * 100); // Stagger animation
                });
            }, 300);
        }
        
        // Auto-select if only one bookable slot
        if (bookableSlots.length === 1) {
            // console.log('🎯 Auto-selecting the only available slot');
            setTimeout(function() {
                me.selectTimeSlot(bookableSlots[0]);
            }, 500);
        }
    });
},


            // 🔥 Add helper methods for time slots
            isSlotBookableForCurrentGuests: function(slot) {
                if (!slot) return false;
                const guests = this.getGuestCount();
                return !slot.is_sold_out && slot.remaining_capacity >= guests;
            },

            handleTimeSlotClick: function(slot) {
                if (this.isSlotBookableForCurrentGuests(slot)) {
                    this.selectTimeSlot(slot);
                }
            },

            getSlotTooltip: function(slot) {
                const guests = this.getGuestCount();
                if (slot.is_sold_out) return 'Fully booked';
                if (slot.remaining_capacity < guests) return 'Only ' + slot.remaining_capacity + ' spots available';
                return slot.remaining_capacity + ' spots available';
            },

            getSlotAriaLabel: function(slot) {
                return 'Time slot ' + this.formatTime(slot.start_time) + ', ' + slot.remaining_capacity + ' spots available';
            },
            
            // 🔥 NEW: User feedback methods
            showTimeSlotSuccess: function(message) {
    // console.log('✅ Success:', message);
    
    // 1. Update Vue reactive message system with proper structure
    this.message = {
        content: message,
        type: true,  // true = success type
        status: true
    };
    
    // 2. Force Vue reactivity update
    this.$forceUpdate();
    
    // 3. Ensure DOM update with nextTick
    var me = this;
    this.$nextTick(function() {
        // console.log('🔄 Vue nextTick completed - success message should be visible');
        
        // 4. Additional UI feedback mechanisms
        
        // Show toastr notification if available
        if (typeof toastr !== 'undefined') {
            toastr.success(message, 'Time Slot Success', {
                timeOut: 4000,
                closeButton: true,
                positionClass: 'toast-top-center'
            });
        }
        
        // Add visual feedback to time slots container
        const timeSlotsContainer = document.querySelector('.time-slots-container, .available-time-slots, [data-time-slots]');
        if (timeSlotsContainer) {
            timeSlotsContainer.classList.add('success-highlight');
            
            // Remove highlight after animation
            setTimeout(function() {
                timeSlotsContainer.classList.remove('success-highlight');
            }, 2000);
        }
        
        // Trigger custom event for other components
        if (typeof window.CustomEvent !== 'undefined') {
            const event = new CustomEvent('timeSlotSuccess', {
                detail: { message: message }
            });
            document.dispatchEvent(event);
        }
        
        // // Update page title temporarily for additional feedback
        // const originalTitle = document.title;
        // document.title = '✅ ' + message;
        // setTimeout(function() {
        //     document.title = originalTitle;
        // }, 3000);
    });
    
    // 5. Auto-clear message after delay
    setTimeout(function() {
        if (me.message.content === message) {
            me.message = {
                content: '',
                type: false,
                status: false
            };
            me.$forceUpdate();
        }
    }, 5000);
},
            
            showTimeSlotError: function(message) {
                // console.error('❌ Error:', message);
                if (typeof toastr !== 'undefined') {
                    toastr.error(message);
                } else {
                    this.showMessage(message, 'error');
                }
            },
            
            showTimeSlotWarning: function(message) {
                // console.warn('⚠️ Warning:', message);
                if (typeof toastr !== 'undefined') {
                    toastr.warning(message);
                } else {
                    this.showMessage(message, 'warning');
                }
            },
            
            showMessage: function(message, type) {
                // Fallback message display if toastr not available
                this.message.content = message;
                this.message.type = type === 'success';
                
                // Auto-clear message after 5 seconds
                var me = this;
                setTimeout(function() {
                    me.message.content = '';
                }, 5000);
            },
            
            // 🔥 NEW: Debug helpers
            enableTimeSlotDebug: function() {
                this.time_slots_debug_mode = true;
                // console.log('🔍 Time slots debug mode enabled');
            },
            
            disableTimeSlotDebug: function() {
                this.time_slots_debug_mode = false;
                // console.log('🔍 Time slots debug mode disabled');
            },
            
            getTimeSlotDebugInfo: function() {
                return {
                    enable_time_slots: this.enable_time_slots,
                    available_time_slots: this.available_time_slots,
                    sold_out_slots: this.sold_out_slots,
                    selected_time_slot: this.selected_time_slot,
                    loading_time_slots: this.loading_time_slots,
                    time_slots_last_update: this.time_slots_last_update,
                    time_slots_retry_count: this.time_slots_retry_count,
                    start_date: this.start_date,
                    guests: this.getGuestCount(),
                    tour_id: this.id
                };
            },
            
            // 🔥 NEW: Force refresh time slots
            refreshTimeSlots: function() {
                if (this.start_date && this.enable_time_slots) {
                    // console.log('🔄 Force refreshing time slots...');
                    this.time_slots_retry_count = 0;
                    this.loadTimeSlotsWithRetry(this.start_date);
                } else {
                    // console.warn('⚠️ Cannot refresh: no date selected or time slots disabled');
                }
            },
            validate() {
                if (!this.start_date) {
                    this.message.status = false;
                    this.message.content = bravo_booking_i18n.no_date_select;
                    return false;
                }

                if (this.enable_time_slots && !this.selected_time_slot) {
                    this.message.status = false;
                    this.message.content = bravo_booking_i18n.no_slot_select;
                    return false;
                }

                return true;
            },

            addPersonType(type) {

                // this is old code

                // type.number = parseInt(type.number);

                // if(type.number < parseInt(type.max) || !type.max) type.number +=1;



                // this is new code added by mohamed ali

                let index = this.person_types.indexOf(type);

                let newNumber = parseInt(type.number) + 1;

                if (newNumber <= (parseInt(type.max) || Infinity)) {

                    this.$set(this.person_types, index, {

                        ...type,

                        number: newNumber,

                    });

                }

            },

            minusPersonType(type) {

                type.number = parseInt(type.number);

                if (type.number > type.min) type.number -= 1;

            },

            changePersonType(type) {

                type.number = parseInt(type.number);

                if (type.number > parseInt(type.max)) {

                    type.number = type.max;

                }

                if (type.number < type.min) {

                    type.number = type.min;

                }

                console.log("Third: ", type.number);

            },

            addGuestsType() {

                var me = this;

                if (me.guests < parseInt(me.max_guests) || !me.max_guests)

                    me.guests += 1;

            },

            minusGuestsType() {

                var me = this;

                if (me.guests >= 1) me.guests -= 1;

            },

            doSubmit: function (e) {

                e.preventDefault();

                if (this.onSubmit) return false;



                if (!this.validate()) return false;



                this.onSubmit = true;

                var me = this;



                this.message.content = "";



                if (this.step == 1) {

                    this.html = "";

                }

                $.ajax({

                    url: bookingCore.url + "/booking/addToCart",

                    data: {

                        service_id: this.id,

                        service_type: "tour",

                        start_date: this.start_date,

                        person_types: this.person_types,

                        packages: this.packages,

                        extra_price: this.extra_price,

                        guests: this.guests,

                        time_slot_id: this.time_slot_id,
                        start_time: this.start_time,
                    },

                    dataType: "json",

                    type: "post",

                    success: function (res) {

                        if (!res.status) {

                            me.onSubmit = false;

                        }

                        if (res.message) {

                            me.message.content = res.message;

                            me.message.type = res.status;

                        }



                        if (res.step) {

                            me.step = res.step;

                        }

                        if (res.html) {

                            me.html = res.html;

                        }



                        if (res.url) {

                            window.location.href = res.url;

                        }



                        if (res.errors && typeof res.errors == "object") {

                            var html = "";

                            for (var i in res.errors) {

                                html += res.errors[i] + "<br>";

                            }

                            me.message.content = html;

                        }

                    },

                    error: function (e) {

                        console.log(e);

                        me.onSubmit = false;



                        bravo_handle_error_response(e);



                        if (e.status == 401) {

                            $(".bravo_single_book_wrap").modal("hide");

                        }



                        if (e.status != 401 && e.responseJSON) {

                            me.message.content = e.responseJSON.message

                                ? e.responseJSON.message

                                : "Can not booking";

                            me.message.type = false;

                        }

                    },

                });

            },

            doEnquirySubmit: function (e) {

                e.preventDefault();

                if (this.onSubmit) return false;

                if (!this.validateenquiry()) return false;

                this.onSubmit = true;

                var me = this;

                this.message.content = "";



                $.ajax({

                    url: bookingCore.url + "/booking/addEnquiry",

                    data: {

                        service_id: this.id,

                        service_type: "tour",

                        name: this.enquiry_name,

                        email: this.enquiry_email,

                        phone: this.enquiry_phone,

                        travel_from: this.enquiry_travel_from,

                        adult: this.enquiry_adult,

                        children: this.enquiry_children,

                        note: this.enquiry_note,

                    },

                    dataType: "json",

                    type: "post",

                    success: function (res) {

                        if (res.message) {

                            me.message.content = res.message;

                            me.message.type = res.status;

                        }

                        if (res.errors && typeof res.errors == "object") {

                            var html = "";

                            for (var i in res.errors) {

                                html += res.errors[i] + "<br>";

                            }

                            me.message.content = html;

                        }

                        if (res.status) {

                            me.enquiry_is_submit = true;

                            me.enquiry_name = "";

                            me.enquiry_email = "";

                            me.enquiry_phone = "";

                            me.enquiry_travel_from = "";

                            me.enquiry_adult = "";

                            me.enquiry_children = "";

                            me.enquiry_note = "";

                        }

                        me.onSubmit = false;

                    },

                    error: function (e) {

                        me.onSubmit = false;

                        bravo_handle_error_response(e);

                        if (e.status == 401) {

                            $(".bravo_single_book_wrap").modal("hide");

                        }

                        if (e.status != 401 && e.responseJSON) {

                            me.message.content = e.responseJSON.message

                                ? e.responseJSON.message

                                : "Can not booking";

                            me.message.type = false;

                        }

                    },

                });

            },

            validateenquiry() {

                if (!this.enquiry_name) {

                    this.message.status = false;

                    this.message.content = bravo_booking_i18n.name_required;

                    return false;

                }

                if (!this.enquiry_email) {

                    this.message.status = false;

                    this.message.content = bravo_booking_i18n.email_required;

                    return false;

                }

                return true;

            },

            openStartDate() {

                $(this.$refs.start_date).trigger("click");

            },

        },

    });



    $(window).on("load", function () {

        var urlHash = window.location.href.split("#")[1];

        if (urlHash && $("." + urlHash).length) {

            var offset_other = 70;

            if (urlHash === "review-list") {

                offset_other = 330;

            }

            $("html,body").animate(

                {

                    scrollTop: $("." + urlHash).offset().top - offset_other,

                },

                1000

            );

        }

    });



    $(".bravo-button-book-mobile").click(function () {

        $(".bravo_single_book_wrap").modal("show");

    });



    $(".bravo_detail_tour .g-faq .item .header").click(function () {

        $(this).parent().toggleClass("active");

    });



    $(".bravo_detail_tour .g-itinerary").each(function () {

        $(this)

            .find(".owl-carousel")

            .owlCarousel({

                items: 3,

                loop: false,

                margin: 15,

                nav: false,

                responsive: {

                    0: {

                        items: 1,

                    },

                    768: {

                        items: 2,

                    },

                    1000: {

                        items: 3,

                    },

                },

            });

    });

    // 🔥 GLOBAL DEBUG FUNCTIONS - Available in browser console
    window.tourTimeSlotsDebug = {
        getVueApp: function() {
            const element = document.querySelector('#bravo_tour_book_app');
            return element && element.__vue__ ? element.__vue__ : null;
        },
        
        getDebugInfo: function() {
            const app = this.getVueApp();
            return app ? app.getTimeSlotDebugInfo() : null;
        },
        
        refreshSlots: function() {
            const app = this.getVueApp();
            if (app) {
                app.refreshTimeSlots();
            } else {
                console.error('❌ Vue app not found');
            }
        },
        
        enableDebug: function() {
            const app = this.getVueApp();
            if (app) {
                app.enableTimeSlotDebug();
            } else {
                console.error('❌ Vue app not found');
            }
        },
        
        disableDebug: function() {
            const app = this.getVueApp();
            if (app) {
                app.disableTimeSlotDebug();
            } else {
                console.error('❌ Vue app not found');
            }
        },
        
        testAPI: function(tourId, date) {
            const app = this.getVueApp();
            const actualTourId = tourId || (app ? app.id : 128);
            const actualDate = date || (app ? app.start_date : '2024-12-15');
            
            // console.log('🧪 Testing API with:', { tourId: actualTourId, date: actualDate });
            
            const apiUrl = '/api/tour/time-slots/available?tour_id=' + actualTourId + '&date=' + actualDate + '&guests=2&_t=' + Date.now();
            
            fetch(apiUrl)
                .then(response => {
                    // console.log('📡 API Response Status:', response.status);
                    return response.json();
                })
                .then(data => {
                    // console.log('✅ API Response Data:', data);
                })
                .catch(error => {
                    console.error('❌ API Error:', error);
                });
        },
        
        setDate: function(date) {
            const app = this.getVueApp();
            if (app) {
                app.start_date = date;
                if (app.enable_time_slots) {
                    app.refreshTimeSlots();
                }
                // console.log('📅 Date set to:', date);
            } else {
                console.error('❌ Vue app not found');
            }
        },
        
        clearSlots: function() {
            const app = this.getVueApp();
            if (app) {
                app.available_time_slots = [];
                app.sold_out_slots = [];
                app.clearTimeSlot();
                // console.log('🧹 Time slots cleared');
            } else {
                // console.error('❌ Vue app not found');
            }
        },
        
        help: function() {
            console.log('📚 Available Commands:');
            console.log('- tourTimeSlotsDebug.getDebugInfo() - Get current debug info');
            console.log('- tourTimeSlotsDebug.refreshSlots() - Refresh time slots');
            console.log('- tourTimeSlotsDebug.testAPI(tourId, date) - Test API call');
            console.log('- tourTimeSlotsDebug.setDate("2024-12-15") - Set date and refresh');
            console.log('- tourTimeSlotsDebug.clearSlots() - Clear all slots');
            console.log('- tourTimeSlotsDebug.enableDebug() - Enable debug mode');
            console.log('- tourTimeSlotsDebug.disableDebug() - Disable debug mode');
        }
    };
    
    // Show help on load
    console.log('🚀 Tour Time Slots Enhanced! Type tourTimeSlotsDebug.help() for commands');
    
})(jQuery);
