{{-- Enhanced Time Slots Component (jQuery Version) --}}
@if($row->meta && $row->meta->enable_time_slots)
<div class="bravo-form-group bravo-time-slots-section">
    <div class="time-slots-container" 
         data-tour-id="{{ $row->id }}" 
         data-auto-select="{{ setting_item('tour_time_slots_auto_select', true) ? 'true' : 'false' }}"
         data-show-price-modifier="{{ setting_item('tour_time_slots_show_price_modifier', true) ? 'true' : 'false' }}">
        <!-- Content will be populated by JavaScript -->
    </div>
</div>

{{-- Include required styles --}}
@push('css')
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
    font-size: 16px;
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
    border-radius: 12px;
    border: 2px dashed #dee2e6;
    transition: all 0.3s ease;
}

.placeholder-content:hover {
    border-color: #adb5bd;
    background: #e9ecef;
}

.placeholder-content i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.6;
    color: #6c757d;
}

.time-slots-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
    margin-top: 15px;
}

.time-slot-item {
    border: 2px solid #e9ecef;
    border-radius: 16px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: white;
    position: relative;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
}

.time-slot-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color, #5191fa), var(--secondary-color, #7c4dff));
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.time-slot-item:hover:not(.disabled)::before {
    transform: scaleX(1);
}

.time-slot-item:hover:not(.disabled) {
    border-color: var(--primary-color, #5191fa);
    box-shadow: 0 8px 25px rgba(81, 145, 250, 0.15);
    transform: translateY(-2px);
}

.time-slot-item.selected {
    border-color: var(--primary-color, #5191fa);
    background: linear-gradient(135deg, rgba(81, 145, 250, 0.05) 0%, rgba(124, 77, 255, 0.05) 100%);
    box-shadow: 0 8px 25px rgba(81, 145, 250, 0.2);
    transform: translateY(-2px);
}

.time-slot-item.selected::before {
    transform: scaleX(1);
}

.time-slot-item.disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background-color: #f8f9fa;
    transform: none !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.time-slot-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.time-slot-time {
    font-weight: 700;
    font-size: 18px;
    color: #2c3e50;
    line-height: 1.2;
}

.time-slot-price {
    font-weight: 600;
    color: var(--primary-color, #5191fa);
    font-size: 14px;
    background: rgba(81, 145, 250, 0.1);
    padding: 4px 10px;
    border-radius: 20px;
    white-space: nowrap;
}

.time-slot-capacity {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    padding: 8px 12px;
    background: rgba(108, 117, 125, 0.1);
    border-radius: 8px;
}

.time-slot-capacity i {
    margin-right: 8px;
    color: #6c757d;
    font-size: 16px;
}

.capacity-text {
    font-size: 14px;
    color: #495057;
    font-weight: 500;
}

.capacity-text.low-capacity {
    color: #dc3545;
    font-weight: 600;
}

.time-slot-description {
    font-size: 13px;
    color: #6c757d;
    margin-top: 10px;
    line-height: 1.4;
    font-style: italic;
    padding: 8px;
    background: rgba(248, 249, 250, 0.8);
    border-radius: 6px;
}

.high-demand-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: linear-gradient(45deg, #ff6b6b, #ff8e53);
    color: white;
    border-radius: 16px;
    padding: 4px 10px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.sold-out-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(248, 249, 250, 0.95);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(2px);
}

.sold-out-text {
    background: linear-gradient(45deg, #dc3545, #c82333);
    color: white;
    padding: 8px 16px;
    border-radius: 25px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
}

.selected-slot-summary {
    margin-top: 20px;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.selected-slot-summary .alert {
    border-radius: 16px;
    border: none;
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
    border-left: 5px solid var(--primary-color, #5191fa);
    box-shadow: 0 4px 12px rgba(81, 145, 250, 0.1);
}

.price-modifier {
    text-align: right;
}

.price-modifier strong {
    font-size: 16px;
    color: var(--primary-color, #5191fa);
}

.time-slots-error .alert {
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border-left: 4px solid #ffc107;
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.2);
}

/* Enhanced accessibility */
.time-slot-item:focus {
    outline: 3px solid var(--primary-color, #5191fa);
    outline-offset: 2px;
}

.time-slot-item[aria-disabled="true"] {
    pointer-events: none;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .time-slots-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .time-slot-item {
        padding: 16px;
    }
    
    .time-slot-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }
    
    .time-slot-time {
        font-size: 16px;
    }
    
    .selected-slot-summary .row {
        flex-direction: column;
    }
    
    .selected-slot-summary .col-md-4 {
        margin-top: 12px;
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
    
    .time-slot-description {
        background: rgba(52, 73, 94, 0.8);
        color: #bdc3c7;
    }
}

/* Print styles */
@media print {
    .time-slots-grid {
        display: block;
    }
    
    .time-slot-item {
        break-inside: avoid;
        margin-bottom: 10px;
        box-shadow: none;
        border: 1px solid #000;
    }
    
    .high-demand-badge,
    .sold-out-overlay {
        display: none;
    }
}

/* Animation for new slots appearing */
.time-slot-item {
    animation: fadeInUp 0.4s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Loading spinner animation */
.spinner-border {
    width: 2.5rem;
    height: 2.5rem;
    border-width: 0.3em;
}

/* Enhanced button styles */
.change-time-btn {
    border-radius: 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.change-time-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
@endpush

{{-- Include required JavaScript --}}
@push('js')
<script src="{{ asset('modules/Tour/js/time-slots-jquery.js') }}"></script>
<script>
$(document).ready(function() {
    // Integration with existing booking form
    $(document).on('timeSlotSelected', function(event, slot) {
        console.log('Time slot selected:', slot);
        
        // Update any existing booking calculation
        if (typeof calculateBookingTotal === 'function') {
            calculateBookingTotal();
        }
        
        // Show success message
        if (typeof showNotification === 'function') {
            showNotification('success', '{{ __("Time slot selected successfully") }}');
        }
    });
    
    $(document).on('timeSlotCleared', function() {
        console.log('Time slot cleared');
        
        // Update booking calculation
        if (typeof calculateBookingTotal === 'function') {
            calculateBookingTotal();
        }
    });
    
    $(document).on('priceModifierChanged', function(event, modifier) {
        console.log('Price modifier changed:', modifier);
        
        // Update the booking form price display
        updateBookingPriceDisplay(modifier);
    });
    
    function updateBookingPriceDisplay(priceModifier) {
        const $priceDisplay = $('.booking-price-display, .total-price');
        if ($priceDisplay.length === 0) return;
        
        // Get base price
        let basePrice = parseFloat($priceDisplay.data('base-price')) || 0;
        
        // Apply modifier
        let newPrice = basePrice + priceModifier;
        
        // Update display
        $priceDisplay.html(formatPrice(newPrice));
        
        // Show modifier separately if not zero
        const $modifierDisplay = $('.price-modifier-display');
        if (priceModifier !== 0) {
            const modifierText = (priceModifier > 0 ? '+' : '') + formatPrice(priceModifier);
            if ($modifierDisplay.length > 0) {
                $modifierDisplay.text(modifierText).show();
            } else {
                $priceDisplay.after(`<small class="price-modifier-display text-muted d-block">${modifierText} {{ __('time slot adjustment') }}</small>`);
            }
        } else {
            $modifierDisplay.hide();
        }
    }
    
    function formatPrice(price) {
        // Use your existing price formatting function or implement one
        if (typeof window.formatMoney === 'function') {
            return window.formatMoney(price);
        }
        
        // Fallback formatting
        return new Intl.NumberFormat('{{ app()->getLocale() }}', {
            style: 'currency',
            currency: '{{ setting_item("currency_main") ?? "USD" }}'
        }).format(price);
    }
    
    // Form validation integration
    $('form.booking-form, form.bravo-booking-form').on('submit', function(e) {
        const $form = $(this);
        const $timeSlotInput = $form.find('input[name="time_slot_id"]');
        
        if ($timeSlotInput.length > 0 && $timeSlotInput.prop('required') && !$timeSlotInput.val()) {
            e.preventDefault();
            
            // Scroll to time slots section
            $('html, body').animate({
                scrollTop: $('.bravo-time-slots-section').offset().top - 100
            }, 500);
            
            // Show error message
            if (typeof showNotification === 'function') {
                showNotification('error', '{{ __("Please select a time slot") }}');
            } else {
                alert('{{ __("Please select a time slot") }}');
            }
            
            return false;
        }
    });
});
</script>
@endpush

@endif
