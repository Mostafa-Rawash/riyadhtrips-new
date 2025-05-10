@extends('admin.layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{__("All Visa Applications")}}</h1>
        </div>
        @include('admin.message')
        <div class="filter-div d-flex justify-content-between ">
            <div class="col-left">
                @if(!empty($rows))
                    <form method="post" action="{{route('visa.admin.bulkEdit')}}" class="filter-form filter-form-left d-flex justify-content-start">
                        {{csrf_field()}}
                        <select name="action" class="form-control">
                            <option value="">{{__(" Bulk Actions ")}}</option>
                            <option value="0">{{__(" Set as Pending ")}}</option>
                            <option value="1">{{__(" Set as Processing ")}}</option>
                            <option value="2">{{__(" Set as Approved ")}}</option>
                            <option value="3">{{__(" Set as Rejected ")}}</option>
                            <option value="4">{{__(" Set as Cancelled ")}}</option>
                            <option value="delete">{{__(" Delete ")}}</option>
                        </select>
                        <button data-confirm="{{__("Do you want to perform this action?")}}" class="btn-info btn btn-icon dungdt-apply-form-btn" type="button">{{__('Apply')}}</button>
                    </form>
                @endif
            </div>
            <div class="col-left dropdown">
                <form method="get" action="{{route('visa.admin.index')}}" class="filter-form filter-form-right d-flex justify-content-end flex-column flex-sm-row" role="search">
                    <input type="text" name="s" value="{{ Request()->s }}" placeholder="{{__('Search by name, email or code')}}" class="form-control">
                    <div class="ml-3 position-relative">
                        <button class="btn btn-secondary dropdown-toggle bc-dropdown-toggle-filter" type="button" id="dropdown_filters">
                            {{ __("Advanced") }}
                        </button>
                        <div class="dropdown-menu px-3 py-3 dropdown-menu-right" aria-labelledby="dropdown_filters">
                            <div class="mb-3">
                                <label class="d-block" for="status">{{ __("Status") }}</label>
                                <select name="status" class="form-control">
                                    <option value="">{{ __('-- All Status --')}} </option>
                                    @foreach($statuses as $status_id => $status_name)
                                        <option value="{{ $status_id }}" @if(Request()->status == $status_id) selected @endif>{{ $status_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="d-block" for="country_id">{{ __("Country") }}</label>
                                <select name="country_id" class="form-control">
                                    <option value="">{{ __('-- All Countries --')}} </option>
                                    @foreach($countries as $country_id => $country_name)
                                        <option value="{{ $country_id }}" @if(Request()->country_id == $country_id) selected @endif>{{ $country_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="d-block" for="payment_status">{{ __("Payment Status") }}</label>
                                <select name="payment_status" class="form-control">
                                    <option value="">{{ __('-- All Payment Status --')}} </option>
                                    @foreach($payment_statuses as $payment_status_id => $payment_status_name)
                                        <option value="{{ $payment_status_id }}" @if(Request()->payment_status == $payment_status_id) selected @endif>{{ $payment_status_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <button class="btn-info btn btn-icon btn_search" type="submit">{{__('Search')}}</button>
                </form>
            </div>
        </div>
        <div class="text-right">
            <p><i>{{__('Found :total items',['total'=>$rows->total()])}}</i></p>
        </div>
        <div class="panel">
            <div class="panel-body">
                <form action="" class="bravo-form-item">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th width="60px"><input type="checkbox" class="check-all"></th>
                                <th>{{ __('Code')}}</th>
                                <th>{{ __('Name')}}</th>
                                <th>{{ __('Email')}}</th>
                                <th>{{ __('Country')}}</th>
                                <th>{{ __('Visa Type')}}</th>
                                <th>{{ __('Date')}}</th>
                                <th>{{ __('Status')}}</th>
                                <th>{{ __('Payment')}}</th>
                                <th width="100px"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @if($rows->total() > 0)
                                @foreach($rows as $row)
                                    <tr>
                                        <td><input type="checkbox" name="ids[]" class="check-item" value="{{$row->id}}">
                                        </td>
                                        <td>#{{$row->unique_code}}</td>
                                        <td class="title">
                                            <a href="{{route('visa.admin.detail',['id'=>$row->id])}}">{{$row->full_name}}</a>
                                        </td>
                                        <td>{{$row->email}}</td>
                                        <td>{{$row->country_name}}</td>
                                        <td>{{$row->visa_name}}</td>
                                        <td>{{ display_date($row->created_at)}}</td>
                                        <td><span class="badge badge-{{ $row->status_class }}">{{ $row->status_name }}</span></td>
                                        <td>{{$row->payment_status}}</td>
                                        <td>
                                            <a href="{{route('visa.admin.detail',['id'=>$row->id])}}" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> {{__('View')}}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10">{{__("No data")}}</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </form>
                {{$rows->appends(request()->query())->links()}}
            </div>
        </div>
    </div>
@endsection