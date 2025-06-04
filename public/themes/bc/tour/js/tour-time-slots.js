/**
 * Tour Time Slots - Consolidated Functionality
 * Handles all time slot related features for tour bookings
 */
(function() {
    'use strict';

    console.log('🚀 Tour Time Slots Script Loading...');

    // Global configuration
    const CONFIG = {
        WAIT_TIMEOUT: 500,
        RETRY_INTERVAL: 100,
        MAX_RETRIES: 50, // 5 seconds maximum wait
        LOAD_DELAY: 1000,
        DEBUG_MODE: typeof bookingCore !== 'undefined' && bookingCore.debug
    };

    let retryCount = 0;

    // Initialize when DOM is ready
    $(document).ready(function() {
        initializeTimeSlots();
    });

    function initializeTimeSlots() {
        // Wait for Vue app to be available
        function waitForVueApp() {
            // Try multiple ways to find the Vue app
            let app = null;
            
            // Method 1: Check for global bravo_booking_vue
            if (typeof window.bravo_booking_vue !== 'undefined' && window.bravo_booking_vue) {
                app = window.bravo_booking_vue;
            }
            // Method 2: Check for Vue app on specific element
            else if ($('#bravo_tour_book_app').length && $('#bravo_tour_book_app')[0].__vue__) {
                app = $('#bravo_tour_book_app')[0].__vue__;
            }
            // Method 3: Check if Vue is available and try to find instances
            else if (typeof Vue !== 'undefined' && Vue.config && Vue.config.devtools) {
                // Try to find Vue instances
                const elements = document.querySelectorAll('[id*="book"], [class*="book"], [data-vue="true"]');
                for (let el of elements) {
                    if (el.__vue__) {
                        app = el.__vue__;
                        break;
                    }
                }
            }
            
            if (app) {
                console.log('✅ Vue app found, initializing time slots...', app);
                window.bravo_booking_vue = app; // Store for consistency
                setupTimeSlotsFunctionality(app);
            } else {
                retryCount++;
                if (retryCount < CONFIG.MAX_RETRIES) {
                    console.log(`⏳ Waiting for Vue app... (${retryCount}/${CONFIG.MAX_RETRIES})`);
                    setTimeout(waitForVueApp, CONFIG.RETRY_INTERVAL);
                } else {
                    console.error('❌ Vue app not found after maximum retries. Time slots functionality will not be available.');
                    // Try to continue without Vue for basic functionality
                    setupBasicTimeSlots();
                }
            }
        }
        
        // Start waiting for Vue app
        setTimeout(waitForVueApp, CONFIG.WAIT_TIMEOUT);
    }

    function setupTimeSlotsFunctionality(app) {
        if (!app) {
            console.error('❌ Vue app not provided');
            return;
        }

        console.log('🎯 Time slots enabled:', app.enable_time_slots);
        
        // Initialize time slot properties if not already set
        initializeVueProperties(app);
        
        // Add methods to Vue app
        addVueMethods(app);
        
        // Setup watchers
        setupVueWatchers(app);
        
        // Load time slots if date is already selected
        loadInitialTimeSlots(app);
        
        // Setup debugging tools
        setupDebugging(app);
        
        console.log('✅ Time slots initialized successfully');
    }

    function setupBasicTimeSlots() {
        console.log('🔧 Setting up basic time slots without Vue integration');
        
        // Basic jQuery-based time slots functionality
        $(document).on('change', 'input[name="start_date"]', function() {
            const date = $(this).val();
            if (date) {
                loadTimeSlotsBasic(date);
            }
        });
        
        $(document).on('click', '.time-slot-item:not(.disabled)', function() {
            $('.time-slot-item').removeClass('selected');
            $(this).addClass('selected');
            
            const slotId = $(this).data('slot-id');
            const startTime = $(this).data('start-time');
            
            $('input[name="time_slot_id"]').val(slotId);
            $('input[name="start_time"]').val(startTime);
        });
        
        console.log('✅ Basic time slots setup complete');
    }

    function loadTimeSlotsBasic(date) {
        const tourId = $('input[name="tour_id"]').val() || $('[data-tour-id]').data('tour-id');
        const guests = $('input[name="guests"]').val() || 1;
        
        if (!tourId) {
            console.warn('⚠️ Tour ID not found for basic time slots loading');
            return;
        }
        
        $.ajax({
            url: bravo_booking_i18n.load_time_slots_url,
            method: 'GET',
            data: {
                tour_id: tourId,
                date: date,
                guests: guests
            },
            beforeSend: function() {
                $('.time-slots-container').html('<div class="time-slots-loading">Loading time slots...</div>');
            },
            success: function(response) {
                if (response.success && response.data && response.data.time_slots) {
                    renderTimeSlotsBasic(response.data.time_slots, guests);
                } else {
                    $('.time-slots-container').html('<div class="time-slots-error">No time slots available</div>');
                }
            },
            error: function() {
                $('.time-slots-container').html('<div class="time-slots-error">Error loading time slots</div>');
            }
        });
    }
    
    function renderTimeSlotsBasic(slots, guestCount) {
        if (!slots || slots.length === 0) {
            $('.time-slots-container').html('<div class="time-slots-empty">No time slots available for this date</div>');
            return;
        }
        
        let html = '<div class="time-slots-grid">';
        slots.forEach(function(slot) {
            const isDisabled = slot.is_sold_out || slot.remaining_capacity < guestCount;
            const statusClass = isDisabled ? 'disabled' : '';
            const statusText = slot.is_sold_out ? 'Sold Out' : 
                             slot.remaining_capacity <= 3 ? `${slot.remaining_capacity} left` : 'Available';
            
            html += `
                <div class="time-slot-item ${statusClass}" 
                     data-slot-id="${slot.id}" 
                     data-start-time="${slot.start_time}">
                    <div class="time-slot-time">${formatTime(slot.start_time)}</div>
                    <div class="time-slot-status">${statusText}</div>
                </div>
            `;
        });
        html += '</div>';
        
        $('.time-slots-container').html(html);
    }

    function initializeVueProperties(app) {
        console.log('🔌 Initializing Vue properties for time slots');
        console.log('📊 Current app data:', {
            id: app.id,
            enable_time_slots: app.enable_time_slots,
            start_date: app.start_date
        });
        
        const properties = {
            'selected_time_slot': null,
            'available_time_slots': [],
            'sold_out_slots': [],
            'loading_time_slots': false,
            'show_sold_out_slots': false,
            'time_slot_id': null,
            'start_time': null
        };

        Object.keys(properties).forEach(prop => {
            if (typeof app[prop] === 'undefined') {
                console.log('🆕 Adding Vue property:', prop, '=', properties[prop]);
                Vue.set(app, prop, properties[prop]);
            } else {
                console.log('✅ Vue property already exists:', prop, '=', app[prop]);
            }
        });
        
        // Ensure enable_time_slots is properly set from booking data
        if (typeof app.enable_time_slots === 'undefined' && typeof bravo_booking_data !== 'undefined') {
            if (bravo_booking_data.enable_time_slots) {
                console.log('🔌 Setting enable_time_slots from booking data:', bravo_booking_data.enable_time_slots);
                Vue.set(app, 'enable_time_slots', bravo_booking_data.enable_time_slots);
            }
        }
        
        console.log('✅ Vue properties initialized. enable_time_slots:', app.enable_time_slots);
    }

    function addVueMethods(app) {
        // Load available time slots for a specific date
        app.loadTimeSlots = function(date) {
            console.log('📡 loadTimeSlots called with date:', date);
            
            if (!this.enable_time_slots || !date) {
                console.log('🚫 Time slots disabled or no date');
                this.available_time_slots = [];
                this.sold_out_slots = [];
                return;
            }
            
            this.loading_time_slots = true;
            this.selected_time_slot = null;
            this.time_slot_id = null;
            this.start_time = null;
            
            const guests = this.getGuestCount();
            
            console.log('📡 Loading time slots for date:', date, 'guests:', guests, 'tour_id:', this.id);
            
            $.ajax({
                url: bravo_booking_i18n.load_time_slots_url,
                method: 'GET',
                data: {
                    tour_id: this.id,
                    date: date,
                    guests: guests
                },
                success: (response) => {
                    console.log('✅ Time slots API response:', response);
                    this.loading_time_slots = false;
                    
                    if (response.success && response.data && response.data.time_slots) {
                        this.processTimeSlots(response.data.time_slots, guests);
                    } else {
                        console.warn('⚠️ No time slots data received');
                        this.available_time_slots = [];
                        this.sold_out_slots = [];
                    }
                },
                error: (xhr, status, error) => {
                    console.error('❌ Failed to load time slots:', error, xhr.responseJSON);
                    this.loading_time_slots = false;
                    
                    // Show user-friendly error message
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to load available time slots. Please try again.');
                    }
                    
                    this.available_time_slots = [];
                    this.sold_out_slots = [];
                }
            });
        };

        // Process and categorize time slots
        app.processTimeSlots = function(slots, guestCount) {
            this.available_time_slots = [];
            this.sold_out_slots = [];
            
            if (!Array.isArray(slots)) {
                console.warn('⚠️ Invalid slots data received');
                return;
            }
            
            slots.forEach(slot => {
                // Add computed properties to slot
                slot.status = getSlotStatus(slot, guestCount);
                slot.is_available = isSlotAvailable(slot, guestCount);
                slot.formatted_time = formatTime(slot.start_time);
                
                if (slot.is_sold_out || slot.remaining_capacity < guestCount) {
                    this.sold_out_slots.push(slot);
                } else {
                    this.available_time_slots.push(slot);
                }
            });
            
            console.log('📊 Processed slots - Available:', this.available_time_slots.length, 'Sold out:', this.sold_out_slots.length);
        };

        // Select a time slot
        app.selectTimeSlot = function(slot) {
            console.log('🎯 Selecting time slot:', slot);
            
            if (!slot || !isSlotAvailable(slot, this.getGuestCount())) {
                console.warn('⚠️ Cannot select unavailable slot:', slot);
                return false;
            }
            
            console.log('🎯 Selecting time slot:', slot);
            
            this.selected_time_slot = slot;
            this.time_slot_id = slot.id;
            this.start_time = slot.start_time;
            
            // Store for form submission
            if ($('input[name="time_slot_id"]').length) {
                $('input[name="time_slot_id"]').val(slot.id);
            } else {
                // Create hidden input if it doesn't exist
                $('<input type="hidden" name="time_slot_id">').val(slot.id).appendTo('#bravo_tour_book_app form');
            }
            
            if ($('input[name="start_time"]').length) {
                $('input[name="start_time"]').val(slot.start_time);
            } else {
                // Create hidden input if it doesn't exist
                $('<input type="hidden" name="start_time">').val(slot.start_time).appendTo('#bravo_tour_book_app form');
            }
            
            console.log('✅ Time slot selected successfully:', slot.id, slot.start_time);
            
            // Trigger any booking form updates
            if (typeof this.updateBookingForm === 'function') {
                this.updateBookingForm();
            }
            
            return true;
        };

        // Get total guest count
        app.getGuestCount = function() {
            if (this.person_types && this.person_types.length) {
                return this.person_types.reduce((total, type) => {
                    return total + (parseInt(type.number) || 0);
                }, 0);
            }
            return parseInt(this.guests) || 1;
        };

        // Format time for display
        app.formatTime = function(time) {
            return formatTime(time);
        };

        // Check if slot is available
        app.isSlotAvailable = function(slot) {
            return isSlotAvailable(slot, this.getGuestCount());
        };

        // Get slot status
        app.getSlotStatus = function(slot) {
            return getSlotStatus(slot, this.getGuestCount());
        };
    }

    function setupVueWatchers(app) {
        console.log('👀 Setting up Vue watchers for time slots');
        
        // Hook into the existing start_date watcher
        const originalStartDateWatcher = app.$options.watch.start_date;
        
        app.$options.watch.start_date = function(newDate, oldDate) {
            console.log('📅 Enhanced start_date watcher triggered:', newDate);
            
            // Call the original watcher function
            if (typeof originalStartDateWatcher === 'function') {
                originalStartDateWatcher.call(this, newDate, oldDate);
            }
            
            // Add our time slots loading
            if (newDate && newDate !== oldDate && this.enable_time_slots) {
                console.log('📅 Loading time slots from enhanced watcher for:', newDate);
                setTimeout(() => {
                    this.loadTimeSlots(newDate);
                }, 200);
            } else if (!newDate) {
                // Clear time slots if date is cleared
                this.available_time_slots = [];
                this.sold_out_slots = [];
                this.selected_time_slot = null;
            }
        };
        
        // Also hook into the daterangepicker event
        setTimeout(() => {
            $(app.$refs.start_date).on('apply.daterangepicker', function(ev, picker) {
                const selectedDate = picker.startDate.format('YYYY-MM-DD');
                console.log('📅 Daterangepicker apply event:', selectedDate);
                
                if (selectedDate && app.enable_time_slots) {
                    setTimeout(() => {
                        app.loadTimeSlots(selectedDate);
                    }, 300);
                }
            });
        }, 1000);

        // Watch for guest count changes
        app.$watch('person_types', function(newTypes, oldTypes) {
            if (this.enable_time_slots && this.start_date && JSON.stringify(newTypes) !== JSON.stringify(oldTypes)) {
                console.log('👥 Guest count changed, reloading time slots');
                setTimeout(() => {
                    this.loadTimeSlots(this.start_date);
                }, 300);
            }
        }, { deep: true });

        app.$watch('guests', function(newGuests, oldGuests) {
            if (this.enable_time_slots && this.start_date && newGuests !== oldGuests) {
                console.log('👥 Guests changed, reloading time slots');
                setTimeout(() => {
                    this.loadTimeSlots(this.start_date);
                }, 300);
            }
        });
        
        console.log('✅ Vue watchers set up successfully');
        
        // Add direct DOM event listener as backup
        $(document).on('change', 'input[name="start_date"], .start_date', function() {
            const selectedDate = $(this).val();
            console.log('📅 Direct DOM date change detected:', selectedDate);
            
            if (selectedDate && app.enable_time_slots) {
                console.log('📅 Triggering loadTimeSlots from DOM event');
                setTimeout(() => {
                    app.loadTimeSlots(selectedDate);
                }, 200);
            }
        });
    }

    function loadInitialTimeSlots(app) {
        if (app.start_date && app.start_date_formatted && app.enable_time_slots) {
            console.log('📅 Loading time slots for pre-selected date:', app.start_date);
            setTimeout(function() {
                app.loadTimeSlots(app.start_date);
            }, CONFIG.LOAD_DELAY);
        }
    }

    function setupDebugging(app) {
        // Add debugging helper
        window.debugTimeSlots = function() {
            console.log('=== TIME SLOTS DEBUG ===');
            console.log('Vue App:', app);
            console.log('Enable Time Slots:', app.enable_time_slots);
            console.log('Start Date:', app.start_date);
            console.log('Start Date Formatted:', app.start_date_formatted);
            console.log('Selected Time Slot:', app.selected_time_slot);
            console.log('Available Slots:', app.available_time_slots);
            console.log('Sold Out Slots:', app.sold_out_slots);
            console.log('Loading:', app.loading_time_slots);
            console.log('Guest Count:', app.getGuestCount());
            console.log('=======================');
        };

        // Add global debug button in development
        if (CONFIG.DEBUG_MODE) {
            const debugButton = $(`
                <button type="button" onclick="debugTimeSlots()" 
                        style="position: fixed; top: 50px; right: 10px; z-index: 9999; 
                               background: #17a2b8; color: white; border: none; 
                               padding: 5px 10px; border-radius: 3px; font-size: 12px;">
                    Debug Time Slots
                </button>
            `);
            debugButton.appendTo('body');
        }
    }

    // Utility functions
    function formatTime(time) {
        if (!time) return '';
        
        try {
            const [hours, minutes] = time.split(':');
            const hour = parseInt(hours);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const formattedHour = hour % 12 || 12;
            return `${formattedHour}:${minutes} ${ampm}`;
        } catch (error) {
            console.warn('⚠️ Invalid time format:', time);
            return time;
        }
    }
    
    function isSlotAvailable(slot, guestCount) {
        if (!slot) return false;
        
        return !slot.is_sold_out && 
               !slot.is_cutoff_reached && 
               (slot.remaining_capacity >= guestCount);
    }
    
    function getSlotStatus(slot, guestCount) {
        if (!slot) return 'unavailable';
        
        if (slot.is_sold_out) return 'sold_out';
        if (slot.is_cutoff_reached) return 'cutoff_reached';
        if (slot.remaining_capacity < guestCount) return 'insufficient_capacity';
        if (slot.remaining_capacity <= 3) return 'low_capacity';
        return 'available';
    }

    // Export utility functions globally
    window.timeSlotUtils = {
        formatTime: formatTime,
        isSlotAvailable: isSlotAvailable,
        getSlotStatus: getSlotStatus
    };

    console.log('🚀 Tour Time Slots Script Loaded Successfully');

})();
