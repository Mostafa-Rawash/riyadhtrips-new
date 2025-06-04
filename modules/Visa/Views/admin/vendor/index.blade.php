@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('My Visa Applications') }}</h1>
            <div class="title-actions">
                <a href="{{ route('visa.vendor.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> {{ __('Add New') }}
                </a>
            </div>
        </div>
        
        @include('admin.message')

        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-body">
                        <div class="filter-div d-flex justify-content-between">
                            <div class="col-left">
                                <form method="GET" action="{{ route('visa.vendor.index') }}" class="filter-form filter-form-list">
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search visa applications...') }}" class="form-control">
                                    <select name="status" class="form-control">
                                        <option value="">{{ __('All Status') }}</option>
                                        @foreach(config('visa.visa_statuses') as $key => $status)
                                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn-info btn btn-icon btn_search" type="submit">{{ __('Search') }}</button>
                                </form>
                            </div>
                            <div class="col-right">
                                <a href="{{ route('visa.vendor.recovery') }}" class="btn btn-recovery btn-primary" title="{{ __('Recovery') }}">
                                    <i class="fa fa-undo"></i> {{ __('Recovery') }}
                                </a>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('#Code') }}</th>
                                        <th>{{ __('Customer') }}</th>
                                        <th>{{ __('Visa Details') }}</th>
                                        <th>{{ __('Trip Date') }}</th>
                                        <th>{{ __('Travelers') }}</th>
                                        <th>{{ __('Price') }}</th>
                                        <th>{{ __('Payment') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Created') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($rows->count() > 0)
                                        @foreach($rows as $visa)
                                            <tr>
                                                <td>#{{ $visa->unique_code }}</td>
                                                <td>
                                                    <div>
                                                        <strong>{{ $visa->first_name }} {{ $visa->last_name }}</strong><br>
                                                        <small>{{ $visa->email }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong>{{ $visa->visa_name }}</strong><br>
                                                        <small>{{ $visa->country_name }}</small><br>
                                                        <small>{{ $visa->embassy_name }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    {{ $visa->scheduled_trip_date ? $visa->scheduled_trip_date->format('M d, Y') : '-' }}
                                                </td>
                                                <td>
                                                    {{ $visa->adults }} adults
                                                    @if($visa->childrens > 0)
                                                        <br>{{ $visa->childrens }} children
                                                    @endif
                                                </td>
                                                <td>{{ $visa->formatted_price }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $visa->payment_status_class }}">
                                                        {{ ucfirst($visa->payment_status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $visa->status_class }}">
                                                        {{ $visa->status_name }}
                                                    </span>
                                                </td>
                                                <td>{{ $visa->created_at->format('M d, Y H:i') }}</td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dropdownMenuButton{{ $visa->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            {{ __('Actions') }}
                                                        </button>
                                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $visa->id }}">
                                                            <a class="dropdown-item" href="{{ route('visa.vendor.edit', $visa->id) }}">{{ __('Edit') }}</a>
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item text-danger" href="{{ route('visa.vendor.delete', $visa->id) }}" onclick="return confirm('{{ __('Are you sure you want to delete this visa application?') }}')">{{ __('Delete') }}</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="10" class="text-center">{{ __('No visa applications found') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="bravo-pagination">
                            {{ $rows->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
