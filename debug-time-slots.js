// Debug Time Slots JavaScript
// Location: Add this to your browser console to test

function debugTimeSlots() {
    console.log('=== TIME SLOTS DEBUG ===');
    
    // Check if Vue app exists
    if (typeof window.bravo_booking_vue !== 'undefined') {
        console.log('✅ Vue app found');
        const app = window.bravo_booking_vue;
        
        console.log('Enable Time Slots:', app.enable_time_slots);
        console.log('Start Date:', app.start_date);
        console.log('Start Date Formatted:', app.start_date_formatted);
        console.log('Tour ID:', app.id);
        
        // Test API call directly
        if (app.id && app.start_date_formatted) {
            console.log('🔄 Testing API call...');
            
            $.ajax({
                url: '/api/tour/time-slots/available',
                method: 'GET',
                data: {
                    tour_id: app.id,
                    date: app.start_date_formatted,
                    guests: 1,
                    force_refresh: true,
                    _t: Date.now()
                },
                success: function(response) {
                    console.log('✅ API Response:', response);
                },
                error: function(xhr, status, error) {
                    console.log('❌ API Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                }
            });
        } else {
            console.log('❌ Missing data for API call');
        }
    } else {
        console.log('❌ Vue app not found');
    }
    
    console.log('========================');
}

// Auto-run debug after page load
$(document).ready(function() {
    setTimeout(debugTimeSlots, 2000);
});
