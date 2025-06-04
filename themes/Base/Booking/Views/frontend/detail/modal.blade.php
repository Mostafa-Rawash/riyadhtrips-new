@php 
    $lang_local = app()->getLocale();
    $timeSlotInfo = $booking->getJsonMeta('time_slot_info');
    $meta = $service->meta ?? null;
    $timeSlotsEnabled = $meta && !empty($meta->enable_time_slots);
@endphp

<div class="booking-detail-modal-content">
    <div class="row">
        <div class="col-md-8">
            {{-- Service Information --}}
            <div class="service-info-section mb-4">
                <div class="d-flex align-items-center">
                    @if($service->image_id)
                        <div class="service-image mr-3">
                            <img src="{{ get_file_url($service->image_id, 'thumb') }}" 
                                 alt="{{ $service->title }}" 
                                 class="rounded" 
                                 style="width: 80px; height: 60px; object-fit: cover;">
                        </div>
                    @endif
                    <div class="service-details">
                        <h5 class="service-title mb-1">
                            <a href="{{ $service->getDetailUrl() }}" target="_blank" class="text-decoration-none">
                                {{ $service->translate($lang_local)->title }}
                            </a>
                        </h5>
                        @if($service->translate($lang_local)->address)
                            <p class="service-address mb-0 text-muted">
                                <i class="fa fa-map-marker"></i> {{ $service->translate($lang_local)->address }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Booking Details --}}
            <div class="booking-details-section">
                <h6 class="section-title">{{__("Booking Details")}}</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <strong>{{__("Booking ID")}}:</strong> 
                            <span class="text-primary">#{{ $booking->id }}</span>
                        </div>
                        <div class="detail-item">
                            <strong>{{__("Status")}}:</strong> 
                            <span class="badge badge-{{ $booking->status_class }}">{{ $booking->statusName }}</span>
                        </div>
                        <div class="detail-item">
                            <strong>{{__("Booking Date")}}:</strong> 
                            {{ display_date($booking->created_at) }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        @if($booking->start_date)
                            <div class="detail-item">
                                <strong>{{__("Start Date")}}:</strong> 
                                {{ display_date($booking->start_date) }}
                            </div>
                            
                            {{-- 🎯 NEW: Enhanced Time Slot Display in Modal --}}
                            @if($timeSlotsEnabled && !empty($timeSlotInfo))
                                <div class="detail-item time-slot-detail">
                                    <strong>
                                        <i class="fa fa-clock-o text-primary"></i>
                                        {{__("Time Slot")}}:
                                    </strong>
                                    <div class="time-slot-display mt-1">
                                        <span class="badge badge-success badge-lg">
                                            {{ $timeSlotInfo['formatted_time'] ?? date('g:i A', strtotime($timeSlotInfo['start_time'])) }}
                                        </span>
                                        @if(!empty($timeSlotInfo['day_name']))
                                            <small class="text-muted ml-2">{{ $timeSlotInfo['day_name'] }}</small>
                                        @endif
                                    </div>
                                </div>
                            @elseif(!empty($booking->start_time))
                                <div class="detail-item time-slot-detail">
                                    <strong>
                                        <i class="fa fa-clock-o text-primary"></i>
                                        {{__("Start Time")}}:
                                    </strong>
                                    <div class="time-slot-display mt-1">
                                        <span class="badge badge-info badge-lg">
                                            {{ date('g:i A', strtotime($booking->start_time)) }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="detail-item">
                                <strong>{{__("Duration")}}:</strong> 
                                {{ $booking->getMeta("duration") ?? "1" }} {{__("hours")}}
                            </div>
                        @endif
                        <div class="detail-item">
                            <strong>{{__("Guests")}}:</strong> 
                            {{ $booking->total_guests }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment Information --}}
            <div class="payment-info-section mt-4">
                <h6 class="section-title">{{__("Payment Information")}}</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <strong>{{__("Total")}}:</strong> 
                            <span class="text-success">{{ format_money($booking->total) }}</span>
                        </div>
                        <div class="detail-item">
                            <strong>{{__("Paid")}}:</strong> 
                            <span class="text-info">{{ format_money($booking->paid) }}</span>
                        </div>
                        @if($booking->total - $booking->paid > 0)
                            <div class="detail-item">
                                <strong>{{__("Remaining")}}:</strong> 
                                <span class="text-warning">{{ format_money($booking->total - $booking->paid) }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if(!empty($booking->gateway))
                            <?php $gateway = get_payment_gateway_obj($booking->gateway); ?>
                            @if($gateway)
                                <div class="detail-item">
                                    <strong>{{__("Payment Method")}}:</strong> 
                                    {{ $gateway->name }}
                                </div>
                            @endif
                        @endif
                        
                        {{-- 🎯 NEW: Time Slot Price Modifier in Modal --}}
                        @if(!empty($timeSlotInfo) && isset($timeSlotInfo['price_modifier']) && $timeSlotInfo['price_modifier'] != 0)
                            <div class="detail-item time-slot-modifier">
                                <strong>
                                    <i class="fa fa-clock-o"></i>
                                    {{__("Time Slot Adjustment")}}:
                                </strong>
                                <span class="modifier-amount @if($timeSlotInfo['price_modifier'] > 0) text-success @else text-info @endif">
                                    @if($timeSlotInfo['price_modifier'] > 0) + @endif
                                    {{ format_money($timeSlotInfo['price_modifier'] * $booking->total_guests) }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Customer Information --}}
            <div class="customer-info-section">
                <h6 class="section-title">{{__("Customer Information")}}</h6>
                <div class="customer-details">
                    <div class="detail-item">
                        <strong>{{__("Name")}}:</strong> 
                        {{ $booking->first_name }} {{ $booking->last_name }}
                    </div>
                    <div class="detail-item">
                        <strong>{{__("Email")}}:</strong> 
                        <a href="mailto:{{ $booking->email }}">{{ $booking->email }}</a>
                    </div>
                    @if($booking->phone)
                        <div class="detail-item">
                            <strong>{{__("Phone")}}:</strong> 
                            <a href="tel:{{ $booking->phone }}">{{ $booking->phone }}</a>
                        </div>
                    @endif
                    @if($booking->address)
                        <div class="detail-item">
                            <strong>{{__("Address")}}:</strong> 
                            {{ $booking->address }}
                            @if($booking->city), {{ $booking->city }}@endif
                            @if($booking->state), {{ $booking->state }}@endif
                            @if($booking->country), {{ $booking->country }}@endif
                        </div>
                    @endif
                    @if($booking->customer_notes)
                        <div class="detail-item">
                            <strong>{{__("Notes")}}:</strong> 
                            <p class="text-muted">{{ $booking->customer_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Vendor Information --}}
            @php $vendor = $service->author; @endphp
            @if($vendor->hasPermission('dashboard_vendor_access') and !$vendor->hasPermission('dashboard_access'))
                <div class="vendor-info-section mt-4">
                    <h6 class="section-title">{{__("Vendor Information")}}</h6>
                    <div class="vendor-details">
                        <div class="d-flex align-items-center">
                            @if($vendor->avatar_id)
                                <img src="{{ get_file_url($vendor->avatar_id, 'thumb') }}" 
                                     alt="{{ $vendor->getDisplayName() }}" 
                                     class="rounded-circle mr-2" 
                                     width="40" height="40">
                            @endif
                            <div>
                                <strong>
                                    <a href="{{ route('user.profile', ['id' => $vendor->id]) }}" target="_blank">
                                        {{ $vendor->getDisplayName() }}
                                    </a>
                                </strong>
                                @if($vendor->email)
                                    <br><small class="text-muted">{{ $vendor->email }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- 🎯 NEW: Modal-specific CSS Styles --}}
<style>
.booking-detail-modal-content {
    padding: 20px;
}

.section-title {
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 5px;
    margin-bottom: 15px;
    font-weight: 600;
}

.detail-item {
    margin-bottom: 10px;
    padding: 5px 0;
}

.detail-item strong {
    color: #495057;
    font-weight: 600;
}

.service-info-section {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.customer-info-section, .vendor-info-section {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}

.time-slot-detail .time-slot-display {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.time-slot-detail .badge-lg {
    font-size: 0.9em;
    padding: 6px 12px;
    border-radius: 12px;
    font-weight: 600;
}

.time-slot-modifier {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 8px;
    border-radius: 6px;
    border-left: 3px solid #007bff;
}

.time-slot-modifier .modifier-amount {
    font-weight: 600;
    font-size: 1.05em;
}

.badge.badge-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
}

.badge.badge-info {
    background: linear-gradient(135deg, #17a2b8 0%, #6610f2 100%);
    color: white;
    box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);
}

/* Status badges */
.badge.badge-primary { background-color: #007bff; }
.badge.badge-success { background-color: #28a745; }
.badge.badge-info { background-color: #17a2b8; }
.badge.badge-danger { background-color: #dc3545; }
.badge.badge-warning { background-color: #ffc107; color: #212529; }

/* Mobile responsive */
@media (max-width: 768px) {
    .booking-detail-modal-content {
        padding: 15px;
    }
    
    .time-slot-detail .time-slot-display {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .service-info-section .d-flex {
        flex-direction: column;
        text-align: center;
    }
    
    .service-image {
        margin-right: 0 !important;
        margin-bottom: 10px;
    }
}
</style>
