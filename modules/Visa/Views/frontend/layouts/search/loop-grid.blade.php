@php
    $translation = $row;
@endphp
<div class="item-loop {{$wrap_class ?? ''}}">
    <div class="visa-card">
        <div class="visa-header">
            <div class="visa-code">
                #{{$row->unique_code}}
            </div>
            <div class="visa-status">
                <span class="badge badge-{{$row->status_class}}">{{$row->status_name}}</span>
            </div>
        </div>
        <div class="visa-body">
            <div class="visa-info">
                <h5 class="visa-title">
                    <a href="{{route('visa.detail', ['id' => $row->id])}}">
                        {{$row->full_name}}
                    </a>
                </h5>
                <div class="visa-meta">
                    <div class="meta-item">
                        <i class="fa fa-globe"></i> {{$row->country_name}}
                    </div>
                    <div class="meta-item">
                        <i class="fa fa-id-card"></i> {{$row->visa_name}}
                    </div>
                    <div class="meta-item">
                        <i class="fa fa-calendar"></i> {{display_date($row->created_at)}}
                    </div>
                </div>
                <div class="visa-details">
                    <div class="detail-item">
                        <i class="fa fa-plane"></i> {{__("Trip Date")}}: {{display_date($row->scheduled_trip_date)}}
                    </div>
                    <div class="detail-item">
                        <i class="fa fa-money"></i> {{__("Total")}}: {{format_money($row->total_price)}}
                    </div>
                    <div class="detail-item">
                        <i class="fa fa-credit-card"></i> {{__("Payment")}}: {{$row->payment_status}}
                    </div>
                </div>
            </div>
        </div>
        <div class="visa-footer">
            <a href="{{route('visa.detail', ['id' => $row->id])}}" class="btn btn-primary">{{__("View Details")}}</a>
        </div>
    </div>
</div>