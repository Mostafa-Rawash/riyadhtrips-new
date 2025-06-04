/**
 * Enhanced Time Slots JavaScript - Consolidated Version
 * This replaces debug-time-slots.js, debug-time-slots-enhanced.js, and fixed-time-slots-methods.js
 */

(function($) {
    'use strict';

    // Configuration
    const CONFIG = {
        DEBUG: typeof bookingCore !== 'undefined' && bookingCore.debug,
        CACHE_DURATION: 60000, // 1 minute
        RETRY_ATTEMPTS: 3,
        RETRY_DELAY: 2000,
        API_TIMEOUT: 15000,
        ENDPOINTS: [
            '/api/tour/time-slots/available',
            '/api/tour/time-slots/fallback'
        ]
    };

    // Enhanced Vue.js integration
    function enhanceVueApp() {
        if (typeof window.bravo_booking_vue === 'undefined') {
            console.warn('⚠️ Vue app not found, retrying...');
            return false;
        }

        const app = window.bravo_booking_vue;
        
        // Initialize time slot properties if not set
        const timeSlotProps = {
            selected_time_slot: null,
            available_time_slots: [],
            sold_out_slots: [],
            loading_time_slots: false,
            show_sold_out_slots: false,
            time_slot_id: null,
            start_time: null,
            total_guests: 0,
            time_slots_cache: {},
            time_slots_last_refresh: null
        };

        Object.keys(timeSlotProps).forEach(prop => {
            if (typeof app[prop] === 'undefined') {
                Vue.set(app, prop, timeSlotProps[prop]);
            }
        });

        // Enhanced loadTimeSlots method
        app.loadTimeSlots = function(date, forceRefresh = false) {
            return new Promise((resolve, reject) => {
                if (CONFIG.DEBUG) {
                    console.log('🚀 loadTimeSlots called:', { date, forceRefresh, enable_time_slots: this.enable_time_slots });
                }

                if (!this.enable_time_slots || !date) {
                    this.clearTimeSlots();
                    resolve([]);
                    return;
                }

                // Check cache first
                const cacheKey = `${date}_${this.id}_${this.getGuestCount()}`;
                const cachedData = this.time_slots_cache[cacheKey];
                const cacheAge = Date.now() - (this.time_slots_last_refresh || 0);

                if (!forceRefresh && cachedData && cacheAge < CONFIG.CACHE_DURATION) {
                    if (CONFIG.DEBUG) console.log('📦 Using cached time slots');
                    this.processTimeSlots(cachedData, this.getGuestCount());
                    resolve(cachedData);
                    return;
                }

                this.loading_time_slots = true;
                this.available_time_slots = [];
                this.sold_out_slots = [];

                const requestData = {
                    tour_id: this.id,
                    date: date,
                    guests: this.getGuestCount(),
                    force_refresh: forceRefresh,
                    _t: Date.now()
                };

                // Try multiple endpoints with retry logic
                this.tryApiEndpoints(requestData, 0)
                    .then(response => {
                        if (CONFIG.DEBUG) console.log('✅ Time slots loaded successfully');
                        
                        // Cache the response
                        const slotsData = response.data?.time_slots || response.data || response.time_slots || [];
                        this.time_slots_cache[cacheKey] = slotsData;
                        this.time_slots_last_refresh = Date.now();
                        
                        this.loading_time_slots = false;
                        this.processTimeSlots(slotsData, this.getGuestCount());
                        resolve(slotsData);
                    })
                    .catch(error => {
                        console.error('❌ Failed to load time slots:', error);
                        this.loading_time_slots = false;
                        this.showTimeSlotError(error.message || 'Failed to load time slots');
                        reject(error);
                    });
            });
        };

        // Enhanced API endpoint retry logic
        app.tryApiEndpoints = function(requestData, endpointIndex, retryCount = 0) {
            return new Promise((resolve, reject) => {
                if (endpointIndex >= CONFIG.ENDPOINTS.length) {
                    reject(new Error('All API endpoints failed'));
                    return;
                }

                const currentEndpoint = CONFIG.ENDPOINTS[endpointIndex];
                
                if (CONFIG.DEBUG) {
                    console.log(`🔗 Trying endpoint ${endpointIndex + 1}/${CONFIG.ENDPOINTS.length}: ${currentEndpoint}`);
                }

                $.ajax({
                    url: currentEndpoint,
                    method: 'GET',
                    data: requestData,
                    timeout: CONFIG.API_TIMEOUT,
                    success: (response) => {
                        if (response.success && (response.data || response.time_slots)) {
                            resolve(response);
                        } else {
                            // Try next endpoint
                            this.tryApiEndpoints(requestData, endpointIndex + 1, 0)
                                .then(resolve)
                                .catch(reject);
                        }
                    },
                    error: (xhr, status, error) => {
                        if (CONFIG.DEBUG) {
                            console.error(`❌ Endpoint ${endpointIndex + 1} failed:`, {
                                url: currentEndpoint,
                                status: xhr.status,
                                error: error
                            });
                        }

                        // Retry current endpoint or try next one
                        if (retryCount < CONFIG.RETRY_ATTEMPTS) {
                            setTimeout(() => {
                                this.tryApiEndpoints(requestData, endpointIndex, retryCount + 1)
                                    .then(resolve)
                                    .catch(reject);
                            }, CONFIG.RETRY_DELAY);
                        } else {
                            // Try next endpoint
                            this.tryApiEndpoints(requestData, endpointIndex + 1, 0)
                                .then(resolve)
                                .catch(reject);
                        }
                    }
                });
            });
        };

        // Enhanced processTimeSlots method
        app.processTimeSlots = function(slots, guestCount) {
            if (CONFIG.DEBUG) console.log('🎯 Processing time slots:', slots);

            this.available_time_slots = [];
            this.sold_out_slots = [];

            if (!Array.isArray(slots)) {
                console.error('❌ Invalid time slots data:', slots);
                this.showTimeSlotError('Invalid time slots data received');
                return;
            }

            slots.forEach(slot => {
                // Enhance slot data
                slot.is_high_demand = slot.remaining_capacity <= 5 && slot.remaining_capacity > 0;
                slot.utilization_percentage = slot.max_guests > 0 ? 
                    Math.round(((slot.max_guests - slot.remaining_capacity) / slot.max_guests) * 100) : 0;
                slot.is_cutoff_reached = this.isSlotCutoffReached(slot);

                // Categorize slots
                if (slot.is_sold_out || slot.remaining_capacity < guestCount || slot.is_cutoff_reached) {
                    this.sold_out_slots.push(slot);
                } else {
                    this.available_time_slots.push(slot);
                }
            });

            // Sort slots by time
            this.available_time_slots.sort((a, b) => a.start_time.localeCompare(b.start_time));
            this.sold_out_slots.sort((a, b) => a.start_time.localeCompare(b.start_time));

            // Validate selected slot
            this.validateSelectedSlot();

            // Auto-select single available slot
            if (this.available_time_slots.length === 1 && !this.selected_time_slot) {
                if (CONFIG.DEBUG) console.log('🎯 Auto-selecting single available slot');
                this.selectTimeSlot(this.available_time_slots[0]);
            }

            if (CONFIG.DEBUG) {
                console.log(`✅ Processed ${this.available_time_slots.length} available, ${this.sold_out_slots.length} sold out slots`);
            }
        };

        // Enhanced selectTimeSlot method
        app.selectTimeSlot = function(slot) {
            if (!slot || slot.is_sold_out || slot.remaining_capacity < this.getGuestCount()) {
                console.warn('⚠️ Cannot select unavailable slot:', slot);
                return false;
            }

            this.selected_time_slot = slot;
            this.time_slot_id = slot.id;
            this.start_time = slot.start_time;

            // Trigger price recalculation
            this.$forceUpdate();

            // Emit event for other components
            if (this.$emit) {
                this.$emit('time-slot-selected', slot);
            }

            if (CONFIG.DEBUG) console.log('✅ Time slot selected:', slot);
            return true;
        };

        // Enhanced validation and helper methods
        app.validateSelectedSlot = function() {
            if (!this.selected_time_slot) return;

            const isStillAvailable = this.available_time_slots.some(slot => 
                slot.id === this.selected_time_slot.id
            );

            if (!isStillAvailable) {
                if (CONFIG.DEBUG) console.log('⚠️ Previously selected slot no longer available');
                this.clearTimeSlot();
            }
        };

        app.isSlotCutoffReached = function(slot) {
            if (!slot.booking_cutoff_hours) return false;
            
            const slotDateTime = new Date(`${this.start_date} ${slot.start_time}`);
            const cutoffTime = new Date(slotDateTime.getTime() - (slot.booking_cutoff_hours * 60 * 60 * 1000));
            return new Date() >= cutoffTime;
        };

        app.clearTimeSlot = function() {
            this.selected_time_slot = null;
            this.time_slot_id = null;
            this.start_time = null;
        };

        app.clearTimeSlots = function() {
            this.available_time_slots = [];
            this.sold_out_slots = [];
            this.selected_time_slot = null;
            this.time_slot_id = null;
            this.start_time = null;
            this.loading_time_slots = false;
        };

        app.showTimeSlotError = function(message) {
            console.error('❌ Time slot error:', message);
            
            // Show user-friendly error
            if (typeof toastr !== 'undefined') {
                toastr.error(message);
            } else if (this.message) {
                this.message.content = message;
                this.message.type = false;
            }
        };

        app.refreshTimeSlotsAfterBooking = function() {
            if (this.enable_time_slots && this.start_date_formatted) {
                if (CONFIG.DEBUG) console.log('🔄 Refreshing time slots after booking...');
                
                // Clear cache to force fresh data
                this.time_slots_cache = {};
                this.loadTimeSlots(this.start_date_formatted, true);
            }
        };

        // Watch for changes
        app.$watch('guests', function() {
            this.updateTimeSlotsAvailability();
        });

        app.$watch('person_types', function() {
            this.updateTimeSlotsAvailability();
        }, { deep: true });

        app.$watch('start_date', function(newDate, oldDate) {
            if (newDate && newDate !== oldDate) {
                if (this.enable_time_slots) {
                    this.loadTimeSlots(newDate);
                }
            }
        });

        app.updateTimeSlotsAvailability = function() {
            if (this.enable_time_slots && this.start_date) {
                this.loadTimeSlots(this.start_date);
            }
        };

        if (CONFIG.DEBUG) console.log('✅ Vue app enhanced with time slots functionality');
        return true;
    }

    // Utility functions
    window.timeSlotUtils = {
        formatTime: function(time, endTime = null) {
            if (!time) return '';
            
            const formatSingle = (t) => {
                const [hours, minutes] = t.split(':');
                const hour = parseInt(hours);
                const ampm = hour >= 12 ? 'PM' : 'AM';
                const formattedHour = hour % 12 || 12;
                return `${formattedHour}:${minutes} ${ampm}`;
            };

            const start = formatSingle(time);
            return endTime ? `${start} - ${formatSingle(endTime)}` : start;
        },

        isSlotAvailable: function(slot, guestCount) {
            return !slot.is_sold_out && 
                   !slot.is_cutoff_reached && 
                   slot.remaining_capacity >= guestCount;
        },

        getSlotStatusClass: function(slot, guestCount) {
            if (slot.is_sold_out) return 'sold-out';
            if (slot.is_cutoff_reached) return 'cutoff-reached';
            if (slot.remaining_capacity < guestCount) return 'insufficient-capacity';
            if (slot.remaining_capacity <= 3) return 'low-capacity';
            if (slot.is_high_demand) return 'high-demand';
            return 'available';
        },

        getCapacityWarning: function(slot) {
            if (slot.remaining_capacity <= 1) return 'Only 1 spot left!';
            if (slot.remaining_capacity <= 3) return `Only ${slot.remaining_capacity} spots left!`;
            if (slot.is_high_demand) return 'High demand - book now!';
            return null;
        }
    };

    // Debug helper
    window.debugTimeSlots = function() {
        if (!CONFIG.DEBUG) {
            console.log('Enable debug mode to use this function');
            return;
        }

        console.group('🔍 TIME SLOTS DEBUG INFO');
        
        const app = window.bravo_booking_vue;
        if (app) {
            console.log('Vue App State:', {
                enable_time_slots: app.enable_time_slots,
                tour_id: app.id,
                start_date: app.start_date,
                start_date_formatted: app.start_date_formatted,
                selected_time_slot: app.selected_time_slot,
                available_slots: app.available_time_slots?.length || 0,
                sold_out_slots: app.sold_out_slots?.length || 0,
                loading: app.loading_time_slots,
                guests: app.getGuestCount?.() || app.guests
            });

            console.log('Cache Info:', {
                cache_size: Object.keys(app.time_slots_cache || {}).length,
                last_refresh: app.time_slots_last_refresh ? new Date(app.time_slots_last_refresh) : null
            });
        } else {
            console.log('❌ Vue app not found');
        }

        console.groupEnd();
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Wait for Vue app to be available
        function initializeTimeSlots() {
            if (enhanceVueApp()) {
                const app = window.bravo_booking_vue;
                
                // Load time slots if date is already selected
                if (app.enable_time_slots && app.start_date && app.start_date_formatted) {
                    setTimeout(() => {
                        app.loadTimeSlots(app.start_date_formatted);
                    }, 1000);
                }

                // Add debug button in development
                if (CONFIG.DEBUG) {
                    $('<button type="button" onclick="debugTimeSlots()" style="position: fixed; top: 10px; right: 10px; z-index: 9999; background: #17a2b8; color: white; border: none; padding: 8px 12px; border-radius: 4px; font-size: 12px; cursor: pointer;">🔍 Debug Time Slots</button>')
                        .appendTo('body');
                }
            } else {
                setTimeout(initializeTimeSlots, 500);
            }
        }

        setTimeout(initializeTimeSlots, 500);
    });

    if (CONFIG.DEBUG) {
        console.log('🚀 Enhanced Time Slots JavaScript loaded');
    }

})(jQuery);
