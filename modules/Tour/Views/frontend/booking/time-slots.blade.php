<div class="bravo_single_book_wrap @if(setting_item('tour_enable_inbox')) has-vendor-box @endif">



    <div class="bravo_single_book">



        <div id="bravo_tour_book_app" v-cloak>



            @if($row->discount_percent)



                <div class="tour-sale-box">



                    <span class="sale_class box_sale sale_small">{{$row->discount_percent}}</span>



                </div>



            @endif



            <div class="form-head">



                <div class="price">



                    <span class="label">



                        {{__("from")}}



                    </span>



                    <span class="value">



                        <span class="onsale">{{ $row->display_sale_price }}</span>



                        <span class="text-lg">{{ $row->display_price }}</span>



                    </span>



                </div>



            </div>



            <div class="nav-enquiry" v-if="is_form_enquiry_and_book">



                <div class="enquiry-item active" >



                    <span>{{ __("Book") }}</span>



                </div>



                <div class="enquiry-item" data-toggle="modal" data-target="#enquiry_form_modal">



                    <span>{{ __("Enquiry") }}</span>



                </div>



            </div>



            <div class="form-book" :class="{'d-none':enquiry_type!='book'}">



                <div class="form-content">



                    <div class="form-group form-date-field form-date-search clearfix " data-format="{{get_moment_date_format()}}">



                        <div class="d-flex p-2  flex-wrap clearfix text-center" v-if="is_fixed_date">



                            <div class="w-50 py-3 flex-grow-1">



                                <div class="font-weight-bold">{{__("Tour Start Date")}}</div>



                                <span>@{{ start_date_html }}</span>



                            </div>



                            <div class="w-50 py-3 flex-grow-1 border-left">



                                <div class="font-weight-bold">{{__("Tour End Date")}}</div>



                                <span>@{{ end_date_html }}</span>



                            </div>



                            <div class="w-100 py-3 flex-grow-1 border-top">



                                <div class="font-weight-bold">{{__("Last Booking Date")}}</div>



                                <span>@{{ last_booking_date_html }}</span>



                            </div>



                        </div>



                        <div class="date-wrapper clearfix" @click="openStartDate" v-else>



                            <div class="check-in-wrapper">



                                <label>{{__("Start Date")}}</label>



                                <div class="render check-in-render">@{{start_date_html}}</div>



                            </div>



                            <i class="fa fa-angle-down arrow"></i>



                        </div>



                        <input type="text" class="start_date" ref="start_date" style="height: 1px; visibility: hidden">



                    </div>



                    <div class="" v-if="person_types">



                        <div class="form-group form-guest-search" v-for="(type,index) in person_types">



                            <div class="guest-wrapper d-flex justify-content-between align-items-center">



                                <div class="flex-grow-1">



                                    <label>@{{type.name}}</label>



                                    <div class="render check-in-render">@{{type.desc}}</div>



                                    <div class="render check-in-render">@{{type.display_price}} {{__("per person")}}</div>



                                </div>



                                <div class="flex-shrink-0">



                                    <div class="input-number-group">



                                        <i class="icon ion-ios-remove-circle-outline" @click="minusPersonType(type)"></i>



                                        <span class="input"><input type="number" v-model="type.number" min="1" @change="changePersonType(type)"/></span>



                                        <i class="icon ion-ios-add-circle-outline" @click="addPersonType(type)"></i>



                                    </div>



                                </div>



                            </div>



                        </div>



                    </div>

                    <div class="form-group form-guest-search" v-else>



                        <div class="guest-wrapper d-flex justify-content-between align-items-center">



                            <div class="flex-grow-1">



                                <label>{{__("Guests")}}</label>



                            </div>



                            <div class="flex-shrink-0">



                                <div class="input-number-group">



                                    <i class="icon ion-ios-remove-circle-outline" @click="minusGuestsType()"></i>



                                    <span class="input"><input type="number" v-model="guests" min="0"/></span>



                                    <i class="icon ion-ios-add-circle-outline" @click="addGuestsType()"></i>



                                </div>



                            </div>



                        </div>



                    </div>

                    <div class="form-section-group form-group" v-if="packages.length">
                        <h4 class="form-section-title">{{ __('Packages:') }}</h4>
                    
                        <div class="form-group" v-for="(type, index) in packages" :key="index">
                            <div class="extra-price-wrap d-flex justify-content-between">
                                <!-- Checkbox and Package Name -->
                                <div class="flex-grow-1">
                                    <label>
                                        <input type="checkbox" true-value="1" false-value="0" v-model="type.enable"> 
                                        @{{ type.name }}
                                    </label>
                                    <div class="render check-in-render" v-if="type.description">@{{ type.description }}</div>
                                    <div class="render" v-if="type.price">(@{{ type.display_price }})</div>
                                </div>
                    
                                <!-- Start Times as Radio Buttons (if needed) -->
                                <div class="flex-shrink-0" v-if="type.start_times && type.start_times.length">
                                    <div v-for="(time, timeIndex) in type.start_times" :key="timeIndex">
                                        <label>
                                            <input 
                                                type="radio" 
                                                :name="'start_time_' + index" 
                                                :value="time" 
                                                v-model="type.selected_time"> 
                                            @{{ time }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Time Slots Section - Enhanced Design -->
                    @if(isset($row->meta->enable_time_slots) && $row->meta->enable_time_slots)
                    <div class="form-section-group form-group" v-if="enable_time_slots && start_date">
                        <h4 class="form-section-title">
                            <i class="fa fa-clock"></i> {{__("Select Time")}}
                        </h4>
                        
                        <!-- Loading State -->
                        <div v-if="loading_time_slots" class="time-slots-loading text-center py-4">
                            <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
                                <span class="sr-only">{{__("Loading...")}}</span>
                            </div>
                            <div class="mt-2 text-muted">{{__("Loading available times...")}}</div>
                        </div>
                        
                        <!-- Time Slots Grid -->
                        <div v-else-if="available_time_slots && available_time_slots.length" class="time-slots-container">
                            <div class="time-slots-grid row">
                                <div v-for="(slot, index) in available_time_slots" :key="index" class="mb-3">
                                    <div class="time-slot-card" 
                                         :class="{
                                             'selected': selected_time_slot && selected_time_slot.id === slot.id,
                                             'high-demand': slot.is_high_demand,
                                             'low-capacity': slot.remaining_capacity <= 3 && slot.remaining_capacity > 0,
                                             'insufficient-capacity': !isSlotBookableForCurrentGuests(slot),
                                             'sold-out': slot.remaining_capacity === 0 || slot.is_sold_out,
                                             'disabled': !isSlotBookableForCurrentGuests(slot) || slot.is_sold_out
                                         }"
                                         @click="handleTimeSlotClick(slot)"
                                         :title="getSlotTooltip(slot)"
                                         role="button"
                                         :tabindex="isSlotBookableForCurrentGuests(slot) ? 0 : -1"
                                         :aria-disabled="!isSlotBookableForCurrentGuests(slot)"
                                         :aria-label="getSlotAriaLabel(slot)">
                                        
                                        <div class="time-slot-header">
                                            <div class="time-display">
                                                <i class="fa fa-clock"></i>
                                                <span class="start-time">@{{ formatTime(slot.start_time) }}</span>
                                                <span v-if="slot.end_time" class="end-time">- @{{ formatTime(slot.end_time) }}</span>
                                            </div>
                                            
                                            <div class="selection-indicator" v-if="selected_time_slot && selected_time_slot.id === slot.id">
                                                <i class="fa fa-check-circle text-success"></i>
                                            </div>
                                        </div>
                                        
                                        <div class="time-slot-info">
                                            <div v-if="slot.description" class="slot-description">
                                                @{{ slot.description }}
                                            </div>
                                            
                                            <div class="capacity-info d-flex justify-content-between align-items-center">
                                                <div class="capacity-text">
                                                    <span class="available-spots" 
                                                          :class="{
                                                              'text-danger': !isSlotBookableForCurrentGuests(slot) && slot.remaining_capacity > 0,
                                                              'text-muted': slot.remaining_capacity === 0 || slot.is_sold_out,
                                                              'text-success': isSlotBookableForCurrentGuests(slot) && slot.remaining_capacity > 3,
                                                              'text-warning': isSlotBookableForCurrentGuests(slot) && slot.remaining_capacity <= 3
                                                          }">
                                                        <i class="fa fa-users"></i>
                                                        <span v-if="slot.remaining_capacity === 0 || slot.is_sold_out">
                                                            {{__("Fully Booked")}}
                                                        </span>
                                                        <span v-else-if="!isSlotBookableForCurrentGuests(slot)">
                                                            {{__("Need")}} @{{ getGuestCount() }}, {{__("only")}} @{{ slot.remaining_capacity }} {{__("left")}}
                                                        </span>
                                                        <span v-else>
                                                            @{{ slot.remaining_capacity }} {{__("spots left")}}
                                                        </span>
                                                    </span>
                                                </div>
                                                
                                                <div class="price-modifier" v-if="slot.price_modifier && slot.price_modifier !== 0">
                                                    <span class="modifier-amount" :class="slot.price_modifier > 0 ? 'text-success' : 'text-danger'">
                                                        @{{ slot.price_modifier > 0 ? '+' : '' }}@{{ formatMoney(slot.price_modifier) }}
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <!-- Enhanced Status Indicators -->
                                            <div class="demand-indicators mt-2">
                                                <span v-if="slot.remaining_capacity === 0 || slot.is_sold_out" class="badge badge-secondary">
                                                    <i class="fa fa-times-circle"></i> {{__("Fully Booked")}}
                                                </span>
                                                <span v-else-if="!isSlotBookableForCurrentGuests(slot)" class="badge badge-danger">
                                                    <i class="fa fa-exclamation-triangle"></i> {{__("Insufficient Capacity")}}
                                                </span>
                                                <span v-else-if="slot.is_high_demand" class="badge badge-warning">
                                                    <i class="fa fa-fire"></i> {{__("High Demand")}}
                                                </span>
                                                <span v-else-if="slot.remaining_capacity <= 3" class="badge badge-warning">
                                                    <i class="fa fa-clock"></i> {{__("Almost Full")}}
                                                </span>
                                                <span v-else class="badge badge-success">
                                                    <i class="fa fa-check"></i> {{__("Available")}}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Disabled Overlay -->
                                        <div v-if="!isSlotBookableForCurrentGuests(slot) || slot.is_sold_out" class="disabled-overlay">
                                            <div class="disabled-content">
                                                <i class="fa fa-ban" v-if="!isSlotBookableForCurrentGuests(slot) && !slot.is_sold_out"></i>
                                                <i class="fa fa-times-circle" v-if="slot.is_sold_out"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Show Sold Out Slots Toggle -->
                            <div v-if="sold_out_slots && sold_out_slots.length" class="sold-out-toggle mt-3">
                                <button type="button" class="btn btn-link btn-sm" @click="show_sold_out_slots = !show_sold_out_slots">
                                    <i class="fa" :class="show_sold_out_slots ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    <span v-if="show_sold_out_slots">{{__("Hide Unavailable Times")}}</span>
                                    <span v-else>{{__("Show Unavailable Times")}} (@{{ sold_out_slots.length }})</span>
                                </button>
                            </div>
                            
                            <!-- Sold Out Slots -->
                            <div v-if="show_sold_out_slots && sold_out_slots && sold_out_slots.length" class="sold-out-slots mt-3">
                                <h6 class="text-muted mb-2">{{__("Unavailable Times")}}</h6>
                                <div class="row">
                                    <div v-for="(slot, index) in sold_out_slots" :key="'sold-' + index" class="col-md-6 col-lg-4 mb-2">
                                        <div class="time-slot-card sold-out disabled">
                                            <div class="time-slot-header">
                                                <div class="time-display">
                                                    <i class="fa fa-clock"></i>
                                                    <span class="start-time">@{{ formatTime(slot.start_time) }}</span>
                                                    <span v-if="slot.end_time" class="end-time">- @{{ formatTime(slot.end_time) }}</span>
                                                </div>
                                                <div class="sold-out-badge">
                                                    <i class="fa fa-times-circle text-danger"></i>
                                                </div>
                                            </div>
                                            <div class="sold-out-reason text-muted small text-center">
                                                <span v-if="slot.is_sold_out">{{__("Fully Booked")}}</span>
                                                <span v-else-if="slot.is_cutoff_reached">{{__("Booking Closed")}}</span>
                                                <span v-else>{{__("Unavailable")}}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- No Time Slots Available -->
                        <div v-else-if="!loading_time_slots && enable_time_slots" class="no-time-slots text-center py-4">
                            <div class="mb-3">
                                <i class="fa fa-calendar-times fa-3x text-muted"></i>
                            </div>
                            <h6 class="text-muted">{{__("No available time slots")}}</h6>
                            <p class="text-muted small mb-0">{{__("Please select a different date or contact us for availability.")}}</p>
                        </div>
                        
                        <!-- Selected Time Summary -->
                        <div v-if="selected_time_slot" class="selected-time-summary mt-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{__("Selected Time:")}}</strong>
                                    <span class="ml-2">
                                        <i class="fa fa-clock text-primary"></i>
                                        @{{ formatTime(selected_time_slot.start_time) }}
                                        <span v-if="selected_time_slot.end_time">- @{{ formatTime(selected_time_slot.end_time) }}</span>
                                    </span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="clearTimeSlot()">
                                    <i class="fa fa-times"></i> {{__("Change")}}
                                </button>
                            </div>
                            <div v-if="selected_time_slot.description" class="mt-2 text-muted small">
                                @{{ selected_time_slot.description }}
                            </div>
                        </div>
                        
                        <!-- Hidden Inputs for Form Submission -->
                        <input type="hidden" name="time_slot_id" :value="time_slot_id">
                        <input type="hidden" name="start_time" :value="start_time">
                    </div>
                    @endif

                    <div class="form-section-group form-group" v-if="extra_price.length">



                        <h4 class="form-section-title">{{__('Extra prices:')}}</h4>



                        <div class="form-group" v-for="(type,index) in extra_price">



                            <div class="extra-price-wrap d-flex justify-content-between">



                                <div class="flex-grow-1">



                                    <label><input type="checkbox" true-value="1" false-value="0" v-model="type.enable"> @{{type.name}}</label>



                                    <div class="render" v-if="type.price_type">(@{{type.price_type}})</div>



                                </div>



                                <div class="flex-shrink-0">@{{type.price_html}}</div>



                            </div>



                        </div>



                    </div>



                    <div class="form-section-group form-group" v-if="discount_by_people_output.length">



                        <h4 class="form-section-title">{{__('Discounts:')}}</h4>



                        <div class="extra-price-wrap d-flex justify-content-between" v-for="(type,index) in discount_by_people_output">



                            <div class="flex-grow-1">



                                <span class="render" v-if='type.to'>



                                    @{{type.from}} - @{{type.to}} {{__('guests')}}



                                </span>



                                <span class="render" v-else >



                                    {{__('from')}} @{{type.from}} {{__('guests')}}



                                </span>



                            </div>



                            <div class="flex-shrink-0">



                                <div class="unit">



                                    - @{{ formatMoney(type.total) }}



                                </div>



                            </div>



                        </div>



                    </div>



                    <div class="form-section-group form-group-padding" v-if="buyer_fees.length">



                        <div class="extra-price-wrap d-flex justify-content-between" v-for="(type,index) in buyer_fees">



                            <div class="flex-grow-1">



                                <label>@{{type.type_name}}



                                    <i class="icofont-info-circle" v-if="type.desc" data-toggle="tooltip" data-placement="top" :title="type.type_desc"></i>



                                </label>



                                <div class="render" v-if="type.price_type">(@{{type.price_type}})</div>



                            </div>



                            <div class="flex-shrink-0">



                                <div class="unit" v-if='type.unit == "percent"'>



                                    @{{ type.price }}%



                                </div>



                                <div class="unit" v-else >



                                    @{{ formatMoney(type.price) }}



                                </div>



                            </div>



                        </div>



                    </div>



                </div>



                <ul class="form-section-total list-unstyled" v-if="total_price > 0">



                    <li>



                        <label>{{__("Total")}}</label>



                        <span class="price">@{{total_price_html}}</span>



                    </li>



                    <li v-if="is_deposit_ready">



                        <label for="">{{__("Pay now")}}</label>



                        <span class="price">@{{pay_now_price_html}}</span>



                    </li>



                </ul>



                <div v-html="html"></div>



                <div class="submit-group">



                    <a class="btn btn-large" @click="doSubmit($event)" :class="{'disabled':onSubmit,'btn-success':(step == 2),'btn-primary':step == 1}" name="submit">



                        <span>{{__("BOOK NOW")}}</span>



                        <i v-show="onSubmit" class="fa fa-spinner fa-spin"></i>



                    </a>



                    <div class="alert-text mt10" v-show="message.content" v-html="message.content" :class="{'danger':!message.type,'success':message.type}"></div>



                </div>



            </div>



            <div class="form-send-enquiry" v-show="enquiry_type=='enquiry'">



                <button class="btn btn-primary" data-toggle="modal" data-target="#enquiry_form_modal">



                    {{ __("Contact Now") }}



                </button>



            </div>



        </div>



    </div>



</div>



@include("Booking::frontend.global.enquiry-form",['service_type'=>'tour'])

<style>
/* Time Slots Enhanced Styling */
.time-slots-container {
    margin-top: 15px;
}

.time-slot-card {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fff;
    position: relative;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.time-slot-card:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.15);
    transform: translateY(-2px);
}

.time-slot-card.selected {
    border-color: #007bff;
    background: #f8f9ff;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
}

.time-slot-card.high-demand {
    border-color: #ffc107;
    background: #fffdf5;
}

.time-slot-card.low-capacity {
    border-color: #dc3545;
    background: #fff5f5;
}

.time-slot-card.insufficient-capacity {
    border-color: #dc3545;
    background: #fff5f5;
    position: relative;
}

.time-slot-card.insufficient-capacity::before {
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

.time-slot-card.sold-out {
    border-color: #6c757d;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    opacity: 0.7;
}

.time-slot-card.disabled {
    cursor: not-allowed !important;
    pointer-events: none !important;
    opacity: 0.6;
    position: relative;
}

.time-slot-card.disabled:hover {
    border-color: #e9ecef !important;
    box-shadow: none !important;
    transform: none !important;
}

.time-slot-card.insufficient-capacity {
    cursor: not-allowed !important;
    pointer-events: none !important;
    border-color: #dc3545 !important;
    background: #fff5f5 !important;
}

.time-slot-card.insufficient-capacity .available-spots {
    color: #dc3545 !important;
    font-weight: 600;
}

.time-slot-card.sold-out {
    cursor: not-allowed !important;
    pointer-events: none !important;
}

.time-slot-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.time-display {
    flex: 1;
}

.time-display i {
    color: #6c757d;
    margin-right: 5px;
}

.start-time {
    font-weight: 600;
    font-size: 16px;
    color: #495057;
}

.end-time {
    font-size: 14px;
    color: #6c757d;
    margin-left: 5px;
}

.selection-indicator {
    color: #007bff;
    font-size: 18px;
}

.time-slot-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.slot-description {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 8px;
    line-height: 1.3;
}

.capacity-info {
    margin-bottom: 8px;
}

.available-spots {
    font-size: 12px;
    color: #28a745;
    font-weight: 500;
}

.available-spots i {
    margin-right: 3px;
}

.price-modifier {
    font-size: 12px;
    font-weight: 600;
}

.modifier-amount {
    padding: 2px 6px;
    border-radius: 3px;
    background: rgba(0, 0, 0, 0.05);
}

.demand-indicators {
    display: flex;
    justify-content: flex-start;
}

.demand-indicators .badge {
    font-size: 10px;
    padding: 4px 8px;
    border-radius: 12px;
}

.demand-indicators .badge i {
    margin-right: 3px;
    font-size: 9px;
}

.disabled-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    backdrop-filter: blur(2px);
}

.disabled-content {
    text-align: center;
    color: #6c757d;
}

.disabled-content i {
    font-size: 48px;
    opacity: 0.7;
}

.sold-out-toggle {
    text-align: center;
    border-top: 1px solid #e9ecef;
    padding-top: 20px;
}

.sold-out-toggle .btn {
    color: #6c757d;
    font-weight: 500;
    text-decoration: none;
}

.sold-out-toggle .btn:hover {
    color: #007bff;
    text-decoration: none;
}

.sold-out-slots {
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
}

.sold-out-badge {
    color: #dc3545;
    font-size: 16px;
}

.sold-out-reason {
    text-align: center;
    font-style: italic;
}

.no-time-slots {
    border: 2px dashed #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
}

.selected-time-summary {
    border: 1px solid #007bff;
    background: linear-gradient(135deg, #f8f9ff 0%, #e3f2fd 100%);
}

.time-slots-loading {
    border: 2px dashed #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
}

.spinner-border {
    border-width: 3px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .time-slot-card {
        margin-bottom: 15px;
    }
    
    .time-display {
        font-size: 14px;
    }
    
    .start-time {
        font-size: 15px;
    }
    
    .available-spots {
        font-size: 11px;
    }
    
    .demand-indicators .badge {
        font-size: 9px;
        padding: 3px 6px;
    }
}

@media (max-width: 576px) {
    .time-slots-grid .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .time-slot-card {
        padding: 12px;
    }
    
    .selected-time-summary {
        padding: 15px !important;
    }
    
    .selected-time-summary .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .selected-time-summary .btn {
        margin-top: 10px;
        align-self: flex-end;
    }
}

/* Animation for slot loading */
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

.time-slot-card {
    animation: fadeInUp 0.3s ease-out;
}

.time-slot-card:nth-child(even) {
    animation-delay: 0.1s;
}

.time-slot-card:nth-child(odd) {
    animation-delay: 0.05s;
}

/* Pulse animation for high demand slots */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
    }
}

.time-slot-card.high-demand:not(.selected) {
    animation: pulse 2s infinite;
}

/* Subtle glow for selected slots */
.time-slot-card.selected {
    box-shadow: 
        0 4px 12px rgba(0, 123, 255, 0.2),
        0 0 0 1px rgba(0, 123, 255, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.7);
}
</style>

<script>
// Enhanced Time Slots Integration Script
(function() {
    'use strict';
    
    // Wait for Vue app to be available
    function initializeTimeSlots() {
        const vueElement = document.getElementById('bravo_tour_book_app');
        if (!vueElement) {
            console.log('⏳ Vue element not found, retrying...');
            setTimeout(initializeTimeSlots, 200);
            return;
        }
        
        if (!vueElement.__vue__) {
            console.log('⏳ Vue instance not ready, retrying...');
            setTimeout(initializeTimeSlots, 200);
            return;
        }
        
        const app = vueElement.__vue__;
        if (!app) {
            console.log('⏳ Vue app not available, retrying...');
            setTimeout(initializeTimeSlots, 200);
            return;
        }
        
        console.log('🚀 Initializing time slots functionality...');
        
        // Initialize time slots properties if they don't exist
        const timeSlotProps = {
            enable_time_slots: {{ isset($row->meta->enable_time_slots) && $row->meta->enable_time_slots ? 'true' : 'false' }}, // Enable based on tour configuration
            available_time_slots: [],
            sold_out_slots: [],
            selected_time_slot: null,
            loading_time_slots: false,
            show_sold_out_slots: false,
            time_slot_id: null,
            start_time: null
        };
        
        Object.keys(timeSlotProps).forEach(prop => {
            if (typeof app[prop] === 'undefined') {
                Vue.set(app, prop, timeSlotProps[prop]);
            }
        });
        
        // Initialize default guest values IMMEDIATELY
        initializeDefaultGuests();
        
        // Add time slots methods
        if (typeof app.loadTimeSlots !== 'function') {
            app.loadTimeSlots = function(date, forceRefresh = false) {
                console.log('📡 Loading time slots for date:', date, 'guests:', this.getGuestCount());
                
                if (!date || !this.enable_time_slots) {
                    console.log('⚠️ Date or time slots not enabled');
                    return;
                }
                
                this.loading_time_slots = true;
                this.available_time_slots = [];
                this.sold_out_slots = [];
                
                // FORCE proper guest count calculation
                const currentGuestCount = this.getGuestCount();
                console.log('👥 Force calculated guest count:', currentGuestCount);
                
                const requestData = {
                    tour_id: this.id,
                    date: date,
                    guests: currentGuestCount,
                    _t: Date.now()
                };
                
                // Add person types if they exist
                if (this.person_types && this.person_types.length) {
                    requestData.person_types = {};
                    this.person_types.forEach((type, index) => {
                        requestData.person_types[index] = {
                            number: type.number || type.min || 0
                        };
                    });
                    console.log('👥 Sending person types:', requestData.person_types);
                }
                
                // Use the existing API endpoint
                const apiUrl = bravo_booking_i18n.load_time_slots_url || '/api/tour/time-slots/available';
                
                $.ajax({
                    url: apiUrl,
                    method: 'GET',
                    data: requestData,
                    timeout: 15000,
                    success: (response) => {
                        console.log('✅ Time slots API response:', response);
                        this.loading_time_slots = false;
                        
                        if (response && response.success && response.data && response.data.time_slots) {
                            const slots = response.data.time_slots;
                            
                            // Process and categorize slots - Show all available slots regardless of capacity
                            this.available_time_slots = slots.filter(slot => 
                                !slot.is_sold_out && 
                                !slot.is_cutoff_reached &&
                                slot.remaining_capacity > 0  // Only hide completely sold out slots
                            );
                            
                            this.sold_out_slots = slots.filter(slot => 
                                slot.is_sold_out || 
                                slot.is_cutoff_reached ||
                                slot.remaining_capacity === 0  // Only fully booked slots go here
                            );
                            
                            console.log(`📊 Loaded ${this.available_time_slots.length} available slots, ${this.sold_out_slots.length} sold out`);
                            
                            // Auto-select if only one slot available
                            if (this.available_time_slots.length === 1 && !this.selected_time_slot) {
                                this.selectTimeSlot(this.available_time_slots[0]);
                            }
                        } else {
                            console.log('⚠️ No time slots data in response');
                            this.available_time_slots = [];
                            this.sold_out_slots = [];
                        }
                    },
                    error: (xhr, status, error) => {
                        console.error('❌ Error loading time slots:', error);
                        this.loading_time_slots = false;
                        this.available_time_slots = [];
                        this.sold_out_slots = [];
                        
                        // Show user-friendly error message
                        if (typeof toastr !== 'undefined') {
                            toastr.error('Failed to load available times. Please try again.');
                        }
                    }
                });
            };
        }
        
        // Add formatTime method if it doesn't exist
        if (typeof app.formatTime !== 'function') {
            app.formatTime = function(time) {
                if (!time) return '';
                const [hours, minutes] = time.split(':');
                const hour = parseInt(hours);
                const ampm = hour >= 12 ? 'PM' : 'AM';
                const formattedHour = hour % 12 || 12;
                return `${formattedHour}:${minutes} ${ampm}`;
            };
        }
        
        // Add selectTimeSlot method if it doesn't exist
        if (typeof app.selectTimeSlot !== 'function') {
            app.selectTimeSlot = function(slot) {
                const currentGuestCount = this.getGuestCount();
                
                // Enhanced validation
                if (!slot) {
                    console.warn('⚠️ Cannot select null slot');
                    return false;
                }
                
                if (slot.is_sold_out) {
                    console.warn('⚠️ Cannot select sold out slot:', slot);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('This time slot is fully booked.');
                    }
                    return false;
                }
                
                if (slot.remaining_capacity === 0) {
                    console.warn('⚠️ Cannot select slot with zero capacity:', slot);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('This time slot is fully booked.');
                    }
                    return false;
                }
                
                if (slot.is_cutoff_reached) {
                    console.warn('⚠️ Booking cutoff reached for slot:', slot);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Booking deadline has passed for this time slot.');
                    }
                    return false;
                }
                
                // Handle insufficient capacity with user confirmation
                if (slot.remaining_capacity < currentGuestCount) {
                    console.warn('⚠️ Insufficient capacity. Need:', currentGuestCount, 'Available:', slot.remaining_capacity);
                    
                    const confirmMessage = `This time slot only has ${slot.remaining_capacity} spots available, but you need ${currentGuestCount} spots.\n\nWould you like to reduce your group size to ${slot.remaining_capacity} people and continue with this time slot?`;
                    
                    if (confirm(confirmMessage)) {
                        // Reduce guest count to match available capacity
                        this.adjustGuestCountToCapacity(slot.remaining_capacity);
                        
                        if (typeof toastr !== 'undefined') {
                            toastr.info(`Group size adjusted to ${slot.remaining_capacity} guests to match available capacity.`);
                        }
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.warning('Please select a different time slot or adjust your group size.');
                        }
                        return false;
                    }
                }
                
                console.log('🎯 Time slot selected:', slot);
                this.selected_time_slot = slot;
                this.time_slot_id = slot.id;
                this.start_time = slot.start_time;
                
                // Trigger price recalculation if method exists
                if (typeof this.onDatesChanged === 'function') {
                    this.onDatesChanged();
                }
                
                return true;
            };
        }
        
        // Add helper method to adjust guest count
        if (typeof app.adjustGuestCountToCapacity !== 'function') {
            app.adjustGuestCountToCapacity = function(maxCapacity) {
                console.log('🔄 Adjusting guest count to capacity:', maxCapacity);
                
                if (this.person_types && this.person_types.length) {
                    // Distribute capacity across person types, prioritizing adults
                    let remainingCapacity = maxCapacity;
                    
                    // First, ensure minimum requirements are met
                    this.person_types.forEach(type => {
                        const minRequired = parseInt(type.min) || 0;
                        if (minRequired > 0 && remainingCapacity >= minRequired) {
                            Vue.set(type, 'number', minRequired);
                            remainingCapacity -= minRequired;
                        } else if (minRequired > remainingCapacity) {
                            Vue.set(type, 'number', remainingCapacity);
                            remainingCapacity = 0;
                        }
                    });
                    
                    // Distribute remaining capacity, prioritizing adults
                    if (remainingCapacity > 0) {
                        const adultType = this.person_types.find(type => 
                            type.name && type.name.toLowerCase().includes('adult')
                        );
                        
                        if (adultType) {
                            const currentAdults = parseInt(adultType.number) || 0;
                            Vue.set(adultType, 'number', currentAdults + remainingCapacity);
                        } else if (this.person_types.length > 0) {
                            // If no adult type found, add to first type
                            const firstType = this.person_types[0];
                            const currentCount = parseInt(firstType.number) || 0;
                            Vue.set(firstType, 'number', currentCount + remainingCapacity);
                        }
                    }
                } else {
                    // Simple guest count adjustment
                    Vue.set(this, 'guests', maxCapacity);
                }
                
                console.log('✅ Guest count adjusted. New total:', this.getGuestCount());
            };
        }
        
        // Add clearTimeSlot method if it doesn't exist
        if (typeof app.clearTimeSlot !== 'function') {
            app.clearTimeSlot = function() {
                console.log('🗑️ Clearing time slot selection');
                this.selected_time_slot = null;
                this.time_slot_id = null;
                this.start_time = null;
            };
        }
        
        // Add helper methods for slot validation
        if (typeof app.isSlotBookableForCurrentGuests !== 'function') {
            app.isSlotBookableForCurrentGuests = function(slot) {
                if (!slot) return false;
                
                const currentGuestCount = this.getGuestCount();
                
                // Check if slot is sold out or has zero capacity
                if (slot.is_sold_out || slot.remaining_capacity === 0) {
                    return false;
                }
                
                // Check if booking cutoff has been reached
                if (slot.is_cutoff_reached) {
                    return false;
                }
                
                // Check if remaining capacity is sufficient for current guest count
                return slot.remaining_capacity >= currentGuestCount;
            };
        }
        
        // Add handleTimeSlotClick method with enhanced guest validation
        if (typeof app.handleTimeSlotClick !== 'function') {
            app.handleTimeSlotClick = function(slot) {
                // Prevent clicking on disabled slots
                if (!this.isSlotBookableForCurrentGuests(slot) || slot.is_sold_out) {
                    console.log('🚫 Slot not clickable:', slot);
                    return false;
                }
                
                this.selectTimeSlot(slot);
            };
        }
        
        // Add tooltip method
        if (typeof app.getSlotTooltip !== 'function') {
            app.getSlotTooltip = function(slot) {
                if (!slot) return '';
                
                const currentGuestCount = this.getGuestCount();
                
                if (slot.is_sold_out) {
                    return 'This time slot is fully booked';
                }
                
                if (slot.remaining_capacity === 0) {
                    return 'No spots available';
                }
                
                if (slot.remaining_capacity < currentGuestCount) {
                    return `Only ${slot.remaining_capacity} spots available, but you need ${currentGuestCount} spots`;
                }
                
                if (slot.is_cutoff_reached) {
                    return 'Booking deadline has passed for this time slot';
                }
                
                return `${slot.remaining_capacity} spots available`;
            };
        }
        
        // Add aria-label method
        if (typeof app.getSlotAriaLabel !== 'function') {
            app.getSlotAriaLabel = function(slot) {
                if (!slot) return '';
                
                const timeLabel = this.formatTime(slot.start_time) + (slot.end_time ? ` to ${this.formatTime(slot.end_time)}` : '');
                const capacityLabel = `${slot.remaining_capacity} spots available`;
                const statusLabel = this.isSlotBookableForCurrentGuests(slot) ? 'Available' : 'Not available';
                
                return `Time slot: ${timeLabel}, ${capacityLabel}, ${statusLabel}`;
            };
        }
        
        // Add getGuestCount method if it doesn't exist
        if (typeof app.getGuestCount !== 'function') {
            app.getGuestCount = function() {
                let totalGuests = 0;
                
                // Check if person types exist and have values
                if (this.person_types && this.person_types.length) {
                    totalGuests = this.person_types.reduce((total, type) => {
                        // Use the person type's number, or its min value, or default to 0
                        const count = parseInt(type.number) || parseInt(type.min) || 0;
                        return total + count;
                    }, 0);
                    
                    // If total is 0, set default values based on min requirements
                    if (totalGuests === 0) {
                        this.person_types.forEach(type => {
                            const minRequired = parseInt(type.min) || 0;
                            if (minRequired > 0) {
                                Vue.set(type, 'number', minRequired);
                                totalGuests += minRequired;
                            }
                        });
                    }
                }
                
                // If still no guests from person types, use regular guests field
                if (totalGuests === 0) {
                    totalGuests = parseInt(this.guests) || 1;
                    
                    // Ensure guests field has a default value if it's 0 or undefined
                    if (!this.guests || this.guests === 0) {
                        Vue.set(this, 'guests', 1);
                        totalGuests = 1;
                    }
                }
                
                console.log('👥 Guest count calculated:', totalGuests, '(person_types:', this.person_types, 'guests:', this.guests, ')');
                return totalGuests;
            };
        }
        
        // Initialize default guest values if needed
        function initializeDefaultGuests() {
            console.log('🔧 Initializing default guest values...');
            
            // Initialize person types with minimum values if they exist
            if (app.person_types && app.person_types.length) {
                let hasAnyValues = false;
                
                app.person_types.forEach(type => {
                    if (type.number && parseInt(type.number) > 0) {
                        hasAnyValues = true;
                    }
                });
                
                // If no values are set, initialize with minimum requirements
                if (!hasAnyValues) {
                    app.person_types.forEach(type => {
                        const minRequired = parseInt(type.min) || 0;
                        if (minRequired > 0) {
                            Vue.set(type, 'number', minRequired);
                            console.log('✅ Set', type.name, 'to minimum:', minRequired);
                        } else if (type.name && type.name.toLowerCase().includes('adult')) {
                            // Default to 1 adult if no minimum is set
                            Vue.set(type, 'number', 1);
                            console.log('✅ Set adult default to 1');
                        }
                    });
                }
            } else if (!app.guests || app.guests === 0) {
                // Set default guests to 1 if using simple guest count
                Vue.set(app, 'guests', 1);
                console.log('✅ Set default guests to 1');
            }
            
            console.log('👥 Final guest count after initialization:', app.getGuestCount());
        }
        
        // Initialize default guest values BEFORE setting up watchers
        initializeDefaultGuests();
        
        // Set up watchers for automatic time slot loading
        if (app.$watch) {
            // Watch for start_date changes
            app.$watch('start_date', function(newDate, oldDate) {
                console.log('📅 Date changed from', oldDate, 'to', newDate);
                if (newDate && newDate !== oldDate && this.enable_time_slots) {
                    // Ensure we have proper guest count before loading
                    const guestCount = this.getGuestCount();
                    console.log('👥 Using guest count for time slots:', guestCount);
                    
                    // Small delay to ensure date is fully processed
                    setTimeout(() => {
                        this.loadTimeSlots(newDate);
                    }, 100);
                }
            }, { immediate: false });
            
            // Watch for formatted date changes (alternative trigger)
            app.$watch('start_date_formatted', function(newDate, oldDate) {
                console.log('📅 Formatted date changed from', oldDate, 'to', newDate);
                if (newDate && newDate !== oldDate && this.enable_time_slots) {
                    // Ensure we have proper guest count before loading
                    const guestCount = this.getGuestCount();
                    console.log('👥 Using guest count for formatted date:', guestCount);
                    
                    setTimeout(() => {
                        this.loadTimeSlots(newDate);
                    }, 100);
                }
            }, { immediate: false });
            
            // Watch for guest count changes to update availability and clear invalid selections
            app.$watch('guests', function(newGuests, oldGuests) {
                console.log('👥 Guest count changed from', oldGuests, 'to', newGuests);
                
                // Clear selected time slot if it's no longer valid for the new guest count
                if (this.selected_time_slot && newGuests > oldGuests) {
                    const selectedSlot = this.available_time_slots.find(slot => slot.id === this.selected_time_slot.id);
                    if (selectedSlot && !this.isSlotBookableForCurrentGuests(selectedSlot)) {
                        console.log('🗑️ Clearing time slot selection due to increased guest count');
                        this.clearTimeSlot();
                        
                        // Show user-friendly message
                        if (typeof toastr !== 'undefined') {
                            toastr.warning(`Your selected time slot was cleared because it only has ${selectedSlot.remaining_capacity} spots available, but you now need ${newGuests} spots.`);
                        }
                    }
                }
                
                if (this.start_date && this.enable_time_slots && newGuests !== oldGuests) {
                    setTimeout(() => {
                        this.loadTimeSlots(this.start_date, true); // Force refresh
                    }, 200);
                }
            });
            
            // Watch for person_types changes and clear invalid selections
            app.$watch('person_types', function(newTypes, oldTypes) {
                if (this.start_date && this.enable_time_slots && newTypes !== oldTypes) {
                    console.log('👥 Person types changed, reloading time slots');
                    
                    // Calculate old and new guest counts
                    const oldGuestCount = oldTypes ? oldTypes.reduce((total, type) => {
                        return total + (parseInt(type.number) || 0);
                    }, 0) : 0;
                    
                    const newGuestCount = this.getGuestCount();
                    
                    // Clear selected time slot if guest count increased and slot is no longer valid
                    if (this.selected_time_slot && newGuestCount > oldGuestCount) {
                        const selectedSlot = this.available_time_slots.find(slot => slot.id === this.selected_time_slot.id);
                        if (selectedSlot && !this.isSlotBookableForCurrentGuests(selectedSlot)) {
                            console.log('🗑️ Clearing time slot selection due to person type changes');
                            this.clearTimeSlot();
                            
                            // Show user-friendly message
                            if (typeof toastr !== 'undefined') {
                                toastr.warning(`Your selected time slot was cleared because it only has ${selectedSlot.remaining_capacity} spots available, but you now need ${newGuestCount} spots.`);
                            }
                        }
                    }
                    
                    setTimeout(() => {
                        this.loadTimeSlots(this.start_date, true); // Force refresh
                    }, 200);
                }
            }, { deep: true });
        }
        
        // Initialize time slots if date is already selected
        if (app.enable_time_slots && (app.start_date || app.start_date_formatted)) {
            const dateToUse = app.start_date_formatted || app.start_date;
            console.log('🔄 Initial time slots load for existing date:', dateToUse);
            console.log('👥 Initial guest count:', app.getGuestCount());
            
            setTimeout(() => {
                // Double-check guest initialization before loading
                const finalGuestCount = app.getGuestCount();
                console.log('👥 Final guest count for initial load:', finalGuestCount);
                app.loadTimeSlots(dateToUse);
            }, 500);
        }
        
        // Store globally for debugging
        window.bravo_booking_vue = app;
        
        console.log('✅ Time slots functionality initialized successfully!');
    }
    
    // Start initialization when DOM is ready with better error handling
    function safeInitialize() {
        try {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(initializeTimeSlots, 500);
                });
            } else {
                setTimeout(initializeTimeSlots, 500);
            }
        } catch (error) {
            console.error('❌ Error in time slots initialization:', error);
        }
    }
    
    // Call safe initialize
    safeInitialize();
    
})();
</script>



