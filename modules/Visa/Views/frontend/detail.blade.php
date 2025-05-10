@extends('layouts.app')
@section('head')
    <link href="{{ asset('module/visa/css/visa.css?_ver='.config('app.asset_version')) }}" rel="stylesheet">
@endsection
@section('content')
    <div class="bravo_detail_visa">
        <div class="bravo_content">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 col-lg-9">
                        <div class="g-header">
                            <div class="left">
                                <h1>{{__('Visa Application: #:code', ['code' => $row->unique_code])}}</h1>
                            </div>
                        </div>
                        <div class="g-visa-detail">
                            <div class="card">
                                <div class="card-header">
                                    {{__("Application Details")}}
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="visa-info-item">
                                                <div class="label">{{__("Full Name")}}</div>
                                                <div class="value">{{$row->first_name}} {{$row->last_name}}</div>
                                            </div>
                                            <div class="visa-info-item">
                                                <div class="label">{{__("Email")}}</div>
                                                <div class="value">{{$row->email}}</div>
                                            </div>
                                            <div class="visa-info-item">
                                                <div class="label">{{__("Phone")}}</div>
                                                <div class="value">{{$row->phone}}</div>
                                            </div>
                                            <div class="visa-info-item">
                                                <div class="label">{{__("Contact Type")}}</div>
                                                <div class="value">{{$row->contact_type}}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="visa-info-item">
                                                <div class="label">{{__("Country")}}</div>
                                                <div class="value">{{$row->country_name}}</div>
                                            </div>
                                            <div class="visa-info-item">
                                                <div class="label">{{__("Visa Type")}}</div>
                                                <div class="value">{{$row->visa_name}}</div>
                                            </div>
                                            <div class="visa-info-item">
                                                <div class="label">{{__("Embassy")}}</div>
                                                <div class="value">{{$row->embassy_name}}</div>
                                            </div>
                                            <div class="visa-info-item">
                                                <div class="label">{{__("Status")}}</div>
                                                <div class="value">
                                                    <span class="badge badge-{{$row->status_class}}">{{$row->status_name}}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mt-4">
                                <div class="card-header">
                                    {{__("Trip Details")}}
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="visa-info-item">
                                                <div class="label">{{__("Scheduled Trip Date")}}</div>
                                                <div class="value">{{display_date($row->scheduled_trip_date)}}</div>
                                            </div>
                                            <div class="visa-info-item">
                                                <div class="label">{{__("Adults")}}</div>
                                                <div class="value">{{$row->adults}}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="visa-info-item">
                                                <div class="label">{{__("Children")}}</div>
                                                <div class="value">{{$row->childrens}}</div>
                                            </div>
                                            <div class="visa-info-item">
                                                <div class="label">{{__("Relationship")}}</div>
                                                <div class="value">{{$row->relationship}}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mt-4">
                                <div class="card-header">
                                    {{__("Payment Information")}}
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="visa-info-item">
                                                <div class="label">{{__("Total Price")}}</div>
                                                <div class="value">{{format_money($row->total_price)}}</div>
                                            </div>
                                            <div class="visa-info-item">
                                                <div class="label">{{__("Payment Method")}}</div>
                                                <div class="value">{{$row->payment_method}}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="visa-info-item">
                                                <div class="label">{{__("Payment Status")}}</div>
                                                <div class="value">{{$row->payment_status}}</div>
                                            </div>
                                            <div class="visa-info-item">
                                                <div class="label">{{__("Application Date")}}</div>
                                                <div class="value">{{display_date($row->created_at)}}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-3">
                        <div class="g-sidebar">
                            <div class="visa-status-box mb-4">
                                <div class="status-header">
                                    {{__("Application Status")}}
                                </div>
                                <div class="status-body">
                                    <div class="current-status">
                                        <span class="badge badge-{{$row->status_class}}">{{$row->status_name}}</span>
                                    </div>
                                    <div class="status-timeline mt-3">
                                        <div class="timeline-item @if($row->status >= 0) active @endif">
                                            <div class="timeline-point"></div>
                                            <div class="timeline-info">{{__("Application Submitted")}}</div>
                                        </div>
                                        <div class="timeline-item @if($row->status >= 1) active @endif">
                                            <div class="timeline-point"></div>
                                            <div class="timeline-info">{{__("Processing")}}</div>
                                        </div>
                                        <div class="timeline-item @if($row->status >= 2) active @endif">
                                            <div class="timeline-point"></div>
                                            <div class="timeline-info">{{__("Approved")}}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="sidebar-widget">
                                <div class="sidebar-title">
                                    <h4>{{__("Payment Information")}}</h4>
                                </div>
                                <div class="sidebar-content">
                                    <div class="info-item">
                                        <div class="label">{{__("Total Price")}}</div>
                                        <div class="value">{{format_money($row->total_price)}}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="label">{{__("Payment Status")}}</div>
                                        <div class="value">{{$row->payment_status}}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="label">{{__("Payment Method")}}</div>
                                        <div class="value">{{$row->payment_method}}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="sidebar-widget">
                                <div class="sidebar-title">
                                    <h4>{{__("Need Help?")}}</h4>
                                </div>
                                <div class="sidebar-content">
                                    <div class="contact-info">
                                        <div class="info-item">
                                            <i class="fa fa-phone"></i> {{__("Contact Support")}}: {{setting_item('site_phone')}}
                                        </div>
                                        <div class="info-item">
                                            <i class="fa fa-envelope"></i> {{__("Email")}}: {{setting_item('site_email')}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <script type="text/javascript" src="{{ asset('module/visa/js/visa.js?_ver='.config('app.asset_version')) }}"></script>
@endsection