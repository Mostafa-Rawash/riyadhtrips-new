/**
 * DIRECT TIME SLOTS FIX - Enhanced with detailed logging
 * This hooks directly into the existing Vue app after it loads
 */

console.log('🚀 DIRECT TIME SLOTS SCRIPT IS LOADING!');
console.log('📊 Initial check - jQuery:', typeof $ !== 'undefined');
console.log('📊 Initial check - Vue:', typeof Vue !== 'undefined');
console.log('📊 Initial check - bravo_booking_i18n:', typeof bravo_booking_i18n !== 'undefined');

if (typeof bravo_booking_i18n !== 'undefined') {
    console.log('📊 API URL available:', bravo_booking_i18n.load_time_slots_url);
} else {
    console.error('❌ bravo_booking_i18n not available - script loading too early?');
}

// Wait for both jQuery and Vue to be ready
$(document).ready(function() {
    console.log('📋 DOM Ready, checking for Vue app...');
    
    // Function to add time slots functionality
    function addTimeSlotsFunctionality() {
        const vueElement = document.getElementById('bravo_tour_book_app');
        if (!vueElement) {
            console.error('❌ Element #bravo_tour_book_app not found');
            return false;
        }
        
        const vueApp = vueElement.__vue__;
        if (!vueApp) {
            console.error('❌ Vue app not found on element');
            return false;
        }
        
        console.log('✅ Vue app found, adding time slots functionality');
        console.log('📊 Current Vue data keys:', Object.keys(vueApp.$data));
        
        // Check if time slots URL is available
        if (typeof bravo_booking_i18n === 'undefined' || !bravo_booking_i18n.load_time_slots_url) {
            console.error('❌ Time slots URL not configured');
            return false;
        }
        
        console.log('✅ Time slots URL found:', bravo_booking_i18n.load_time_slots_url);
        
        // Add required properties with Vue.set for reactivity
        Vue.set(vueApp, 'enable_time_slots', true);
        Vue.set(vueApp, 'available_time_slots', []);
        Vue.set(vueApp, 'selected_time_slot', null);
        Vue.set(vueApp, 'loading_time_slots', false);
        Vue.set(vueApp, 'sold_out_slots', []);
        Vue.set(vueApp, 'show_sold_out_slots', false);
        Vue.set(vueApp, 'time_slot_id', null);
        Vue.set(vueApp, 'start_time', null);
        
        console.log('✅ Time slots properties added');
        
        // Add getGuestCount method using multiple approaches for reliability
        const getGuestCountFn = function() {
            if (this.person_types && this.person_types.length) {
                return this.person_types.reduce((total, type) => {
                    return total + (parseInt(type.number) || 0);
                }, 0);
            }
            return parseInt(this.guests) || 1;
        };
        
        // Apply using all possible methods
        vueApp.getGuestCount = getGuestCountFn;
        Vue.set(vueApp, 'getGuestCount', getGuestCountFn);
        
        if (vueApp.$options && vueApp.$options.methods) {
            vueApp.$options.methods.getGuestCount = getGuestCountFn;
        }
        
        // Add other essential methods
        const methods = {
            formatTime: function(time) {
                if (!time) return '';
                const [hours, minutes] = time.split(':');
                const hour = parseInt(hours);
                const ampm = hour >= 12 ? 'PM' : 'AM';  
                const formattedHour = hour % 12 || 12;
                return `${formattedHour}:${minutes} ${ampm}`;
            },
            
            selectTimeSlot: function(slot) {
                console.log('🎯 Time slot selected:', slot);
                Vue.set(this, 'selected_time_slot', slot);
                Vue.set(this, 'time_slot_id', slot.id);
                Vue.set(this, 'start_time', slot.start_time);
            },
            
            clearTimeSlot: function() {
                Vue.set(this, 'selected_time_slot', null);
                Vue.set(this, 'time_slot_id', null);
                Vue.set(this, 'start_time', null);
            },
            
            loadTimeSlots: function(date) {
                console.log('📡 loadTimeSlots called with date:', date);
                console.log('📡 Making API call to:', bravo_booking_i18n.load_time_slots_url);
                
                if (!date) {
                    console.warn('❌ No date provided to loadTimeSlots');
                    return;
                }
                
                Vue.set(this, 'loading_time_slots', true);
                Vue.set(this, 'available_time_slots', []);
                Vue.set(this, 'sold_out_slots', []);
                
                const guests = this.getGuestCount();
                const requestData = {
                    tour_id: this.id,
                    date: date,
                    guests: guests
                };
                
                console.log('📡 Request data:', requestData);
                
                $.ajax({
                    url: bravo_booking_i18n.load_time_slots_url,
                    method: 'GET',
                    data: requestData,
                    success: (response) => {
                        console.log('✅ API Success Response:', response);
                        Vue.set(this, 'loading_time_slots', false);
                        
                        if (response.success && response.data && response.data.time_slots) {
                            const availableSlots = response.data.time_slots.filter(slot => 
                                !slot.is_sold_out && slot.remaining_capacity >= guests
                            );
                            const soldOutSlots = response.data.time_slots.filter(slot => 
                                slot.is_sold_out || slot.remaining_capacity < guests
                            );
                            
                            Vue.set(this, 'available_time_slots', availableSlots);
                            Vue.set(this, 'sold_out_slots', soldOutSlots);
                            
                            console.log('✅ Time slots processed:', {
                                total: response.data.time_slots.length,
                                available: availableSlots.length,
                                sold_out: soldOutSlots.length
                            });
                        } else {
                            console.warn('⚠️ No time slots in response or response unsuccessful:', response);
                            Vue.set(this, 'available_time_slots', []);
                            Vue.set(this, 'sold_out_slots', []);
                        }
                    },
                    error: (xhr, status, error) => {
                        console.error('❌ API Error:', {
                            status: status,
                            error: error,
                            response: xhr.responseJSON,
                            url: bravo_booking_i18n.load_time_slots_url,
                            data: requestData
                        });
                        Vue.set(this, 'loading_time_slots', false);
                        Vue.set(this, 'available_time_slots', []);
                        Vue.set(this, 'sold_out_slots', []);
                    }
                });
            }
        };
        
        // Add all methods to Vue instance
        Object.keys(methods).forEach(methodName => {
            vueApp[methodName] = methods[methodName];
            Vue.set(vueApp, methodName, methods[methodName]);
            
            if (vueApp.$options && vueApp.$options.methods) {
                vueApp.$options.methods[methodName] = methods[methodName];
            }
        });
        
        // Set up date watcher  
        vueApp.$watch('start_date', function(newDate, oldDate) {
            console.log('📅 Vue watcher triggered - Date changed from', oldDate, 'to', newDate);
            if (newDate && newDate !== oldDate && this.enable_time_slots) {
                console.log('📅 Loading time slots from watcher for date:', newDate);
                setTimeout(() => {
                    this.loadTimeSlots(newDate);
                }, 200);
            } else if (!newDate) {
                console.log('📅 Clearing time slots (no date)');
                Vue.set(this, 'available_time_slots', []);
                Vue.set(this, 'sold_out_slots', []);
                Vue.set(this, 'selected_time_slot', null);
            }
        });
        
        // Make app globally available for debugging
        window.bravo_booking_vue = vueApp;
        
        console.log('✅ Time slots functionality added successfully!');
        console.log('🔧 Debug: You can now use window.bravo_booking_vue in console');
        
        // Test the methods
        try {
            const guestCount = vueApp.getGuestCount();
            console.log('🧪 getGuestCount test successful:', guestCount);
        } catch (error) {
            console.error('❌ getGuestCount test failed:', error);
        }
        
        return true;
    }
    
    // Try to add functionality with multiple attempts
    function attemptIntegration() {
        let attempts = 0;
        const maxAttempts = 50; // 25 seconds max
        
        function tryIntegration() {
            attempts++;
            console.log(`🔄 Integration attempt ${attempts}/${maxAttempts}`);
            
            if (addTimeSlotsFunctionality()) {
                console.log('✅ Integration successful!');
                return;
            }
            
            if (attempts < maxAttempts) {
                setTimeout(tryIntegration, 500);
            } else {
                console.error('❌ Integration failed after', maxAttempts, 'attempts');
                console.log('🔍 Final check - Element exists:', !!document.getElementById('bravo_tour_book_app'));
                const el = document.getElementById('bravo_tour_book_app');
                console.log('🔍 Final check - Vue on element:', el ? !!el.__vue__ : 'Element not found');
                console.log('💡 You can try running the manual fix from the console');
            }
        }
        
        // Start first attempt after a short delay
        setTimeout(tryIntegration, 1000);
    }
    
    attemptIntegration();
});
