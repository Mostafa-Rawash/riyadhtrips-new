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

            },

            person_types: {

                handler: function f() {

                    this.step = 1;

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

                            // Clone the person types and format display prices
                            me.person_types = item.person_types.map(function(pt) {
                                return {
                                    ...pt,
                                    display_price: window.bravo_format_money ? window.bravo_format_money(pt.price) : '€' + pt.price
                                };
                            });

                        } else {

                            me.person_types = null;

                        }

                        me.max_guests = parseInt(item.max_guests);

                        me.price = parseFloat(item.price);

                    }

                }

            },

            packages: {

                handler: function f() {

                    this.step = 1;

                },

                deep: true,

            },

        },

        computed: {

            total_price: function () {

                var me = this;

                console.log(me);

                if (me.start_date !== "") {

                    var total = 0;

                    var total_guests = 0;

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
                    
                    console.log("Selected event:", selectedEvent);
                    
                    // Use person types from the selected event if available
                    if (selectedEvent && selectedEvent.person_types != null && selectedEvent.person_types.length > 0) {
                        for (var ix in selectedEvent.person_types) {
                            var person_type = selectedEvent.person_types[ix];
                            // Get the price from the person type in the selected event
                            var personPrice = parseFloat(person_type.price);
                            // Get the number from me.person_types which has the updated guest counts
                            var personNumber = 0;
                            for (var jx in me.person_types) {
                                if (me.person_types[jx].name === person_type.name) {
                                    personNumber = parseInt(me.person_types[jx].number);
                                    break;
                                }
                            }
                            console.log("Person type:", person_type.name, "Price from event:", personPrice, "Number:", personNumber);
                            total += personPrice * personNumber;
                            total_guests += personNumber;
                        }
                    } else if (me.person_types != null && me.person_types.length > 0) {
                        // Fallback to using me.person_types if selectedEvent not found
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
                                console.log("Package selected:", packages.name, "Price:", packages.price);
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

            validate() {

                if (!this.start_date) {

                    this.message.status = false;

                    this.message.content = bravo_booking_i18n.no_date_select;

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

})(jQuery);
