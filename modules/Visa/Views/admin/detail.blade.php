@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('Visa Application Details') }}</h1>
            <div class="title-actions">
                @if($submissions->isNotEmpty())
                    <div class="dropdown mr-2">
                        <button class="btn btn-info dropdown-toggle" type="button" id="submissionDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-file-alt"></i> {{ __('View Submissions') }} ({{ $submissions->count() }})
                        </button>
                        <div class="dropdown-menu" aria-labelledby="submissionDropdown">
                            @foreach($submissions as $key => $sub)
                                <a class="dropdown-item" href="{{ route('visa.admin.submission_detail_specific', ['id' => $visa->id, 'submission_id' => $sub->id]) }}">
                                    {{ __('Submission') }} #{{ $key+1 }} - {{ $sub->visa_name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
                <a href="{{ route('visa.admin.index') }}" class="btn btn-primary">
                    <i class="fa fa-arrow-left"></i> {{ __('Back to List') }}
                </a>
                <a href="{{ route('visa.admin.edit', $visa->id) }}" class="btn btn-success">
                    <i class="fa fa-edit"></i> {{ __('Edit') }}
                </a>
            </div>
        </div>
        
        @include('admin.message')

        <div class="row">
            <div class="col-md-8">
                <div class="panel">
                    <div class="panel-title">{{ __('Application #:code', ['code' => $visa->unique_code]) }}</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="150">{{ __('Customer') }}:</td>
                                        <td><strong>{{ $visa->first_name }} {{ $visa->last_name }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Email') }}:</td>
                                        <td>{{ $visa->email }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Phone') }}:</td>
                                        <td>{{ $visa->phone }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Contact Type') }}:</td>
                                        <td>{{ ucfirst($visa->contact_type) }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Relationship') }}:</td>
                                        <td>{{ $visa->relationship }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="150">{{ __('Visa Type') }}:</td>
                                        <td><strong>{{ $visa->visa_name }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Country') }}:</td>
                                        <td>{{ $visa->country_name }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Embassy') }}:</td>
                                        <td>{{ $visa->embassy_name }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Trip Date') }}:</td>
                                        <td>{{ $visa->scheduled_trip_date ? $visa->scheduled_trip_date->format('M d, Y') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Travelers') }}:</td>
                                        <td>{{ $visa->adults }} adults, {{ $visa->childrens }} children</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                @if($visa->appointment)
                    <div class="panel">
                        <div class="panel-title">{{ __('Admin Notes/Response') }}</div>
                        <div class="panel-body">
                            {!! nl2br(e($visa->appointment)) !!}
                        </div>
                    </div>
                @endif
                
                <!-- Related Submissions -->
                @if($submissions->isNotEmpty())
                    <div class="panel">
                        <div class="panel-title">{{ __('Related Submissions') }}</div>
                        <div class="panel-body">
                            <!-- Filter bar for submissions -->
                            <div class="filter-div d-flex justify-content-between mb-3">
                                <form method="GET" class="form-inline">
                                    <select name="submission_filter" class="form-control form-control-sm mr-2">
                                        <option value="">{{ __('All Visa Types') }}</option>
                                        @php
                                            $visaTypes = $submissions->pluck('visa_name')->unique();
                                        @endphp
                                        @foreach($visaTypes as $visaType)
                                            <option value="{{ $visaType }}" {{ request('submission_filter') == $visaType ? 'selected' : '' }}>
                                                {{ $visaType }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fa fa-filter"></i> {{ __('Filter') }}
                                    </button>
                                    @if(request()->has('submission_filter'))
                                        <a href="{{ route('visa.admin.detail', $visa->id) }}" class="btn btn-sm btn-secondary ml-2">
                                            <i class="fa fa-times"></i> {{ __('Clear') }}
                                        </a>
                                    @endif
                                </form>
                                <div>
                                    <span class="badge badge-info">
                                        {{ __('Total Submissions') }}: {{ $submissions->count() }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Visa Type') }}</th>
                                            <th>{{ __('Country') }}</th>
                                            <th>{{ __('Trip Date') }}</th>
                                            <th>{{ __('Price') }}</th>
                                            <th>{{ __('Payment') }}</th>
                                            <th>{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($submissions as $key => $sub)
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td>{{ $sub->visa_name ?? '-' }}</td>
                                                <td>{{ $sub->country_name ?? '-' }}</td>
                                                <td>{{ $sub->scheduled_trip_date ?? '-' }}</td>
                                                <td>{{ number_format($sub->visa_price ?? 0, 2) }} SR</td>
                                                <td>
                                                    <span class="badge badge-{{ strtolower($sub->payment_status) == 'paid' ? 'success' : (strtolower($sub->payment_status) == 'pending' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($sub->payment_status ?? '-') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('visa.admin.submission_detail_specific', ['id' => $visa->id, 'submission_id' => $sub->id]) }}" class="btn btn-sm btn-primary">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Compare Submissions Button (Only show if there are multiple submissions) -->
                @if($submissions->count() > 1)
                    <div class="text-center mt-3 mb-3">
                        <a href="{{ route('visa.admin.compare_submissions', $visa->id) }}" class="btn btn-info btn-lg">
                            <i class="fa fa-exchange-alt"></i> {{ __('Compare Submissions') }}
                        </a>
                        <p class="text-muted mt-2 small">{{ __('Compare different visa submissions for this application') }}</p>
                    </div>
                @endif
            </div>
            <div class="col-md-4">
                <div class="panel">
                    <div class="panel-title">{{ __('Application Status') }}</div>
                    <div class="panel-body">
                        <table class="table table-borderless">
                            <tr>
                                <td width="120">{{ __('Status') }}:</td>
                                <td>
                                    <span class="badge badge-{{ $visa->status_class }}">
                                        {{ $visa->status_name }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ __('Payment') }}:</td>
                                <td>
                                    <span class="badge badge-{{ $visa->payment_status_class }}">
                                        {{ ucfirst($visa->payment_status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ __('Method') }}:</td>
                                <td>{{ ucfirst($visa->payment_method) }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('Total') }}:</td>
                                <td><strong>{{ $visa->formatted_price }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-title">{{ __('Timeline') }}</div>
                    <div class="panel-body">
                        <table class="table table-borderless">
                            <tr>
                                <td width="120">{{ __('Created') }}:</td>
                                <td>{{ $visa->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('Updated') }}:</td>
                                <td>{{ $visa->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-title">{{ __('Actions') }}</div>
                    <div class="panel-body text-center">
                        <a href="{{ route('visa.admin.edit', $visa->id) }}" class="btn btn-primary btn-block">
                            <i class="fa fa-edit"></i> {{ __('Edit Application') }}
                        </a>
                        <br>
                        <form action="{{ route('visa.admin.delete', $visa->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this visa application?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fa fa-trash"></i> {{ __('Delete Application') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
