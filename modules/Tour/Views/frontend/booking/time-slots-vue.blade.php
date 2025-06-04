{{-- Enhanced Time Slots Component with Vue.js --}}
@if($row->meta && $row->meta->enable_time_slots)
<div class="time-slots-vue-container" 
     data-tour-id="{{ $row->id }}" 
     data-time-slots-enabled="true">
    <tour-time-slots 
        :tour-id="{{ $row->id }}"
        :time-slots-enabled="true"
        :initial-date="'{{ request()->input('start') ?? '' }}'"
        :initial-guests="{{ request()->input('guests') ?? 1 }}"
        @time-slot-selected="onTimeSlotSelected"
        @time-slot-cleared="onTimeSlotCleared"
        @price-modifier-changed="onPriceModifierChanged"
    ></tour-time-slots>
</div>

{{-- Fallback for non-Vue environments --}}
<noscript>
    <div class="bravo-form-group bravo-time-slots-section">
        <div class="time-slots-header">
            <label class="form-label">
                <i class="icofont-clock-time"></i>
                {{__('Select Your Preferred Time')}} 
                <span class="required">*</span>
            </label>
        </div>
        
        <div class="time-slots-container">
            <select name="time_slot_id" class="form-control" required>
                <option value="">{{__('Please enable JavaScript to see available time slots')}}</option>
            </select>
        </div>
    </div>
</noscript>

{{-- Styles for Vue Component --}}
<style>
.bravo-time-slots-section {
    margin-bottom: 25px;
}

.time-slots-header {
    margin-bottom: 15px;
}

.time-slots-header .form-label {
    font-weight: 600;
    margin-bottom: 5px;
    display: block;
    color: #2c3e50;
}

.time-slots-info {
    margin-top: 5px;
}

.time-slots-container {
    min-height: 120px;
    position: relative;
}

.loading-spinner {
    text-align: center;
    padding: 40px 20px;
}

.placeholder-content {
    text-align: center;
    padding: 40px 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px dashed #dee2e6;
}

.placeholder-content i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.5;
}

.time-slots-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.time-slot-item {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 18px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
    position: relative;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.time-slot-item:hover:not(.disabled) {
    border-color: var(--primary-color, #5191fa);
    box-shadow: 0 4px 12px rgba(81, 145, 250, 0.15);
    transform: translateY(-2px);
}

.time-slot-item.selected {
    border-color: var(--primary-color, #5191fa);
    background-color: rgba(81, 145, 250, 0.05);
    box-shadow: 0 4px 12px rgba(81, 145, 250, 0.2);
}

.time-slot-item.disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background-color: #f8f9fa;
    transform: none !important;
}

.time-slot-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.time-slot-time {
    font-weight: 600;
    font-size: 16px;
    color: #2c3e50;
}

.time-slot-price {
    font-weight: 600;
    color: var(--primary-color, #5191fa);
    font-size: 14px;
    background: rgba(81, 145, 250, 0.1);
    padding: 2px 8px;
    border-radius: 12px;
}

.time-slot-capacity {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}

.time-slot-capacity i {
    margin-right: 6px;
    color: #6c757d;
    font-size: 14px;
}

.capacity-text {
    font-size: 13px;
    color: #6c757d;
    font-weight: 500;
}

.capacity-text.low-capacity {
    color: #ff6b6b;
    font-weight: 600;
}

.time-slot-description {
    font-size: 12px;
    color: #6c757d;
    margin-top: 8px;
    line-height: 1.4;
    font-style: italic;
}

.high-demand-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: linear-gradient(45deg, #ff6b6b, #ff8e53);
    color: white;
    border-radius: 12px;
    padding: 3px 8px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    box-shadow: 0 2px 4px rgba(255, 107, 107, 0.3);
}

.sold-out-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(248, 249, 250, 0.95);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sold-out-text {
    background: #dc3545;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
}

.selected-slot-summary {
    margin-top: 20px;
}

.selected-slot-summary .alert {
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
    border-left: 4px solid var(--primary-color, #5191fa);
}

.time-slots-error .alert {
    border-radius: 8px;
    border: none;
    background: #fff3cd;
    border-left: 4px solid #ffc107;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .time-slots-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .time-slot-item {
        padding: 15px;
    }
    
    .time-slot-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .selected-slot-summary .row {
        flex-direction: column;
    }
    
    .selected-slot-summary .col-md-4 {
        margin-top: 10px;
        text-align: left !important;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .time-slot-item {
        background: #2c3e50;
        border-color: #34495e;
        color: #ecf0f1;
    }
    
    .time-slot-item:hover:not(.disabled) {
        background: #34495e;
    }
    
    .time-slot-time {
        color: #ecf0f1;
    }
    
    .placeholder-content {
        background: #2c3e50;
        border-color: #34495e;
        color: #ecf0f1;
    }
}

/* Animation for slot updates */
.time-slot-item {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Loading animation */
.spinner-border {
    width: 2rem;
    height: 2rem;
}

/* Accessibility improvements */
.time-slot-item:focus {
    outline: 2px solid var(--primary-color, #5191fa);
    outline-offset: 2px;
}

.time-slot-item[aria-disabled="true"] {
    pointer-events: none;
}

/* Print styles */
@media print {
    .time-slots-grid {
        display: block;
    }
    
    .time-slot-item {
        break-inside: avoid;
        margin-bottom: 10px;
    }
    
    .high-demand-badge,
    .sold-out-overlay {
        display: none;
    }
}
</style>

{{-- JavaScript Integration --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Vue component integration with existing booking form
    if (typeof Vue !== 'undefined') {
        // Create Vue instance for time slots if not already created
        const timeSlotContainer = document.querySelector('.time-slots-vue-container');
        if (timeSlotContainer && !timeSlotContainer.__vue__) {
            new Vue({
                el: timeSlotContainer,
                data: {
                    selectedTimeSlot: null,
                    priceModifier: 0
                },
                methods: {
                    onTimeSlotSelected(slot) {
                        this.selectedTimeSlot = slot;
                        this.updateBookingForm(slot);
                        console.log('Time slot selected:', slot);
                    },
                    
                    onTimeSlotCleared() {
                        this.selectedTimeSlot = null;
                        this.priceModifier = 0;
                        this.updateBookingForm(null);
                        console.log('Time slot cleared');
                    },
                    
                    onPriceModifierChanged(modifier) {
                        this.priceModifier = modifier;
                        this.updatePricing(modifier);
                        console.log('Price modifier changed:', modifier);
                    },
                    
                    updateBookingForm(slot) {
                        // Update time_slot_id hidden input
                        const hiddenInput = document.querySelector('input[name="time_slot_id"]');
                        if (hiddenInput) {
                            hiddenInput.value = slot ? slot.id : '';
                        }
                        
                        // Handle start_time hidden input
                        let startTimeInput = document.querySelector('input[name="start_time"]');
                        const form = document.querySelector('.booking-form, form.bravo-booking-form');
                        
                        if (form) {
                            if (!startTimeInput) {
                                startTimeInput = document.createElement('input');
                                startTimeInput.type = 'hidden';
                                startTimeInput.name = 'start_time';
                                form.appendChild(startTimeInput);
                            }
                            
                            // Update start_time value, using fallback if needed
                            startTimeInput.value = slot ? (slot.start_time || slot.time || '') : '';
                            
                            // Trigger form validation
                            if (typeof jQuery !== 'undefined') {
                                $(form).trigger('timeSlotChanged', [slot]);
                            }
                        }
                    },
                    
                    updatePricing(modifier) {
                        // Integrate with existing pricing system
                        if (typeof updateBookingPrice === 'function') {
                            updateBookingPrice();
                        }
                        
                        // Trigger custom event for other components
                        document.dispatchEvent(new CustomEvent('priceModifierChanged', {
                            detail: { modifier: modifier }
                        }));
                    }
                }
            });
        }
    } else {
        console.warn('Vue.js not found. Time slots component will not be interactive.');
    }
});
</script>

@endif
