@php $lang_local = app()->getLocale() @endphp

<div class="booking-review">
    <h4 class="booking-review-title">{{__("Your Booking")}}</h4>
    <div class="booking-review-content">
        <div class="review-section">
            <div class="service-info">
                <div>
                    @php
                        $service_translation = $service->translate($lang_local);
                    @endphp
                    <h3 class="service-name"><a href="{{$service->getDetailUrl()}}">{!! clean($service_translation->title) !!}</a></h3>
                    @if($service_translation->address)
                        <p class="address"><i class="fa fa-map-marker"></i>
                            {{$service_translation->address}}
                        </p>
                    @endif
                </div>
                @php $vendor = $service->author; @endphp
                @if($vendor->hasPermission('dashboard_vendor_access') and !$vendor->hasPermission('dashboard_access'))
                    <div class="mt-1">
                        <i class="icofont-info-circle"></i>
                        {{ __("Vendor") }}: <a href="{{route('user.profile',['id'=>$vendor->id])}}" target="_blank" >{{$vendor->getDisplayName()}}</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="review-section">
            <ul class="review-list">
                @if($booking->start_date)
                    <li>
                        <div class="label">{{__('Start date:')}}</div>
                        <div class="val">
                            {{display_date($booking->start_date)}}
                        </div>
                    </li>
                    
                    {{-- 🎯 NEW: Enhanced Time Slot Display --}}
                    @php 
                        $timeSlotInfo = $booking->getJsonMeta('time_slot_info');
                        $meta = $service->meta ?? null;
                        $timeSlotsEnabled = $meta && !empty($meta->enable_time_slots);
                    @endphp
                    
                    @if($timeSlotsEnabled && !empty($timeSlotInfo))
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
                    @elseif(!empty($booking->start_time))
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
                    @endif

                    <li>
                        <div class="label">{{__('Duration:')}}</div>
                        <div class="val">
                            {{human_time_diff($booking->end_date,$booking->start_date)}}
                        </div>
                    </li>
                @endif

                @php $person_types = $booking->getJsonMeta('person_types')@endphp
                @if(!empty($person_types))
                    @foreach($person_types as $type)
                        <li>
                            <div class="label">{{$type['name_'.$lang_local] ?? __($type['name'])}}:</div>
                            <div class="val">
                                {{$type['number']}}
                            </div>
                        </li>
                    @endforeach
                @else
                    <li>
                        <div class="label">{{__("Guests")}}:</div>
                        <div class="val">
                            {{$booking->total_guests}}
                        </div>
                    </li>
                @endif

                @php $packages = $booking->getJsonMeta('packages')@endphp
                @if(!empty($packages))
                    @foreach($packages as $type)
                        <li>
                            <div class="label">{{$type['name_'.$lang_local] ?? __($type['name'])}}:</div>
                            <div class="val">
                                {{$type['start_time']}}
                            </div>
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>

        @do_action('booking.checkout.before_total_review')
        <div class="review-section total-review">
            <ul class="review-list">
                @php $person_types = $booking->getJsonMeta('person_types') @endphp
                @if(!empty($person_types))
                    @foreach($person_types as $type)
                        <li>
                            <div class="label">{{ $type['name_'.$lang_local] ?? __($type['name'])}}: {{$type['number']}} * {{format_money($type['price'])}}</div>
                            <div class="val">
                                {{format_money($type['price'] * $type['number'])}}
                            </div>
                        </li>
                    @endforeach
                @else
                    <li>
                        <div class="label">{{__("Guests")}}: {{$booking->total_guests}} * {{format_money($booking->getMeta('base_price'))}}</div>
                        <div class="val">
                            {{format_money($booking->getMeta('base_price') * $booking->total_guests)}}
                        </div>
                    </li>
                @endif

                {{-- 🎯 NEW: Time Slot Price Modifier --}}
                @if(!empty($timeSlotInfo) && isset($timeSlotInfo['price_modifier']) && $timeSlotInfo['price_modifier'] != 0)
                    <li class="time-slot-modifier">
                        <div class="label">
                            <i class="fa fa-clock-o"></i>
                            {{__("Time Slot Adjustment")}}:
                        </div>
                        <div class="val">
                            <span class="@if($timeSlotInfo['price_modifier'] > 0) text-success @else text-info @endif">
                                @if($timeSlotInfo['price_modifier'] > 0) + @endif
                                {{format_money($timeSlotInfo['price_modifier'] * $booking->total_guests)}}
                            </span>
                        </div>
                    </li>
                @endif

                @php $packages = $booking->getJsonMeta('packages') @endphp
                @if(!empty($packages))
                    <li>
                        <div class="label-title"><strong>{{__("Packages:")}}</strong></div>
                    </li>
                    <li class="no-flex">
                        <ul>
                            @foreach($packages as $type)
                                <li>
                                    <div class="label">{{$type['name_'.$lang_local] ?? __($type['name'])}}:</div>
                                    <div class="val">
                                        {{format_money($type['total'] ?? 0)}}
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif

                @php $extra_price = $booking->getJsonMeta('extra_price') @endphp
                @if(!empty($extra_price))
                    <li>
                        <div class="label-title"><strong>{{__("Extra Prices:")}}</strong></div>
                    </li>
                    <li class="no-flex">
                        <ul>
                            @foreach($extra_price as $type)
                                <li>
                                    <div class="label">{{$type['name_'.$lang_local] ?? __($type['name'])}}:</div>
                                    <div class="val">
                                        {{format_money($type['total'] ?? 0)}}
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif

                @php $discount_by_people = $booking->getJsonMeta('discount_by_people')@endphp
                @if(!empty($discount_by_people))
                    <li>
                        <div class="label-title"><strong>{{__("Discounts:")}}</strong></div>
                    </li>
                    <li class="no-flex">
                        <ul>
                            @foreach($discount_by_people as $type)
                                <li>
                                    <div class="label">
                                        @if(!$type['to'])
                                            {{__('from :from guests',['from'=>$type['from']])}}
                                        @else
                                            {{__(':from - :to guests',['from'=>$type['from'],'to'=>$type['to']])}}
                                        @endif
                                        :
                                    </div>
                                    <div class="val">
                                        - {{format_money($type['total'] ?? 0)}}
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif

                @php
                    $list_all_fee = [];
                    if(!empty($booking->buyer_fees)){
                        $buyer_fees = json_decode($booking->buyer_fees , true);
                        $list_all_fee = $buyer_fees;
                    }
                    if(!empty($vendor_service_fee = $booking->vendor_service_fee)){
                        $list_all_fee = array_merge($list_all_fee , $vendor_service_fee);
                    }
                @endphp
                @if(!empty($list_all_fee))
                    @foreach ($list_all_fee as $item)
                        @php
                            $fee_price = $item['price'];
                            if(!empty($item['unit']) and $item['unit'] == "percent"){
                                $fee_price = ( $booking->total_before_fees / 100 ) * $item['price'];
                            }
                        @endphp
                        <li>
                            <div class="label">
                                {{$item['name_'.$lang_local] ?? $item['name']}}
                                <i class="icofont-info-circle" data-toggle="tooltip" data-placement="top" title="{{ $item['desc_'.$lang_local] ?? $item['desc'] }}"></i>
                                @if(!empty($item['per_person']) and $item['per_person'] == "on")
                                    : {{$booking->total_guests}} * {{format_money( $fee_price )}}
                                @endif
                            </div>
                            <div class="val">
                                @if(!empty($item['per_person']) and $item['per_person'] == "on")
                                    {{ format_money( $fee_price * $booking->total_guests ) }}
                                @else
                                    {{ format_money( $fee_price ) }}
                                @endif
                            </div>
                        </li>
                    @endforeach
                @endif

                @includeIf('Coupon::frontend/booking/checkout-coupon')

                <li class="final-total d-block">
                    <div class="d-flex justify-content-between">
                        <div class="label">{{__("Total:")}}</div>
                        <div class="val">{{format_money($booking->total)}}</div>
                    </div>
                    @if($booking->status !='draft')
                        <div class="d-flex justify-content-between">
                            <div class="label">{{__("Paid:")}}</div>
                            <div class="val">{{format_money($booking->paid)}}</div>
                        </div>
                        @if($booking->paid < $booking->total )
                            <div class="d-flex justify-content-between">
                                <div class="label">{{__("Remain:")}}</div>
                                <div class="val">{{format_money($booking->total - $booking->paid)}}</div>
                            </div>
                        @endif
                    @endif
                </li>
                @include ('Booking::frontend/booking/checkout-deposit-amount')
            </ul>
        </div>
    </div>
</div>

{{-- 🎯 NEW: CSS Styles for Time Slot Display --}}
<style>
.time-slot-detail .time-slot-info-detailed {
    display: flex;
    align-items: center;
    gap: 10px;
}

.time-slot-detail .time-slot-time .badge {
    font-size: 0.9em;
    padding: 5px 10px;
}

.time-slot-detail .time-slot-day {
    font-style: italic;
}

.time-slot-modifier {
    background: #f8f9fa;
    border-left: 3px solid #007bff;
    padding-left: 10px;
    margin: 5px 0;
}

.time-slot-info {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 5px;
}

.time-slot-info .badge {
    font-size: 0.8em;
    padding: 3px 8px;
}

@media (max-width: 768px) {
    .time-slot-detail .time-slot-info-detailed {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
}
</style>
