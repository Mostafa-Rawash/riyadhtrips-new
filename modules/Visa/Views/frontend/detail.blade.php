@extends('layouts.user')

@section('head')
    <link href="{{ asset('dist/frontend/module/user/css/user.css?_ver='.config('app.version')) }}" rel="stylesheet">
    <style>
        .visa-detail-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .detail-item {
            margin-bottom: 15px;
        }
        .detail-label {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .detail-value {
            font-size: 16px;
            color: #212529;
        }
        .status-timeline {
            position: relative;
            padding: 20px 0;
        }
        .timeline-item {
            position: relative;
            padding-left: 40px;
            padding-bottom: 20px;
        }
        .timeline-item:before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        .timeline-item:last-child:before {
            bottom: 20px;
        }
        .timeline-dot {
            position: absolute;
            left: 0;
            top: 8px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #6c757d;
            border: 2px solid #fff;
            box-shadow: 0 0 0 1px #dee2e6;
        }
        .timeline-dot.active {
            background: #28a745;
            box-shadow: 0 0 0 1px #28a745;
        }
    </style>
@endsection

@section('content')
    <div class="bravo-user-profile">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="user-content-wrapper">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h3>{{ __('Visa Application Details') }}</h3>
                                    <div>
                                        <a href="{{ route('visa.customer.history') }}" class="btn btn-secondary">
                                            <i class="fa fa-arrow-left"></i> {{ __('Back to History') }}
                                        </a>
                                        @if($visa->canEdit())
                                            <a href="{{ route('visa.customer.edit', $visa->id) }}" class="btn btn-primary">
                                                <i class="fa fa-edit"></i> {{ __('Edit') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <!-- Submissions Information (if multiple) -->
                                @if(isset($submissions) && $submissions->count() > 1)
                                    <div class="visa-detail-section">
                                        <h4 class="mb-3">{{ __('Multiple Visa Submissions') }}</h4>
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle"></i> {{ __('You have multiple visa submissions under this application.') }}
                                        </div>
                                        <div class="row">
                                            @foreach($submissions as $submission)
                                                <div class="col-md-6 mb-3">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h5 class="card-title">{{ $submission->visa_name }}</h5>
                                                            <h6 class="card-subtitle mb-2 text-muted">{{ $submission->country_name }}</h6>
                                                            <p class="card-text">
                                                                <strong>{{ __('Scheduled Trip:') }}</strong> {{ $submission->scheduled_trip_date ?? 'Not specified' }}<br>
                                                                <strong>{{ __('Travelers:') }}</strong> {{ $submission->adults ?? 0 }} adults, {{ $submission->childrens ?? 0 }} children
                                                            </p>
                                                            <a href="{{ route('visa.customer.submission', ['id' => $visa->id, 'submission_id' => $submission->id]) }}" class="btn btn-primary btn-sm">
                                                                <i class="fa fa-eye"></i> {{ __('View Details') }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Basic Information -->
                                <div class="visa-detail-section">
                                    <h4 class="mb-3">{{ __('Basic Information') }}</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Application Code') }}</div>
                                                <div class="detail-value">#{{ $visa->unique_code }}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Full Name') }}</div>
                                                <div class="detail-value">{{ $visa->first_name }} {{ $visa->last_name }}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Email') }}</div>
                                                <div class="detail-value">{{ $visa->email }}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Phone') }}</div>
                                                <div class="detail-value">{{ $visa->phone }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Contact Type') }}</div>
                                                <div class="detail-value">{{ $visa->contact_type }}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Relationship') }}</div>
                                                <div class="detail-value">{{ $visa->relationship }}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Application Date') }}</div>
                                                <div class="detail-value">{{ $visa->created_at->format('M d, Y H:i') }}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Last Updated') }}</div>
                                                <div class="detail-value">{{ $visa->updated_at->format('M d, Y H:i') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Visa Details -->
                                <div class="visa-detail-section">
                                    <h4 class="mb-3">{{ __('Visa Details') }}</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Visa Type') }}</div>
                                                <div class="detail-value">{{ $visa->visa_name }}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Country') }}</div>
                                                <div class="detail-value">{{ $visa->country_name }}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Embassy') }}</div>
                                                <div class="detail-value">{{ $visa->embassy_name }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Scheduled Trip Date') }}</div>
                                                <div class="detail-value">{{ $visa->scheduled_trip_date ? $visa->scheduled_trip_date->format('M d, Y') : '-' }}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Travelers') }}</div>
                                                <div class="detail-value">{{ $visa->adults }} adults, {{ $visa->childrens }} children</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Total Price') }}</div>
                                                <div class="detail-value">{{ $visa->formatted_price }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Information -->
                                <div class="visa-detail-section">
                                    <h4 class="mb-3">{{ __('Payment Information') }}</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Payment Status') }}</div>
                                                <div class="detail-value">
                                                    <span class="badge badge-{{ $visa->payment_status_class }}">
                                                        {{ ucfirst($visa->payment_status) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Payment Method') }}</div>
                                                <div class="detail-value">{{ $visa->payment_method }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Application Status -->
                                <div class="visa-detail-section">
                                    <h4 class="mb-3">{{ __('Application Status') }}</h4>
                                    <div class="current-status mb-4">
                                        <span class="badge badge-{{ $visa->status_class }} badge-lg">
                                            {{ $visa->status_name }}
                                        </span>
                                    </div>

                                    <div class="status-timeline">
                                        <div class="timeline-item">
                                            <div class="timeline-dot {{ $visa->status >= 0 ? 'active' : '' }}"></div>
                                            <div class="timeline-content">
                                                <h6>{{ __('Application Submitted') }}</h6>
                                                <small class="text-muted">{{ $visa->created_at->format('M d, Y H:i') }}</small>
                                            </div>
                                        </div>
                                        <div class="timeline-item">
                                            <div class="timeline-dot {{ $visa->status >= 1 ? 'active' : '' }}"></div>
                                            <div class="timeline-content">
                                                <h6>{{ __('Under Processing') }}</h6>
                                                <small class="text-muted">{{ $visa->status >= 1 ? $visa->updated_at->format('M d, Y H:i') : __('Pending') }}</small>
                                            </div>
                                        </div>
                                        <div class="timeline-item">
                                            <div class="timeline-dot {{ $visa->status == 2 || $visa->status == 5 ? 'active' : '' }}"></div>
                                            <div class="timeline-content">
                                                <h6>{{ __('Approved/Completed') }}</h6>
                                                <small class="text-muted">{{ ($visa->status == 2 || $visa->status == 5) ? $visa->updated_at->format('M d, Y H:i') : __('Pending') }}</small>
                                            </div>
                                        </div>
                                        @if($visa->status == 3)
                                            <div class="timeline-item">
                                                <div class="timeline-dot" style="background: #dc3545;"></div>
                                                <div class="timeline-content">
                                                    <h6>{{ __('Rejected') }}</h6>
                                                    <small class="text-muted">{{ $visa->updated_at->format('M d, Y H:i') }}</small>
                                                </div>
                                            </div>
                                        @endif
                                        @if($visa->status == 4)
                                            <div class="timeline-item">
                                                <div class="timeline-dot" style="background: #6c757d;"></div>
                                                <div class="timeline-content">
                                                    <h6>{{ __('Cancelled') }}</h6>
                                                    <small class="text-muted">{{ $visa->updated_at->format('M d, Y H:i') }}</small>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Additional Information -->
                                @if($visa->appointment)
                                    <div class="visa-detail-section">
                                        <h4 class="mb-3">{{ __('Notes/Response') }}</h4>
                                        <div class="detail-value">
                                            {!! nl2br(e($visa->appointment)) !!}
                                        </div>
                                    </div>
                                @endif

                                <!-- Actions -->
                                <div class="text-center mt-4">
                                    <!-- View Full Submission Data Button -->
                                    <a href="{{ route('visa.customer.submission', $visa->id) }}" class="btn btn-info btn-lg mb-3">
                                        <i class="fa fa-file-alt"></i> {{ __('View Complete Application Details') }}
                                    </a>
                                    <div class="small text-muted mb-4">{{ __('Click above to see all submitted information and documents') }}</div>
                                    
                                    @if($visa->canCancel())
                                        <form action="{{ route('visa.customer.cancel', $visa->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to cancel this visa application?') }}')">
                                            @csrf
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fa fa-times"></i> {{ __('Cancel Application') }}
                                            </button>
                                        </form>
                                    @endif
                                    @if($visa->payment_status != 'paid' && in_array($visa->status, [0, 1]))
                                        <a href="#" class="btn btn-success">
                                            <i class="fa fa-credit-card"></i> {{ __('Make Payment') }}
                                        </a>
                                    @endif
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
    <style>
        .badge-lg {
            padding: .5rem 1rem;
            font-size: 1rem;
        }
    </style>
@endsection