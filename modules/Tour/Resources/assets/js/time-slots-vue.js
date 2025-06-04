/**
 * Vue Component for Tour Time Slots Selection
 * This component handles time slot selection with real-time availability updates
 */

Vue.component('tour-time-slots', {
    template: `
        <div class="bravo-form-group bravo-time-slots-section" v-if="timeSlotsEnabled">
            <div class="time-slots-header">
                <label class="form-label">
                    <i class="icofont-clock-time"></i>
                    {{ __('Select Your Preferred Time') }} 
                    <span class="required">*</span>
                </label>
                <div class="time-slots-info">
                    <small class="text-muted">
                        <i class="icofont-info-circle"></i>
                        {{ __('Times shown are available for your selected date and party size') }} ({{ guestCount }} {{ guestCount === 1 ? __('guest') : __('guests') }})
                    </small>
                </div>
            </div>

            <div class="time-slots-container">
                <!-- Loading State -->
                <div class="time-slots-loading" v-show="loading">
                    <div class="loading-spinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">{{ __('Loading...') }}</span>
                        </div>
                        <p class="mt-2">{{ __('Finding available times...') }}</p>
                    </div>
                </div>

                <!-- Placeholder State -->
                <div class="time-slots-placeholder" v-show="!loading && !selectedDate">
                    <div class="placeholder-content">
                        <i class="icofont-calendar text-muted"></i>
                        <p class="text-muted mb-0">{{ __('Please select a date to view available time slots') }}</p>
                    </div>
                </div>

                <!-- Time Slots Grid -->
                <div class="time-slots-grid" v-show="!loading && timeSlots.length > 0 && !selectedSlot">
                    <div 
                        v-for="slot in timeSlots" 
                        :key="slot.id"
                        class="time-slot-item"
                        :class="{ 
                            'disabled': slot.is_sold_out || slot.remaining_capacity < guestCount || slot.is_cutoff_reached,
                            'insufficient-capacity': !slot.is_sold_out && slot.remaining_capacity < guestCount,
                            'sold-out': slot.is_sold_out,
                            'selected': selected_time_slot === slot.id 
                        }"
                        @click="handleTimeSlotClick(slot)"
                        :title="getSlotTooltip(slot)"
                        role="button"
                        :tabindex="isSlotBookable(slot) ? 0 : -1"
                        :aria-disabled="!isSlotBookable(slot)"
                    >
                        <!-- High Demand Badge -->
                        <div v-if="slot.high_demand" class="high-demand-badge">
                            {{ __('Popular') }}
                        </div>
                        
                        <div class="time-slot-header">
                            <div class="time-slot-time">{{ slot.formatted_time }}</div>
                            <div v-if="slot.price_modifier != 0" class="time-slot-price">
                                {{ formatPrice(slot.price_modifier) }}
                            </div>
                        </div>
                        
                        <div class="time-slot-capacity">
                            <i class="icofont-users-alt-4"></i>
                            <span 
                                class="capacity-text"
                                :class="{ 'low-capacity': slot.remaining_capacity <= 3 && slot.remaining_capacity > 0 }"
                            >
                                {{ getCapacityText(slot) }}
                            </span>
                        </div>
                        
                        <div v-if="slot.description" class="time-slot-description">
                            {{ slot.description }}
                        </div>
                        
                        <!-- Sold Out Overlay -->
                        <div v-if="slot.is_sold_out" class="sold-out-overlay">
                            <div class="sold-out-text">{{ __('Sold Out') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Error State -->
                <div class="time-slots-error" v-show="!loading && error">
                    <div class="alert alert-warning">
                        <i class="icofont-warning"></i>
                        <span>{{ error }}</span>
                    </div>
                </div>

                <!-- Hidden Input -->
                <input type="hidden" name="time_slot_id" v-model="selected_time_slot" required>
            </div>

            <!-- Selected Slot Summary -->
            <div class="selected-slot-summary" v-show="selectedSlot">
                <div class="alert alert-info">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <strong>{{ __('Selected Time:') }}</strong>
                            {{ selectedSlot ? selectedSlot.formatted_time : '' }}
                            <div class="mt-1">
                                <small class="text-muted">
                                    {{ getSelectedSlotDetails() }}
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <div v-if="selectedSlot && selectedSlot.price_modifier != 0">
                                <strong>{{ formatPrice(selectedSlot.price_modifier, true) }}</strong>
                                <small class="text-muted d-block">{{ __('Time slot adjustment') }}</small>
                            </div>
                            <button 
                                type="button" 
                                class="btn btn-sm btn-outline-secondary mt-1" 
                                @click="clearSelection()"
                            >
                                {{ __('Change Time') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,

    props: {
        tourId: {
            type: [String, Number],
            required: true
        },
        timeSlotsEnabled: {
            type: Boolean,
            default: false
        },
        initialDate: {
            type: String,
            default: ''
        },
        initialGuests: {
            type: Number,
            default: 1
        }
    },

    data() {
        return {
            selected_time_slot: null,
            selectedSlot: null,
            timeSlots: [],
            loading: false,
            error: null,
            selectedDate: this.initialDate,
            guestCount: this.initialGuests,
            updateInterval: null,
            lastUpdate: null
        }
    },

    computed: {
        availableSlots() {
            return this.timeSlots.filter(slot => 
                !slot.is_sold_out && slot.remaining_capacity >= this.guestCount
            );
        }
    },

    mounted() {
        this.initializeComponent();
        this.setupEventListeners();
        
        if (this.selectedDate) {
            this.loadTimeSlots();
        }
    },

    beforeDestroy() {
        this.cleanup();
    },

    methods: {
        initializeComponent() {
            // Initialize real-time updates
            this.startRealTimeUpdates();
        },

        setupEventListeners() {
            // Listen for date changes from parent form
            this.$root.$on('dateChanged', (date) => {
                this.selectedDate = date;
                this.clearSelection();
                if (date) {
                    this.loadTimeSlots();
                } else {
                    this.resetState();
                }
            });

            // Listen for guest count changes with slot validation
            this.$root.$on('guestsChanged', (guests) => {
                const newGuestCount = parseInt(guests) || 1;
                const oldGuestCount = this.guestCount;
                
                this.guestCount = newGuestCount;
                
                // Clear selected slot if it's no longer valid for new guest count
                if (this.selectedSlot && newGuestCount > oldGuestCount) {
                    if (!this.isSlotBookable(this.selectedSlot)) {
                        console.log('🗑️ Clearing time slot selection due to increased guest count');
                        this.clearSelection();
                        
                        // Show user notification if available
                        if (window.toastr) {
                            window.toastr.warning(`Your selected time slot was cleared because it only has ${this.selectedSlot.remaining_capacity} spots available, but you now need ${newGuestCount} spots.`);
                        }
                    }
                }
                
                if (this.selectedDate) {
                    this.loadTimeSlots();
                }
            });
        },

        async loadTimeSlots() {
            if (!this.selectedDate || !this.tourId) return;

            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get('/api/tour/time-slots/available', {
                    params: {
                        tour_id: this.tourId,
                        date: this.selectedDate,
                        guests: this.guestCount
                    }
                });

                if (response.data.success) {
                    this.timeSlots = response.data.time_slots || [];
                    this.lastUpdate = new Date();
                    
                    // Auto-select if only one available slot
                    if (this.availableSlots.length === 1) {
                        this.selectTimeSlot(this.availableSlots[0]);
                    }
                } else {
                    this.error = response.data.message || this.__('Failed to load time slots');
                    this.timeSlots = [];
                }
            } catch (error) {
                console.error('Error loading time slots:', error);
                this.error = error.response?.data?.message || this.__('Network error. Please try again.');
                this.timeSlots = [];
            } finally {
                this.loading = false;
            }
        },

        // Enhanced slot validation method
        isSlotBookable(slot) {
            if (!slot) return false;
            
            // Check if slot is sold out
            if (slot.is_sold_out) return false;
            
            // Check if slot has zero capacity
            if (slot.remaining_capacity === 0) return false;
            
            // Check if booking cutoff has been reached
            if (slot.is_cutoff_reached) return false;
            
            // Check if remaining capacity is sufficient for current guest count
            return slot.remaining_capacity >= this.guestCount;
        },
        
        // Enhanced click handler with validation
        handleTimeSlotClick(slot) {
            // Prevent clicking on disabled slots
            if (!this.isSlotBookable(slot)) {
                console.log('🚫 Slot not clickable:', slot);
                return false;
            }
            
            this.selectTimeSlot(slot);
        },
        
        // Get tooltip text for slot
        getSlotTooltip(slot) {
            if (!slot) return '';
            
            if (slot.is_sold_out) {
                return this.__('This time slot is fully booked');
            }
            
            if (slot.remaining_capacity === 0) {
                return this.__('No spots available');
            }
            
            if (slot.remaining_capacity < this.guestCount) {
                return this.__('Only :available spots available, but you need :needed spots', {
                    available: slot.remaining_capacity,
                    needed: this.guestCount
                });
            }
            
            if (slot.is_cutoff_reached) {
                return this.__('Booking deadline has passed for this time slot');
            }
            
            return this.__(':count spots available', { count: slot.remaining_capacity });
        },

        selectTimeSlot(slot) {
            if (!this.isSlotBookable(slot)) {
                return;
            }

            this.selected_time_slot = slot.id;
            this.selectedSlot = { ...slot };
            
            // Ensure start_time is included in the emitted data
            const slotData = {
                ...slot,
                start_time: slot.start_time || slot.time // Fallback to time if start_time not present
            };
            
            // Emit events for parent components with complete slot data
            this.$emit('timeSlotSelected', slotData);
            this.$root.$emit('timeSlotSelected', slotData);
            
            // Update pricing if needed
            this.updatePricing(slot.price_modifier);
        },

        clearSelection() {
            this.selected_time_slot = null;
            this.selectedSlot = null;
            
            this.$emit('timeSlotCleared');
            this.$root.$emit('timeSlotCleared');
            
            this.updatePricing(0);
        },

        resetState() {
            this.timeSlots = [];
            this.clearSelection();
            this.error = null;
        },

        getCapacityText(slot) {
            if (slot.is_sold_out) {
                return this.__('Sold Out');
            }
            
            if (slot.remaining_capacity === 0) {
                return this.__('Fully Booked');
            }
            
            if (slot.remaining_capacity < this.guestCount) {
                return this.__('Need :needed, only :available left', {
                    needed: this.guestCount,
                    available: slot.remaining_capacity
                });
            }
            
            if (slot.remaining_capacity <= 3) {
                return this.__('Only :count spots left!', { count: slot.remaining_capacity });
            }
            
            return this.__(':count spaces available', { count: slot.remaining_capacity });
        },

        getSelectedSlotDetails() {
            if (!this.selectedSlot) return '';
            
            let details = this.__(':capacity guests max', { capacity: this.selectedSlot.max_guests });
            
            if (this.selectedSlot.description) {
                details += ' • ' + this.selectedSlot.description;
            }
            
            return details;
        },

        formatPrice(amount, withSign = false) {
            if (amount === 0) return '';
            
            const formatted = new Intl.NumberFormat(this.getLocale(), {
                style: 'currency',
                currency: this.getCurrency()
            }).format(Math.abs(amount));
            
            if (withSign) {
                return (amount > 0 ? '+' : '-') + formatted;
            }
            
            return formatted;
        },

        updatePricing(priceModifier) {
            this.$emit('priceModifierChanged', priceModifier);
            this.$root.$emit('priceModifierChanged', priceModifier);
        },

        startRealTimeUpdates() {
            this.updateInterval = setInterval(() => {
                if (this.selectedDate && this.timeSlots.length > 0) {
                    this.getRealTimeUpdates();
                }
            }, 30000); // 30 seconds
        },

        async getRealTimeUpdates() {
            try {
                const response = await axios.get('/api/tour/time-slots/updates', {
                    params: {
                        tour_id: this.tourId,
                        date: this.selectedDate
                    }
                });

                if (response.data.success && response.data.updates) {
                    this.updateSlotsWithRealTimeData(response.data.updates);
                }
            } catch (error) {
                // Silently handle real-time update errors
                console.warn('Real-time update failed:', error);
            }
        },

        updateSlotsWithRealTimeData(updates) {
            updates.forEach(updatedSlot => {
                const index = this.timeSlots.findIndex(slot => slot.id === updatedSlot.id);
                if (index !== -1) {
                    // Update existing slot data
                    this.$set(this.timeSlots, index, {
                        ...this.timeSlots[index],
                        ...updatedSlot
                    });
                }
            });
        },

        cleanup() {
            if (this.updateInterval) {
                clearInterval(this.updateInterval);
            }
            
            this.$root.$off('dateChanged');
            this.$root.$off('guestsChanged');
        },

        // Helper methods
        getLocale() {
            return document.documentElement.lang || 'en';
        },

        getCurrency() {
            return window.defaultCurrency || 'USD';
        },

        __(key, replace = {}) {
            // Simple translation function - replace with your app's translation method
            let translation = window.i18n && window.i18n[key] ? window.i18n[key] : key;
            
            Object.keys(replace).forEach(placeholder => {
                translation = translation.replace(`:${placeholder}`, replace[placeholder]);
            });
            
            return translation;
        }
    },

    watch: {
        tourId(newVal) {
            if (newVal && this.selectedDate) {
                this.loadTimeSlots();
            }
        },

        timeSlotsEnabled(newVal) {
            if (!newVal) {
                this.resetState();
            }
        }
    }
});

// Auto-initialize if jQuery is available
$(document).ready(function() {
    // Initialize Vue component for existing time slots containers
    $('.time-slots-vue-container').each(function() {
        const container = $(this);
        const tourId = container.data('tour-id');
        const timeSlotsEnabled = container.data('time-slots-enabled') || false;
        
        new Vue({
            el: this,
            data: {
                tourId: tourId,
                timeSlotsEnabled: timeSlotsEnabled
            }
        });
    });
    
    // Setup event bridges between jQuery and Vue
    $('input[name="start_date"]').on('change', function() {
        const date = $(this).val();
        Vue.prototype.$root.$emit('dateChanged', date);
    });
    
    $('input[name="guests"], select[name="guests"]').on('change', function() {
        const guests = parseInt($(this).val()) || 1;
        Vue.prototype.$root.$emit('guestsChanged', guests);
    });
});

// Enhanced CSS Styles for Vue Time Slots Component
if (typeof document !== 'undefined') {
    const style = document.createElement('style');
    style.textContent = `
        /* Vue Time Slots Enhanced Styling */
        .time-slot-item.disabled {
            cursor: not-allowed !important;
            pointer-events: none !important;
            opacity: 0.6;
            position: relative;
        }
        
        .time-slot-item.disabled:hover {
            border-color: #e9ecef !important;
            box-shadow: none !important;
            transform: none !important;
        }
        
        .time-slot-item.insufficient-capacity {
            cursor: not-allowed !important;
            pointer-events: none !important;
            border-color: #dc3545 !important;
            background: linear-gradient(135deg, #fff5f5 0%, #ffeaea 100%) !important;
        }
        
        .time-slot-item.insufficient-capacity .capacity-text {
            color: #dc3545 !important;
            font-weight: 600;
        }
        
        .time-slot-item.sold-out {
            cursor: not-allowed !important;
            pointer-events: none !important;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            opacity: 0.7;
        }
        
        .time-slot-item.insufficient-capacity::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(220, 53, 69, 0.1);
            border-radius: 6px;
            pointer-events: none;
        }
        
        .time-slot-item[aria-disabled="true"] {
            outline: none;
        }
        
        .time-slot-item.disabled .time-slot-time {
            color: #6c757d !important;
        }
        
        .time-slot-item.insufficient-capacity .time-slot-time {
            color: #dc3545 !important;
        }
        
        /* Tooltip enhancement */
        .time-slot-item[title]:hover::after {
            content: attr(title);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 1000;
            margin-bottom: 5px;
        }
        
        .time-slot-item[title]:hover::before {
            content: '';
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: rgba(0, 0, 0, 0.8);
            z-index: 1000;
        }
    `;
    document.head.appendChild(style);
}
