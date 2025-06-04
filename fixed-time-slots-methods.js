// Fixed time slots loading for Vue.js
// Add this to the Vue.js methods section

// Enhanced loadTimeSlots method with better error handling
loadTimeSlots: function(date, forceRefresh = false) {
    console.log('🔄 loadTimeSlots called with:', { date, forceRefresh, enable_time_slots: this.enable_time_slots });
    
    if (!this.enable_time_slots || !date) {
        console.log('❌ Time slots not enabled or no date provided');
        this.clearTimeSlots();
        return;
    }

    this.loading_time_slots = true;
    this.available_time_slots = [];
    this.sold_out_slots = [];
    
    var me = this;
    var guestCount = this.getGuestCount();
    
    // Multiple API endpoints to try
    var apiUrls = [
        '/api/tour/time-slots/available',
        '{{ route("tour.time_slots.availability") }}' // Fallback to named route
    ];
    
    var requestData = {
        tour_id: this.id,
        date: date,
        guests: guestCount,
        force_refresh: forceRefresh,
        _t: Date.now()
    };
    
    console.log('📡 Making request with data:', requestData);
    
    function tryApiCall(urlIndex) {
        if (urlIndex >= apiUrls.length) {
            console.error('❌ All API endpoints failed');
            me.loading_time_slots = false;
            me.showTimeSlotError('Failed to load time slots from all endpoints');
            return;
        }
        
        var currentUrl = apiUrls[urlIndex];
        console.log('🔗 Trying API URL:', currentUrl);
        
        $.ajax({
            url: currentUrl,
            method: 'GET',
            data: requestData,
            timeout: 10000,
            success: function(response) {
                console.log('✅ API Success:', response);
                me.loading_time_slots = false;
                
                if (response.success && response.data && Array.isArray(response.data)) {
                    me.processTimeSlots(response.data, guestCount);
                    
                    if (response.cache_refreshed) {
                        console.log('✅ Time slot data refreshed from server');
                    }
                } else if (response.time_slots && Array.isArray(response.time_slots)) {
                    me.processTimeSlots(response.time_slots, guestCount);
                } else {
                    console.log('⚠️ No time slots in response');
                    me.clearTimeSlots();
                    me.showTimeSlotError('No time slots available for the selected date');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ API Error for URL', currentUrl, ':', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                
                // Try next URL
                tryApiCall(urlIndex + 1);
            }
        });
    }
    
    // Start with first API URL
    tryApiCall(0);
},

// New helper method to show time slot errors
showTimeSlotError: function(message) {
    console.log('❌ Time slot error:', message);
    this.available_time_slots = [];
    this.sold_out_slots = [];
    this.loading_time_slots = false;
    
    // Show error in UI if needed
    if (this.$refs.timeSlotError) {
        this.$refs.timeSlotError.textContent = message;
        this.$refs.timeSlotError.style.display = 'block';
    }
},

// Enhanced processTimeSlots with better logging
processTimeSlots: function(slots, guestCount) {
    console.log('🎯 Processing time slots:', slots);
    
    this.available_time_slots = [];
    this.sold_out_slots = [];

    if (!Array.isArray(slots)) {
        console.error('❌ Time slots is not an array:', slots);
        this.showTimeSlotError('Invalid time slots data received');
        return;
    }

    for (var i = 0; i < slots.length; i++) {
        var slot = slots[i];
        slot.is_high_demand = slot.remaining_capacity && slot.remaining_capacity <= 5 && slot.remaining_capacity > 0;
        slot.utilization_percentage = slot.max_guests > 0 ? Math.round(((slot.max_guests - slot.remaining_capacity) / slot.max_guests) * 100) : 0;
        
        if (slot.is_sold_out || (slot.remaining_capacity && slot.remaining_capacity < guestCount)) {
            this.sold_out_slots.push(slot);
        } else {
            this.available_time_slots.push(slot);
        }
    }
    
    console.log('✅ Processed slots - Available:', this.available_time_slots.length, 'Sold out:', this.sold_out_slots.length);

    // Reset selected slot if it's no longer available
    if (this.selected_time_slot) {
        var isStillAvailable = this.available_time_slots.some(function(slot) {
            return slot.id === this.selected_time_slot.id;
        }.bind(this));
        
        if (!isStillAvailable) {
            console.log('⚠️ Previously selected slot no longer available');
            this.selected_time_slot = null;
            this.time_slot_id = null;
        }
    }
    
    // Auto-select if only one slot available
    if (this.available_time_slots.length === 1) {
        console.log('🎯 Auto-selecting single available slot');
        this.selectTimeSlot(this.available_time_slots[0]);
    }
},
