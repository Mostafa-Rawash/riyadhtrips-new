console.log('🚀 Time Slots Debug Script Loaded');

// Wait for page to load and Vue to initialize
$(document).ready(function() {
    setTimeout(function() {
        console.log('=== TIME SLOTS DEBUGGING ===');
        
        // Check if Vue app exists
        if (typeof window.bravo_booking_vue !== 'undefined') {
            const app = window.bravo_booking_vue;
            console.log('✅ Vue app found');
            console.log('Tour ID:', app.id);
            console.log('Enable Time Slots:', app.enable_time_slots);
            console.log('Start Date:', app.start_date);
            console.log('Start Date Formatted:', app.start_date_formatted);
            
            // Test the API directly
            console.log('🔬 Testing API endpoints...');
            
            // Test 1: Basic test endpoint
            $.ajax({
                url: '/api/tour/time-slots/test',
                method: 'GET',
                success: function(response) {
                    console.log('✅ Test API works:', response);
                },
                error: function(xhr) {
                    console.log('❌ Test API failed:', xhr.status, xhr.statusText);
                }
            });
            
            // Test 2: Main API endpoint with dummy data
            $.ajax({
                url: '/api/tour/time-slots/available',
                method: 'GET',
                data: {
                    tour_id: app.id || 1,
                    date: '2025-05-25',
                    guests: 1
                },
                success: function(response) {
                    console.log('✅ Main API works:', response);
                },
                error: function(xhr) {
                    console.log('❌ Main API failed:', xhr.status, xhr.statusText, xhr.responseText);
                }
            });
            
        } else {
            console.log('❌ Vue app not found');
        }
        
        console.log('===============================');
    }, 3000);
});

// Override the loadTimeSlots function with debugging
if (typeof window.bravo_booking_vue !== 'undefined') {
    const originalLoadTimeSlots = window.bravo_booking_vue.loadTimeSlots;
    
    window.bravo_booking_vue.loadTimeSlots = function(date, forceRefresh = false) {
        console.log('🔄 loadTimeSlots called with:', { 
            date, 
            forceRefresh, 
            enable_time_slots: this.enable_time_slots,
            tour_id: this.id 
        });
        
        if (!this.enable_time_slots) {
            console.log('❌ Time slots not enabled');
            return;
        }
        
        if (!date) {
            console.log('❌ No date provided');
            return;
        }
        
        console.log('📡 Making AJAX request to time slots API...');
        
        // Call original function if it exists, otherwise implement directly
        if (originalLoadTimeSlots) {
            return originalLoadTimeSlots.call(this, date, forceRefresh);
        }
        
        // Direct implementation with detailed logging
        this.loading_time_slots = true;
        this.available_time_slots = [];
        this.sold_out_slots = [];
        
        const me = this;
        const guestCount = this.getGuestCount();
        
        $.ajax({
            url: '/api/tour/time-slots/available',
            method: 'GET',
            data: {
                tour_id: this.id,
                date: date,
                guests: guestCount,
                force_refresh: forceRefresh,
                _t: Date.now()
            },
            timeout: 10000,
            success: function(response) {
                console.log('✅ Time slots API success:', response);
                me.loading_time_slots = false;
                
                if (response.success && response.data && Array.isArray(response.data)) {
                    console.log('✅ Processing', response.data.length, 'time slots');
                    me.processTimeSlots(response.data, guestCount);
                } else if (response.time_slots && Array.isArray(response.time_slots)) {
                    console.log('✅ Processing', response.time_slots.length, 'time slots (legacy format)');
                    me.processTimeSlots(response.time_slots, guestCount);
                } else {
                    console.log('⚠️ No time slots found in response');
                    me.clearTimeSlots();
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Time slots API error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                
                me.loading_time_slots = false;
                me.clearTimeSlots();
            }
        });
    };
}