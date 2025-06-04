@extends('layouts.user')

@section('head')
    <link href="{{ asset('dist/frontend/module/user/css/user.css?_ver='.config('app.version')) }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset("dist/frontend/css/daterangepicker.css") }}" >
    <style>
        .bravo-user-profile .user-profile-sidebar .sidebar-menu .active {
            background: #f5f5f5;
        }
        .visa-status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            text-transform: uppercase;
        }
        .visa-status-pending { background: #fff3cd; color: #856404; }
        .visa-status-processing { background: #cce5ff; color: #004085; }
        .visa-status-approved { background: #d4edda; color: #155724; }
        .visa-status-rejected { background: #f8d7da; color: #721c24; }
        .visa-status-cancelled { background: #e2e3e5; color: #383d41; }
        .visa-status-completed { background: #d4edda; color: #155724; }
    </style>
@endsection

@section('content')
    <div class="bravo-user-profile">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="user-content-wrapper">
                        <div class="bravo-user-booking">
                            <div class="booking-title-hd">
                                <h3 class="title">{{__('Visa History')}}</h3>
                                <a href="{{ route('visa.customer.history') }}" class="btn btn-primary">{{ __('View All') }}</a>
                            </div>

                            <!-- Visa Summary Stats -->
                            <div class="row mb-4">
                                <div class="col-md-2 col-6">
                                    <div class="card text-center bg-light">
                                        <div class="card-body py-2">
                                            <h4 class="card-title mb-0">{{ $summary['total'] }}</h4>
                                            <p class="card-text mb-0 small">{{ __('Total') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6">
                                    <div class="card text-center bg-warning">
                                        <div class="card-body py-2">
                                            <h4 class="card-title mb-0 text-white">{{ $summary['pending'] }}</h4>
                                            <p class="card-text mb-0 small text-white">{{ __('Pending') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6">
                                    <div class="card text-center bg-info">
                                        <div class="card-body py-2">
                                            <h4 class="card-title mb-0 text-white">{{ $summary['processing'] }}</h4>
                                            <p class="card-text mb-0 small text-white">{{ __('Processing') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6">
                                    <div class="card text-center bg-success">
                                        <div class="card-body py-2">
                                            <h4 class="card-title mb-0 text-white">{{ $summary['approved'] }}</h4>
                                            <p class="card-text mb-0 small text-white">{{ __('Approved') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6">
                                    <div class="card text-center bg-danger">
                                        <div class="card-body py-2">
                                            <h4 class="card-title mb-0 text-white">{{ $summary['rejected'] }}</h4>
                                            <p class="card-text mb-0 small text-white">{{ __('Rejected') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6">
                                    <div class="card text-center bg-light">
                                        <div class="card-body py-2">
                                            <h4 class="card-title mb-0">{{ number_format($summary['total_spent'], 2) }} SR</h4>
                                            <p class="card-text mb-0 small">{{ __('Total Spent') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filters -->
                            <div class="booking-filter-box">
                                <form action="{{ route('visa.customer.history') }}" method="GET" class="d-flex justify-content-between align-items-end">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label>{{ __('Status') }}</label>
                                            <select name="status" class="form-control">
                                                <option value="">{{ __('All Status') }}</option>
                                                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('Processing') }}</option>
                                                <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                                                <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                                                <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                                                <option value="5" {{ request('status') == '5' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>{{ __('Payment Status') }}</label>
                                            <select name="payment_status" class="form-control">
                                                <option value="">{{ __('All Payment Status') }}</option>
                                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                                                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                                <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>{{ __('Failed') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label>{{ __('Search') }}</label>
                                            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search visa, country, embassy...') }}" class="form-control">
                                        </div>
                                        <div class="col-md-2">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn-primary form-control">{{ __('Search') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Visa Applications Table -->
                            <div class="booking-list">
                                @if($visaApplications->count() > 0)
                                    <div class="booking-list-manager table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>{{ __('#Code') }}</th>
                                                    <th>{{ __('Visa Details') }}</th>
                                                    <th>{{ __('Trip Date') }}</th>
                                                    <th>{{ __('Travelers') }}</th>
                                                    <th>{{ __('Price') }}</th>
                                                    <th>{{ __('Payment') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th>{{ __('Actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($visaApplications as $visa)
                                                    <tr>
                                                        <td>#{{ $visa->unique_code }}</td>
                                                        <td>
                                                            <strong>{{ $visa->visa_name }}</strong><br>
                                                            <small>{{ $visa->country_name }}</small><br>
                                                            <small>{{ $visa->embassy_name }}</small>
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
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <a href="{{ route('visa.customer.detail', $visa->id) }}" class="btn btn-xs btn-info" title="{{ __('View Details') }}">
                                                                    <i class="fa fa-eye"></i>
                                                                </a>
                                                                @if($visa->canEdit())
                                                                    <a href="{{ route('visa.customer.edit', $visa->id) }}" class="btn btn-xs btn-primary" title="{{ __('Edit') }}">
                                                                        <i class="fa fa-edit"></i>
                                                                    </a>
                                                                @endif
                                                                @if($visa->canCancel())
                                                                    <form action="{{ route('visa.customer.cancel', $visa->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to cancel this visa application?') }}')">
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-xs btn-danger" title="{{ __('Cancel') }}">
                                                                            <i class="fa fa-times"></i>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    {{ $visaApplications->appends(request()->query())->links() }}
                                @else
                                    <div class="alert alert-info">
                                        {{ __('No visa applications found') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
@endsection
