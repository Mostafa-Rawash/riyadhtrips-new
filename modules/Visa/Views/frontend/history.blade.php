```php
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
        
        /* Enhanced table styles */
        .submission-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
            border-radius: 0.25rem;
            overflow: hidden;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .submission-table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            padding: 0.75rem;
            vertical-align: middle;
            text-align: left;
            font-weight: 600;
            color: #495057;
        }
        
        .submission-table tbody tr {
            border-bottom: 1px solid #dee2e6;
            transition: background-color 0.15s ease-in-out;
        }
        
        .submission-table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.04);
            cursor: pointer;
        }
        
        .submission-table tbody td {
            padding: 0.75rem;
            vertical-align: middle;
        }
        
        .submission-table .visa-code {
            font-weight: 600;
            color: #007bff;
        }
        
        .submission-table .visa-details {
            max-width: 250px;
        }
        
        .submission-table .visa-details .visa-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .submission-table .visa-details .visa-country,
        .submission-table .visa-details .visa-embassy {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .submission-table .action-buttons {
            display: flex;
            gap: 0.25rem;
        }
        
        .submission-table .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
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
                            <div class="booking-filter-box mb-4">
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

                            <!-- Visa Applications Table (Enhanced Version) -->
                            <div class="booking-list">
                                @if($visaApplications->count() > 0)
                                    <div class="table-responsive">
                                        <table class="submission-table">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Visa Code') }}</th>
                                                    <th>{{ __('Visa Details') }}</th>
                                                    <th>{{ __('Trip Date') }}</th>
                                                    <th>{{ __('Travelers') }}</th>
                                                    <th>{{ __('Price') }}</th>
                                                    <th>{{ __('Payment') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th>{{ __('Submissions') }}</th>
                                                    <th>{{ __('Actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($visaApplications as $visa)
                                                    <tr class="visa-row" data-id="{{ $visa->id }}" onclick="window.location.href='{{ route('visa.customer.detail', $visa->id) }}'">
                                                        <td class="visa-code">#{{ $visa->unique_code }}</td>
                                                        <td class="visa-details">
                                                            <div class="visa-name">{{ $visa->visa_name }}</div>
                                                            <div class="visa-country">{{ $visa->country_name }}</div>
                                                            <div class="visa-embassy">{{ $visa->embassy_name }}</div>
                                                        </td>
                                                        <td>
                                                            {{ $visa->scheduled_trip_date ? $visa->scheduled_trip_date->format('M d, Y') : '-' }}
                                                        </td>
                                                        <td>
                                                            {{ $visa->adults }} {{ __('adults') }}
                                                            @if($visa->childrens > 0)
                                                                <br>{{ $visa->childrens }} {{ __('children') }}
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
                                                            <span class="badge badge-info">
                                                                {{ $visa->submissions_count ?? 0 }}
                                                            </span>
                                                        </td>
                                                        <td onclick="event.stopPropagation()">
                                                            <div class="action-buttons">
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
<script>
    // Add JavaScript to make the entire row clickable except for action buttons
    document.addEventListener('DOMContentLoaded', function() {
        const visaRows = document.querySelectorAll('.visa-row');
        visaRows.forEach(row => {
            row.style.cursor = 'pointer';
        });
    });
</script>
@endsection
```