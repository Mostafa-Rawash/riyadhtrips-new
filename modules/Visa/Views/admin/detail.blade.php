@extends('admin.layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{$page_title}}</h1>
        </div>
        @include('admin.message')
        <div class="row">
            <div class="col-md-8">
                <div class="panel">
                    <div class="panel-title"><strong>{{__("Application Details")}}</strong></div>
                    <div class="panel-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">{{__("Application Code")}}</th>
                                <td>#{{$row->unique_code}}</td>
                            </tr>
                            <tr>
                                <th>{{__("Full Name")}}</th>
                                <td>{{$row->first_name}} {{$row->last_name}}</td>
                            </tr>
                            <tr>
                                <th>{{__("Email")}}</th>
                                <td>{{$row->email}}</td>
                            </tr>
                            <tr>
                                <th>{{__("Phone")}}</th>
                                <td>{{$row->phone}}</td>
                            </tr>
                            <tr>
                                <th>{{__("Contact Type")}}</th>
                                <td>{{$row->contact_type}}</td>
                            </tr>
                            <tr>
                                <th>{{__("Country")}}</th>
                                <td>{{$row->country_name}}</td>
                            </tr>
                            <tr>
                                <th>{{__("Visa Type")}}</th>
                                <td>{{$row->visa_name}}</td>
                            </tr>
                            <tr>
                                <th>{{__("Embassy")}}</th>
                                <td>{{$row->embassy_name}}</td>
                            </tr>
                            <tr>
                                <th>{{__("Scheduled Trip Date")}}</th>
                                <td>{{display_date($row->scheduled_trip_date)}}</td>
                            </tr>
                            <tr>
                                <th>{{__("Travelers")}}</th>
                                <td>{{__("Adults")}}: {{$row->adults}}, {{__("Children")}}: {{$row->childrens}}</td>
                            </tr>
                            <tr>
                                <th>{{__("Relationship")}}</th>
                                <td>{{$row->relationship}}</td>
                            </tr>
                            <tr>
                                <th>{{__("Appointment")}}</th>
                                <td>{{$row->appointment}}</td>
                            </tr>
                            <tr>
                                <th>{{__("Created At")}}</th>
                                <td>{{display_date($row->created_at)}}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <form action="{{route('visa.admin.update', ['id' => $row->id])}}" method="post">
                    @csrf
                    <div class="panel">
                        <div class="panel-title"><strong>{{__("Payment Information")}}</strong></div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label>{{__("Total Price")}}</label>
                                <div class="form-control">{{format_money($row->total_price)}}</div>
                            </div>
                            <div class="form-group">
                                <label>{{__("Payment Method")}}</label>
                                <div class="form-control">{{$row->payment_method}}</div>
                            </div>
                            <div class="form-group">
                                <label>{{__("Payment Status")}}</label>
                                <select name="payment_status" class="form-control">
                                    @foreach($payment_statuses as $key => $status)
                                        <option value="{{$key}}" @if($row->payment_status == $key) selected @endif>{{$status}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="panel">
                        <div class="panel-title"><strong>{{__("Status Management")}}</strong></div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label>{{__("Status")}}</label>
                                <select name="status" class="form-control">
                                    @foreach($statuses as $key => $status)
                                        <option value="{{$key}}" @if($row->status == $key) selected @endif>{{$status}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>{{__("Admin Notes")}}</label>
                                <textarea name="notes" rows="5" class="form-control">{{$row->notes ?? ''}}</textarea>
                            </div>
                            <button class="btn btn-primary" type="submit">{{__("Update")}}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection