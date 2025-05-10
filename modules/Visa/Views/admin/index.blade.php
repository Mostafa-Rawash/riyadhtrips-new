@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('Visa Applications') }}</h1>
            <div class="title-actions">
                <a href="{{ route('visa.admin.statistics') }}" class="btn btn-info">
                    <i class="fa fa-chart-bar"></i> {{ __('Statistics') }}
                </a>
            </div>
        </div>
        
        @include('admin.message')

        <div class="filter-div d-flex justify-content-between">
            <div class="col-left">
                <form method="GET" action="{{ route('visa.admin.index') }}" class="filter-form filter-form-list d-inline-flex">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search visa applications...') }}" class="form-control">
                    <select name="status" class="form-control">
                        <option value="">{{ __('All Status') }}</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('Processing') }}</option>
                        <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                        <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                        <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                        <option value="5" {{ request('status') == '5' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                    </select>
                    <select name="payment_status" class="form-control">
                        <option value="">{{ __('All Payment Status') }}</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>{{ __('Failed') }}</option>
                    </select>
                    <button class="btn-info btn btn-icon btn_search" type="submit">{{ __('Search') }}</button>
                </form>
            </div>
            <div class="col-right">
                <div class="dropdown" id="actionDropdown">
                    <button class="btn btn-success btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ __('Bulk Actions') }}
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="#" data-action="approve">{{ __('Approve') }}</a>
                        <a class="dropdown-item" href="#" data-action="reject">{{ __('Reject') }}</a>
                        <a class="dropdown-item" href="#" data-action="delete">{{ __('Delete') }}</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-body">
                        <form action="{{ route('visa.admin.bulk-actions') }}" method="POST" id="bulk-action-form">
                            @csrf
                            <input type="hidden" name="action" id="bulk-action-input">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="check-all"></th>
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
                                                    <td><input type="checkbox" name="ids[]" value="{{ $visa->id }}"></td>
                                                    <td>#{{ $visa->unique_code }}</td>
                                                    <td>
                                                        <div>
                                                            <strong>{{ $visa->first_name }} {{ $visa->last_name }}</strong><br>
                                                            <small>{{ $visa->email }}</small><br>
                                                            <small>{{ $visa->phone }}</small>
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
                                                                <a class="dropdown-item" href="{{ route('visa.admin.detail', $visa->id) }}">{{ __('View Details') }}</a>
                                                                <a class="dropdown-item" href="{{ route('visa.admin.edit', $visa->id) }}">{{ __('Edit') }}</a>
                                                                <div class="dropdown-divider"></div>
                                                                <form action="{{ route('visa.admin.delete', $visa->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this visa application?') }}')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item text-danger">{{ __('Delete') }}</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="11" class="text-center">{{ __('No visa applications found') }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </form>

                        <div class="bravo-pagination">
                            {{ $rows->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <script>
        $(document).ready(function() {
            // Check all functionality
            $('#check-all').change(function() {
                $('input[name="ids[]"]').prop('checked', $(this).prop('checked'));
            });

            // Bulk actions
            $('#actionDropdown .dropdown-item').click(function(e) {
                e.preventDefault();
                var action = $(this).data('action');
                var selectedItems = $('input[name="ids[]"]:checked').length;
                
                if (selectedItems === 0) {
                    alert('{{ __('Please select at least one item') }}');
                    return;
                }

                if (confirm('{{ __('Are you sure you want to perform this action?') }}')) {
                    $('#bulk-action-input').val(action);
                    $('#bulk-action-form').submit();
                }
            });
        });
    </script>
@endsection
