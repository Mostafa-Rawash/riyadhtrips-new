/**
 * jQuery-based Time Slots Component (Fallback)
 * For environments where Vue.js is not available or preferred
 */

(function($) {
    'use strict';
    
    class TimeSlots {
        constructor(container, options = {}) {
            this.container = $(container);
            this.options = {
                tourId: null,
                apiEndpoint: '/api/tour/time-slots/available',
                updateInterval: 30000, // 30 seconds
                autoSelect: true,
                showPriceModifier: true,
                ...options
            };
            
            this.selectedSlot = null;
            this.timeSlots = [];
            this.currentDate = null;
            this.currentGuests = 1;
            this.updateTimer = null;
            
            this.init();
        }
        
        init() {
            this.bindEvents();
            this.createElements();
            
            // Get initial values
            this.currentDate = $('input[name="start_date"]').val();
            this.currentGuests = this.getGuestCount();
            
            if (this.currentDate) {
                this.loadTimeSlots();
            }
            
            this.startAutoUpdate();
        }
        
        createElements() {
            this.container.html(`
                <div class="time-slots-header">
                    <label class="form-label">
                        <i class="icofont-clock-time"></i>
                        ${this.__('Select Your Preferred Time')} 
                        <span class="required">*</span>
                    </label>
                    <div class="time-slots-info">
                        <small class="text-muted">
                            <i class="icofont-info-circle"></i>
                            ${this.__('Times shown are available for your selected date and party size')}
                        </small>
                    </div>
                </div>

                <div class="time-slots-container">
                    <div class="time-slots-loading" style="display: none;">
                        <div class="loading-spinner">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">${this.__('Loading...')}</span>
                            </div>
                            <p class="mt-2">${this.__('Finding available times...')}</p>
                        </div>
                    </div>

                    <div class="time-slots-placeholder">
                        <div class="placeholder-content">
                            <i class="icofont-calendar text-muted"></i>
                            <p class="text-muted mb-0">${this.__('Please select a date to view available time slots')}</p>
                        </div>
                    </div>

                    <div class="time-slots-grid" style="display: none;"></div>

                    <div class="time-slots-error" style="display: none;">
                        <div class="alert alert-warning">
                            <i class="icofont-warning"></i>
                            <span class="error-message"></span>
                        </div>
                    </div>

                    <input type="hidden" name="time_slot_id" required>
                </div>

                <div class="selected-slot-summary" style="display: none;">
                    <div class="alert alert-info">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <strong>${this.__('Selected Time:')}</strong>
                                <span class="selected-time-display"></span>
                                <div class="mt-1">
                                    <small class="text-muted selected-slot-details"></small>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="selected-slot-price"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-1 change-time-btn">
                                    ${this.__('Change Time')}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }
        
        bindEvents() {
            const self = this;
            
            // Date change event
            $(document).on('change', 'input[name="start_date"]', function() {
                self.currentDate = $(this).val();
                self.clearSelection();
                
                if (self.currentDate) {
                    self.loadTimeSlots();
                } else {
                    self.resetState();
                }
            });
            
            // Guest count change event
            $(document).on('change', 'input[name="guests"], select[name="guests"]', function() {
                self.currentGuests = self.getGuestCount();
                if (self.currentDate) {
                    self.loadTimeSlots();
                }
            });
            
            // Person types change (for tours with person types enabled)
            $(document).on('change', 'input[name^="person_types"]', function() {
                self.currentGuests = self.getGuestCount();
                if (self.currentDate) {
                    self.loadTimeSlots();
                }
            });
            
            // Change time button
            this.container.on('click', '.change-time-btn', function() {
                self.clearSelection();
            });
            
            // Window unload cleanup
            $(window).on('beforeunload', function() {
                self.cleanup();
            });
        }
        
        async loadTimeSlots() {
            if (!this.currentDate || !this.options.tourId) return;
            
            this.showLoading();
            
            try {
                const response = await $.ajax({
                    url: this.options.apiEndpoint,
                    method: 'GET',
                    data: {
                        tour_id: this.options.tourId,
                        date: this.currentDate,
                        guests: this.currentGuests
                    },
                    dataType: 'json'
                });
                
                if (response.success) {
                    this.timeSlots = response.time_slots || [];
                    this.displayTimeSlots();
                    
                    // Auto-select if only one available slot
                    const availableSlots = this.timeSlots.filter(slot => 
                        !slot.is_sold_out && slot.remaining_capacity >= this.currentGuests
                    );
                    
                    if (this.options.autoSelect && availableSlots.length === 1) {
                        this.selectTimeSlot(availableSlots[0]);
                    }
                } else {
                    this.showError(response.message || this.__('Failed to load time slots'));
                }
            } catch (error) {
                console.error('Error loading time slots:', error);
                let message = this.__('Network error. Please try again.');
                
                if (error.responseJSON && error.responseJSON.message) {
                    message = error.responseJSON.message;
                }
                
                this.showError(message);
            }
        }
        
        displayTimeSlots() {
            this.hideLoading();
            this.container.find('.time-slots-placeholder, .time-slots-error').hide();
            
            const grid = this.container.find('.time-slots-grid');
            grid.empty().show();
            
            if (this.timeSlots.length === 0) {
                this.showError(this.__('No time slots available for the selected date'));
                return;
            }
            
            this.timeSlots.forEach(slot => {
                const slotElement = this.createTimeSlotElement(slot);
                grid.append(slotElement);
            });
        }
        
        createTimeSlotElement(slot) {
            const isDisabled = slot.is_sold_out || slot.remaining_capacity < this.currentGuests;
            const isLowCapacity = slot.remaining_capacity <= 3 && slot.remaining_capacity > 0;
            const isHighDemand = slot.recent_bookings > 0 && slot.remaining_capacity < 5;
            
            let capacityText = '';
            let capacityClass = '';
            
            if (slot.is_sold_out) {
                capacityText = this.__('Sold Out');
                capacityClass = 'low-capacity';
            } else if (isLowCapacity) {
                capacityText = this.__('Only :count spots left!', { count: slot.remaining_capacity });
                capacityClass = 'low-capacity';
            } else {
                capacityText = this.__(':count spaces available', { count: slot.remaining_capacity });
            }
            
            const priceDisplay = slot.price_modifier != 0 ? 
                (slot.price_modifier > 0 ? '+' : '') + this.formatMoney(slot.price_modifier) : '';
            
            const $element = $(`
                <div class="time-slot-item ${isDisabled ? 'disabled' : ''}" 
                     data-slot-id="${slot.id}"
                     ${isDisabled ? 'aria-disabled="true"' : ''}
                     role="button"
                     tabindex="${isDisabled ? -1 : 0}">
                    
                    ${isHighDemand ? `<div class="high-demand-badge">${this.__('Popular')}</div>` : ''}
                    
                    <div class="time-slot-header">
                        <div class="time-slot-time">${slot.formatted_time}</div>
                        ${priceDisplay ? `<div class="time-slot-price">${priceDisplay}</div>` : ''}
                    </div>
                    
                    <div class="time-slot-capacity">
                        <i class="icofont-users-alt-4"></i>
                        <span class="capacity-text ${capacityClass}">${capacityText}</span>
                    </div>
                    
                    ${slot.description ? `<div class="time-slot-description">${slot.description}</div>` : ''}
                    
                    ${slot.is_sold_out ? `
                        <div class="sold-out-overlay">
                            <div class="sold-out-text">${this.__('Sold Out')}</div>
                        </div>
                    ` : ''}
                </div>
            `);
            
            if (!isDisabled) {
                $element.on('click keypress', (e) => {
                    if (e.type === 'keypress' && e.which !== 13 && e.which !== 32) return;
                    e.preventDefault();
                    this.selectTimeSlot(slot);
                });
            }
            
            return $element;
        }
        
        selectTimeSlot(slot) {
            if (slot.is_sold_out || slot.remaining_capacity < this.currentGuests) {
                return;
            }
            
            // Clear previous selection
            this.container.find('.time-slot-item').removeClass('selected');
            
            // Select new slot
            this.container.find(`.time-slot-item[data-slot-id="${slot.id}"]`).addClass('selected');
            
            // Update internal state
            this.selectedSlot = { ...slot };
            
            // Update form
            this.container.find('input[name="time_slot_id"]').val(slot.id);
            
            // Update summary
            this.updateSelectedSlotSummary(slot);
            
            // Trigger events
            this.container.trigger('timeSlotSelected', [slot]);
            $(document).trigger('timeSlotSelected', [slot]);
            
            // Update pricing
            this.updatePricing(slot.price_modifier);
        }
        
        updateSelectedSlotSummary(slot) {
            this.container.find('.selected-time-display').text(slot.formatted_time);
            
            let details = this.__(':capacity guests max', { capacity: slot.max_guests });
            if (slot.description) {
                details += ' • ' + slot.description;
            }
            
            this.container.find('.selected-slot-details').text(details);
            
            if (slot.price_modifier != 0) {
                const modifier = slot.price_modifier > 0 ? '+' : '';
                this.container.find('.selected-slot-price').html(`
                    <div class="price-modifier">
                        <strong>${modifier}${this.formatMoney(slot.price_modifier)}</strong>
                        <small class="text-muted d-block">${this.__('Time slot adjustment')}</small>
                    </div>
                `);
            } else {
                this.container.find('.selected-slot-price').empty();
            }
            
            this.container.find('.selected-slot-summary').show();
            this.container.find('.time-slots-grid').hide();
        }
        
        clearSelection() {
            this.selectedSlot = null;
            this.container.find('input[name="time_slot_id"]').val('');
            this.container.find('.time-slot-item').removeClass('selected');
            this.container.find('.selected-slot-summary').hide();
            this.container.find('.time-slots-grid').show();
            
            // Trigger events
            this.container.trigger('timeSlotCleared');
            $(document).trigger('timeSlotCleared');
            
            // Reset pricing
            this.updatePricing(0);
        }
        
        resetState() {
            this.timeSlots = [];
            this.clearSelection();
            this.container.find('.time-slots-loading, .time-slots-grid, .time-slots-error, .selected-slot-summary').hide();
            this.container.find('.time-slots-placeholder').show();
        }
        
        updatePricing(priceModifier) {
            // Trigger custom events for pricing updates
            this.container.trigger('priceModifierChanged', [priceModifier]);
            $(document).trigger('priceModifierChanged', [priceModifier]);
            
            // Integrate with existing booking form pricing if available
            if (typeof updateBookingPrice === 'function') {
                updateBookingPrice();
            }
        }
        
        getGuestCount() {
            // Try different selectors for guest count
            let guests = parseInt($('input[name="guests"]').val()) || 
                        parseInt($('select[name="guests"]').val()) || 1;
            
            // Check for person types if enabled
            const personTypes = $('input[name^="person_types"]');
            if (personTypes.length > 0) {
                let totalPersons = 0;
                personTypes.each(function() {
                    totalPersons += parseInt($(this).val()) || 0;
                });
                if (totalPersons > 0) {
                    guests = totalPersons;
                }
            }
            
            return Math.max(1, guests);
        }
        
        showLoading() {
            this.container.find('.time-slots-placeholder, .time-slots-grid, .time-slots-error').hide();
            this.container.find('.time-slots-loading').show();
        }
        
        hideLoading() {
            this.container.find('.time-slots-loading').hide();
        }
        
        showError(message) {
            this.container.find('.time-slots-placeholder, .time-slots-grid, .time-slots-loading').hide();
            this.container.find('.time-slots-error .error-message').text(message);
            this.container.find('.time-slots-error').show();
        }
        
        startAutoUpdate() {
            const self = this;
            this.updateTimer = setInterval(() => {
                if (self.currentDate && self.timeSlots.length > 0) {
                    self.getRealTimeUpdates();
                }
            }, this.options.updateInterval);
        }
        
        async getRealTimeUpdates() {
            try {
                const response = await $.ajax({
                    url: '/api/tour/time-slots/updates',
                    method: 'GET',
                    data: {
                        tour_id: this.options.tourId,
                        date: this.currentDate
                    },
                    dataType: 'json'
                });
                
                if (response.success && response.updates) {
                    this.updateSlotsWithRealTimeData(response.updates);
                }
            } catch (error) {
                // Silently handle real-time update errors
                console.warn('Real-time update failed:', error);
            }
        }
        
        updateSlotsWithRealTimeData(updates) {
            updates.forEach(updatedSlot => {
                const $slotElement = this.container.find(`.time-slot-item[data-slot-id="${updatedSlot.id}"]`);
                if ($slotElement.length === 0) return;
                
                // Update capacity display
                const $capacityText = $slotElement.find('.capacity-text');
                let capacityText = '';
                let capacityClass = '';
                
                if (updatedSlot.is_sold_out) {
                    capacityText = this.__('Sold Out');
                    capacityClass = 'low-capacity';
                    $slotElement.addClass('disabled').attr('aria-disabled', 'true');
                    
                    // Add sold out overlay if not exists
                    if ($slotElement.find('.sold-out-overlay').length === 0) {
                        $slotElement.append(`
                            <div class="sold-out-overlay">
                                <div class="sold-out-text">${this.__('Sold Out')}</div>
                            </div>
                        `);
                    }
                } else {
                    $slotElement.removeClass('disabled').removeAttr('aria-disabled');
                    $slotElement.find('.sold-out-overlay').remove();
                    
                    if (updatedSlot.remaining_capacity <= 3) {
                        capacityText = this.__('Only :count spots left!', { count: updatedSlot.remaining_capacity });
                        capacityClass = 'low-capacity';
                    } else {
                        capacityText = this.__(':count spaces available', { count: updatedSlot.remaining_capacity });
                        capacityClass = '';
                    }
                }
                
                $capacityText.text(capacityText).attr('class', `capacity-text ${capacityClass}`);
                
                // Update high demand badge
                if (updatedSlot.high_demand && $slotElement.find('.high-demand-badge').length === 0) {
                    $slotElement.prepend(`<div class="high-demand-badge">${this.__('Popular')}</div>`);
                } else if (!updatedSlot.high_demand) {
                    $slotElement.find('.high-demand-badge').remove();
                }
                
                // Update internal data
                const slotIndex = this.timeSlots.findIndex(slot => slot.id === updatedSlot.id);
                if (slotIndex !== -1) {
                    this.timeSlots[slotIndex] = { ...this.timeSlots[slotIndex], ...updatedSlot };
                }
            });
        }
        
        formatMoney(amount) {
            // Get currency from global settings or default
            const currency = window.defaultCurrency || 'USD';
            const locale = document.documentElement.lang || 'en';
            
            try {
                return new Intl.NumberFormat(locale, {
                    style: 'currency',
                    currency: currency
                }).format(Math.abs(amount));
            } catch (e) {
                // Fallback formatting
                return '$' + Math.abs(amount).toFixed(2);
            }
        }
        
        __(key, replacements = {}) {
            // Simple translation function - integrate with your app's i18n
            let translation = key;
            
            if (window.i18n && window.i18n[key]) {
                translation = window.i18n[key];
            }
            
            // Replace placeholders
            Object.keys(replacements).forEach(placeholder => {
                translation = translation.replace(new RegExp(`:${placeholder}`, 'g'), replacements[placeholder]);
            });
            
            return translation;
        }
        
        cleanup() {
            if (this.updateTimer) {
                clearInterval(this.updateTimer);
                this.updateTimer = null;
            }
        }
        
        // Public API methods
        getSelectedSlot() {
            return this.selectedSlot;
        }
        
        refresh() {
            if (this.currentDate) {
                this.loadTimeSlots();
            }
        }
        
        destroy() {
            this.cleanup();
            this.container.empty();
        }
    }
    
    // jQuery plugin wrapper
    $.fn.timeSlots = function(options) {
        return this.each(function() {
            const $this = $(this);
            let instance = $this.data('timeSlots');
            
            if (!instance) {
                instance = new TimeSlots(this, options);
                $this.data('timeSlots', instance);
            }
            
            return instance;
        });
    };
    
    // Auto-initialize
    $(document).ready(function() {
        $('.time-slots-container[data-tour-id]').each(function() {
            const $container = $(this);
            const tourId = $container.data('tour-id');
            
            if (tourId) {
                $container.timeSlots({
                    tourId: tourId,
                    autoSelect: $container.data('auto-select') !== false,
                    showPriceModifier: $container.data('show-price-modifier') !== false
                });
            }
        });
    });
    
})(jQuery);
