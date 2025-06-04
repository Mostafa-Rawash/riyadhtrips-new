@extends ('admin.layouts.app')
@section ('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{__('All Bookings')}}</h1>
        </div>
        @include('admin.message')
        <div class="filter-div d-flex justify-content-between">
            <div class="col-left">
                @if(!empty($booking_update))
                    <form method="post" action="{{route('report.admin.booking.bulkEdit')}}" class="filter-form filter-form-left d-flex justify-content-start">
                        @csrf
                        <select name="action" class="form-control">
                            <option value="">{{__("-- Bulk Actions --")}}</option>
                            @if(!empty($statues))
                                @foreach($statues as $status)
                                    <option value="{{$status}}">{{__('Mark as: :name',['name'=>booking_status_to_text($status)])}}</option>
                                @endforeach
                            @endif
                            <option value="delete">{{__("DELETE booking")}}</option>
                        </select>
                        <button data-confirm="{{__("Do you want to delete?")}}" class="btn-info btn btn-icon dungdt-apply-form-btn" type="button">{{__('Apply')}}</button>
                    </form>
                @endif
            </div>
            <div class="col-left">
                <form method="get" action="" class="filter-form filter-form-right d-flex justify-content-end">
                    @csrf
                    @if(!empty($booking_manage_others))
                        <?php
                        $user = !empty(Request()->vendor_id) ? App\User::find(Request()->vendor_id) : false;
                        \App\Helpers\AdminForm::select2('vendor_id', [
                            'configs' => [
                                'ajax'        => [
                                    'url'      => route('user.admin.getForSelect2'),
                                    'dataType' => 'json'
                                ],
                                'allowClear'  => true,
                                'placeholder' => __('-- Vendor --')
                            ]
                        ], !empty($user->id) ? [
                            $user->id,
                            $user->name_or_email . ' (#' . $user->id . ')'
                        ] : false)
                        ?>
                    @endif
                    <input type="text" name="s" value="{{ Request()->s }}" placeholder="{{__('Search by name or ID')}}" class="form-control">
                    <button class="btn-info btn btn-icon" type="submit">{{__('Filter')}}</button>
                </form>
            </div>
        </div>
        <div class="text-right">
            <p><i>{{__('Found :total items',['total'=>$rows->total()])}}</i></p>
        </div>
        <div class="panel booking-history-manager">
            <div class="panel-title">{{__('Bookings')}}</div>
            <div class="panel-body">
                <form action="" class="bravo-form-item">
                    <table class="table table-hover bravo-list-item">
                        <thead>
                        <tr>
                            <th width="80px"><input type="checkbox" class="check-all"></th>
                            <th>{{__('Service')}}</th>
                            <th>{{__('Customer')}}</th>
                            <th>{{__('Schedule & Time Slot')}}</th>
                            <th>{{__('Payment Information')}}</th>
                            <th  width="80px" >{{__('Commission')}}</th>
                            <th width="80px">{{__('Status')}}</th>
                            <th width="150px">{{__('Payment Method')}}</th>
                            <th width="120px">{{__('Created At')}}</th>
                            <th width="80px">{{__('Actions')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($rows as $row)
                            @php  
                                $booking = $row;
                                $service = $row->service;
                                $timeSlotInfo = $booking->getJsonMeta('time_slot_info');
                                $meta = $service->meta ?? null;
                                $timeSlotsEnabled = $meta && !empty($meta->enable_time_slots);
                            @endphp
                            <tr>
                                <td><input type="checkbox" class="check-item" name="ids[]" value="{{$row->id}}">
                                    #{{$row->id}}</td>
                                <td>
                                    @if($service = $row->service)
                                        <a href="{{$service->getDetailUrl()}}" target="_blank">{{$service->title ?? ''}}</a>
                                        @if($row->vendor)
                                            <br>
                                            <span>{{__('by')}}</span>
                                            <a href="{{route('user.admin.detail',['id'=>$row->vendor_id])}}"
                                               target="_blank">{{$row->vendor->name_or_email.' (#'.$row->vendor_id.')' }}</a>
                                        @endif
                                    @else
                                        {{__("[Deleted]")}}
                                    @endif
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-name">
                                            <strong>{{$row->first_name}} {{$row->last_name}}</strong>
                                        </div>
                                        <div class="customer-contact">
                                            <small><i class="fa fa-envelope"></i> {{$row->email}}</small><br>
                                            @if($row->phone)
                                                <small><i class="fa fa-phone"></i> {{$row->phone}}</small><br>
                                            @endif
                                            @if($row->address)
                                                <small><i class="fa fa-map-marker"></i> {{$row->address}}</small>
                                            @endif
                                        </div>
                                        @if($row->customer_notes)
                                            <div class="customer-notes mt-1">
                                                <small class="text-muted"><strong>{{__("Notes:")}}:</strong> {{Str::limit($row->customer_notes, 50)}}</small>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                {{-- 🎯 NEW: Schedule & Time Slot Column --}}
                                <td class="schedule-column">
                                    <div class="schedule-info">
                                        @if($booking->start_date)
                                            <div class="booking-date">
                                                <i class="fa fa-calendar text-primary"></i>
                                                <strong>{{display_date($booking->start_date)}}</strong>
                                            </div>
                                            
                                            {{-- Time Slot Information --}}
                                            @if($timeSlotsEnabled && !empty($timeSlotInfo))
                                                <div class="time-slot-info mt-1">
                                                    <div class="time-slot-badge">
                                                        <i class="fa fa-clock-o text-success"></i>
                                                        <span class="badge badge-success">
                                                            {{ $timeSlotInfo['formatted_time'] ?? date('g:i A', strtotime($timeSlotInfo['start_time'])) }}
                                                        </span>
                                                    </div>
                                                    @if(!empty($timeSlotInfo['day_name']))
                                                        <div class="time-slot-day">
                                                            <small class="text-muted">{{ $timeSlotInfo['day_name'] }}</small>
                                                        </div>
                                                    @endif
                                                    {{-- Price modifier indicator --}}
                                                    @if(isset($timeSlotInfo['price_modifier']) && $timeSlotInfo['price_modifier'] != 0)
                                                        <div class="time-slot-modifier">
                                                            <small class="@if($timeSlotInfo['price_modifier'] > 0) text-success @else text-info @endif">
                                                                <i class="fa fa-adjust"></i>
                                                                @if($timeSlotInfo['price_modifier'] > 0) + @endif
                                                                {{ format_money($timeSlotInfo['price_modifier'] * $booking->total_guests) }}
                                                            </small>
                                                        </div>
                                                    @endif
                                                </div>
                                            @elseif(!empty($booking->start_time))
                                                <div class="time-slot-info mt-1">
                                                    <div class="time-slot-badge">
                                                        <i class="fa fa-clock-o text-info"></i>
                                                        <span class="badge badge-info">
                                                            {{ date('g:i A', strtotime($booking->start_time)) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <div class="booking-duration mt-1">
                                                <small class="text-muted">
                                                    <i class="fa fa-hourglass-half"></i>
                                                    {{ $booking->getMeta("duration") ?? "1" }} {{__("hours")}}
                                                </small>
                                            </div>
                                            
                                            <div class="booking-guests mt-1">
                                                <small class="text-muted">
                                                    <i class="fa fa-users"></i>
                                                    {{ $booking->total_guests }} {{__("guests")}}
                                                </small>
                                            </div>
                                        @else
                                            <span class="text-muted">{{__("No schedule set")}}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{__("Total")}} : {{format_money_main($row->total)}}<br/>
                                    {{__("Paid")}} : {{format_money_main($row->paid)}}<br/>
                                    {{__("Remain")}} : {{format_money_main($booking->total - $booking->paid)}}<br/>
                                </td>
                                <td>
                                    {{format_money_main($booking->commission)}}
                                </td>
                                <td>
                                    <span class="label label-{{$row->status}}">{{$row->statusName}}</span>
                                </td>
                                <td>
                                    {{$row->gatewayObj ? $row->gatewayObj->getDisplayName() : ''}}
                                </td>
                                <td>{{display_datetime($row->updated_at)}}</td>
                                <td>
                                    @if($service = $row->service)
                                        <div class="dropdown">
                                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{__('Actions')}}
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                                <a class="dropdown-item btn-detail-booking" href="#modal_booking_detail" data-ajax="{{route('booking.modal',['booking'=>$booking])}}" data-toggle="modal" data-id="{{$booking->id}}" data-target="#modal_booking_detail">{{__('Detail')}}</a>
                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modal-paid-{{$row->id}}">{{__('Set Paid')}}</a>
                                                <a class="dropdown-item" href="{{route('report.admin.booking.email_preview',['id'=>$row->id])}}">{{__('Email Preview')}}</a>
                                            </div>
                                        </div>
                                        @include ($service->set_paid_modal_file ?? '')
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </form>

                <div class="modal" tabindex="-1" id="modal_booking_detail">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{__('Booking ID: #')}} <span class="user_id"></span></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="d-flex justify-content-center">{{__("Loading...")}}</div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Close')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            {{$rows->links()}}
        </div>
    </div>
@endsection

@push('css')
<style>
/* Enhanced Admin Booking Table Styles */
.customer-info .customer-name {
    font-weight: 600;
    margin-bottom: 5px;
}

.customer-info .customer-contact {
    line-height: 1.4;
    margin-bottom: 5px;
}

.customer-info .customer-contact i {
    width: 12px;
    color: #6c757d;
}

.customer-notes {
    font-style: italic;
    background: #f8f9fa;
    padding: 3px 6px;
    border-radius: 3px;
}

/* Schedule Column Styles */
.schedule-column {
    min-width: 180px;
}

.schedule-info .booking-date {
    font-weight: 600;
    margin-bottom: 8px;
    color: #495057;
}

.schedule-info .booking-date i {
    margin-right: 5px;
}

.time-slot-info {
    background: #f8f9fa;
    padding: 6px 8px;
    border-radius: 6px;
    border-left: 3px solid #28a745;
}

.time-slot-info .time-slot-badge {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 3px;
}

.time-slot-info .badge {
    font-size: 0.75em;
    padding: 3px 8px;
    border-radius: 10px;
}

.time-slot-info .badge.badge-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    box-shadow: 0 1px 3px rgba(40, 167, 69, 0.3);
}

.time-slot-info .badge.badge-info {
    background: linear-gradient(135deg, #17a2b8 0%, #6610f2 100%);
    color: white;
    box-shadow: 0 1px 3px rgba(23, 162, 184, 0.3);
}

.time-slot-day {
    font-style: italic;
    margin: 2px 0;
}

.time-slot-modifier {
    margin-top: 3px;
    font-weight: 600;
}

.booking-duration, .booking-guests {
    line-height: 1.3;
}

.booking-duration i, .booking-guests i {
    width: 14px;
    margin-right: 3px;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .schedule-column {
        min-width: 150px;
    }
    
    .time-slot-info {
        padding: 4px 6px;
    }
}

@media (max-width: 768px) {
    .time-slot-info .time-slot-badge {
        flex-direction: column;
        align-items: flex-start;
        gap: 3px;
    }
}
</style>
@endpush

@push('js')
    <script>
        $(document).on('click', '#set_paid_btn', function (e) {
            var id = $(this).data('id');
            $.ajax({
                url:bookingCore.url+'/booking/setPaidAmount',
                data:{
                    id: id,
                    remain: $('#modal-paid-'+id+' #set_paid_input').val(),
                },
                dataType:'json',
                type:'post',
                success:function(res){
                    alert(res.message);
                    window.location.reload();
                }
            });
        });
        $('.btn-detail-booking').on('click',function (e){
            var btn = $(this);
            $(this).find('.user_id').html(btn.data('id'));
            $(this).find('.modal-body').html('<div class="d-flex justify-content-center">{{__("Loading...")}}</div>');
            var modal = $("#modal_booking_detail");
            $.get(btn.data('ajax'), function (html){
                    modal.find('.modal-body').html(html);
                }
            )
        })
    </script>
@endpush
