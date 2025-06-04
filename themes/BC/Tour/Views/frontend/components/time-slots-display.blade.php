{{-- 
    Consolidated Time Slots Display Component
    Usage: @include('Tour::frontend.components.time-slots-display', ['booking' => $booking, 'service' => $service, 'context' => 'modal|detail|history'])
--}}

@php
    $lang_local = app()->getLocale();
    $timeSlotInfo = $booking->getJsonMeta('time_slot_info');
    $meta = $service->meta ?? null;
    $timeSlotsEnabled = $meta && !empty($meta->enable_time_slots);
    $context = $context ?? 'detail'; // Options: 'modal', 'detail', 'history', 'checkout'
@endphp

@if($timeSlotsEnabled && !empty($timeSlotInfo))
    <div class="time-slot-display-component context-{{ $context }}">
        @if($context === 'modal')
            {{-- Modal Display --}}
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
            
            {{-- Price Modifier Display --}}
            @if(isset($timeSlotInfo['price_modifier']) && $timeSlotInfo['price_modifier'] != 0)
                <div class="detail-item time-slot-modifier">
                    <strong>
                        <i class="fa fa-clock-o"></i>
                        {{__("Time Slot Adjustment")}}:
                    </strong>
                    <span class="modifier-amount @if($timeSlotInfo['price_modifier'] > 0) text-success @else text-info @endif">
                        @if($timeSlotInfo['price_modifier'] > 0) + @endif
                        {{ format_money($timeSlotInfo['price_modifier'] * ($booking->total_guests ?? 1)) }}
                    </span>
                </div>
            @endif
            
        @elseif($context === 'detail' || $context === 'checkout')
            {{-- Detail/Checkout Display --}}
            <li class="time-slot-detail">
                <div class="label">
                    <i class="fa fa-clock-o text-primary"></i>
                    {{__('Time Slot:')}}
                </div>
                <div class="val">
                    <div class="time-slot-info-detailed">
                        <div class="time-slot-time">
                            <strong class="badge badge-success">
                                {{ $timeSlotInfo['formatted_time'] ?? date('g:i A', strtotime($timeSlotInfo['start_time'])) }}
                            </strong>
                        </div>
                        @if(!empty($timeSlotInfo['day_name']))
                            <div class="time-slot-day">
                                <small class="text-muted">{{ $timeSlotInfo['day_name'] }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            </li>
            
            {{-- Price Modifier for Detail/Checkout --}}
            @if($context === 'checkout' && isset($timeSlotInfo['price_modifier']) && $timeSlotInfo['price_modifier'] != 0)
                <li class="time-slot-modifier">
                    <div class="label">
                        <i class="fa fa-clock-o"></i>
                        {{__("Time Slot Adjustment")}}:
                    </div>
                    <div class="val">
                        <span class="@if($timeSlotInfo['price_modifier'] > 0) text-success @else text-info @endif">
                            @if($timeSlotInfo['price_modifier'] > 0) + @endif
                            {{format_money($timeSlotInfo['price_modifier'] * ($booking->total_guests ?? 1))}}
                        </span>
                    </div>
                </li>
            @endif
            
        @elseif($context === 'history')
            {{-- History List Display --}}
            <span class="time-slot-info">
                <i class="fa fa-clock-o text-primary"></i>
                <strong>{{__("Time Slot")}}:</strong> 
                <span class="badge badge-info">{{ $timeSlotInfo['formatted_time'] ?? $timeSlotInfo['start_time'] }}</span>
            </span>
        @endif
    </div>
    
@elseif(!empty($booking->start_time))
    {{-- Fallback for basic start_time without full time slot info --}}
    <div class="time-slot-display-component context-{{ $context }} fallback-time">
        @if($context === 'modal')
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
        @elseif($context === 'detail' || $context === 'checkout')
            <li class="time-slot-detail">
                <div class="label">
                    <i class="fa fa-clock-o text-primary"></i>
                    {{__('Start Time:')}}
                </div>
                <div class="val">
                    <strong class="badge badge-info">
                        {{ date('g:i A', strtotime($booking->start_time)) }}
                    </strong>
                </div>
            </li>
        @elseif($context === 'history')
            <span class="time-slot-info">
                <i class="fa fa-clock-o text-primary"></i>
                <strong>{{__("Time")}}:</strong> 
                <span class="badge badge-info">{{ date('g:i A', strtotime($booking->start_time)) }}</span>
            </span>
        @endif
    </div>
@endif

{{-- CSS Styles for Time Slot Display Component --}}
@once
@push('css')
<style>
.time-slot-display-component {
    /* Base styles for all contexts */
}

/* Modal Context Styles */
.time-slot-display-component.context-modal .time-slot-display {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.time-slot-display-component.context-modal .badge-lg {
    font-size: 0.9em;
    padding: 6px 12px;
    border-radius: 12px;
    font-weight: 600;
}

.time-slot-display-component.context-modal .time-slot-modifier {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 8px;
    border-radius: 6px;
    border-left: 3px solid #007bff;
}

.time-slot-display-component.context-modal .modifier-amount {
    font-weight: 600;
    font-size: 1.05em;
}

/* Detail/Checkout Context Styles */
.time-slot-display-component.context-detail .time-slot-info-detailed,
.time-slot-display-component.context-checkout .time-slot-info-detailed {
    display: flex;
    align-items: center;
    gap: 10px;
}

.time-slot-display-component.context-detail .badge,
.time-slot-display-component.context-checkout .badge {
    font-size: 0.9em;
    padding: 5px 10px;
}

.time-slot-display-component.context-checkout .time-slot-modifier {
    background: #f8f9fa;
    border-left: 3px solid #007bff;
    padding-left: 10px;
    margin: 5px 0;
}

/* History Context Styles */
.time-slot-display-component.context-history .time-slot-info {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 5px;
}

.time-slot-display-component.context-history .badge {
    font-size: 0.8em;
    padding: 3px 8px;
}

/* Badge Styles */
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

/* Responsive Design */
@media (max-width: 768px) {
    .time-slot-display-component.context-modal .time-slot-display {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .time-slot-display-component.context-detail .time-slot-info-detailed,
    .time-slot-display-component.context-checkout .time-slot-info-detailed {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
}

/* Fallback time display */
.time-slot-display-component.fallback-time {
    opacity: 0.9;
}
</style>
@endpush
@endonce
