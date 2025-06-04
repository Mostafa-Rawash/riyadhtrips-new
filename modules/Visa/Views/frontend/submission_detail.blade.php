                                <!-- Edit Notification (only shown if editable) -->
                                <!-- @if($visa->canEdit())
                                    <div class="alert alert-info mb-4">
                                        <div class="d-flex align-items-center">
                                            <i class="fa fa-edit mr-3" style="font-size: 24px;"></i>
                                            <div>
                                                <strong>{{ __('You can edit this submission') }}</strong>
                                                <p class="mb-0">{{ __('While your application is being processed, you can update your personal and travel information. Some fields like visa type and country cannot be changed.') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif -->
                                @extends('layouts.user')

@section('head')
    <link href="{{ asset('dist/frontend/module/user/css/user.css?_ver='.config('app.version')) }}" rel="stylesheet">
    <style>
        .submission-detail-section {
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
        .document-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .document-card img {
            width: 100%;
            height: auto;
            max-height: 150px;
            object-fit: cover;
        }
        .document-card .card-body {
            padding: 10px;
        }
        .document-card .card-title {
            font-size: 14px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .document-card .btn {
            width: 100%;
            margin-top: 10px;
        }
        
        /* Submission badges and tabs */
        .submissions-nav {
            display: flex;
            overflow-x: auto;
            margin-bottom: 20px;
            padding-bottom: 5px;
            border-bottom: 1px solid #dee2e6;
        }
        .submissions-nav .nav-item {
            flex: 0 0 auto;
            margin-right: 5px;
        }
        .submissions-nav .nav-link {
            padding: 8px 15px;
            border-radius: 20px;
            white-space: nowrap;
            color: #495057;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .submissions-nav .nav-link.active {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
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
                                    <div>
                                        <h3>{{ __('Visa Submission Details') }}</h3>
                                        <p class="text-muted mb-0">{{ __('Application Code') }}: #{{ $visa->unique_code }}</p>
                                    </div>
                                    <div>
                                        @if($visa->canEdit())
                                            <a href="{{ route('visa.customer.edit_submission', [$visa->id, $submission->id]) }}" class="btn btn-primary">
                                                <i class="fa fa-edit"></i> {{ __('Edit This Submission') }}
                                            </a>
                                        @endif
                                        <a href="{{ route('visa.customer.history') }}" class="btn btn-secondary ml-2">
                                            <i class="fa fa-arrow-left"></i> {{ __('Back to History') }}
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Submission Selection Tabs (if multiple submissions) -->
                                @if($submissions->count() > 1)
                                    <div class="submissions-nav">
                                        @foreach($submissions as $key => $sub)
                                            <div class="nav-item">
                                                <a href="{{ route('visa.customer.submission', ['id' => $visa->id, 'submission_id' => $sub->id]) }}" 
                                                   class="nav-link {{ $sub->id == $submission->id ? 'active' : '' }}">
                                                    <i class="fa fa-file-alt"></i> 
                                                    {{ $submission->first_name }} {{ $submission->last_name }} visa requist
                                                    <small>({{ $sub->country_name }})</small>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Application Status -->
                                <div class="submission-detail-section">
                                    <div class="d-flex justify-content-between">
                                        <h4 class="mb-3">{{ __('Application Status') }}</h4>
                                        <div>
                                            <span class="badge badge-{{ $visa->status_class }} p-2">
                                                {{ $visa->status_name }}
                                            </span>
                                        </div>
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

                                <!-- Basic Information -->
                                <div class="submission-detail-section">
                                    <h4 class="mb-3">{{ __('Visa Information') }}</h4>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Visa Type') }}</div>
                                                <div class="detail-value">{{ $submission->visa_name }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Country') }}</div>
                                                <div class="detail-value">{{ $submission->country_name }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Embassy') }}</div>
                                                <div class="detail-value">{{ $submission->embassy_name }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Trip Date') }}</div>
                                                <div class="detail-value">{{ $submission->scheduled_trip_date ?? '-' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Arrival Date') }}</div>
                                                <div class="detail-value">{{ $submission->arrival_date ?? '-' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Stay Length') }}</div>
                                                <div class="detail-value">{{ $submission->stay_length ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Travelers') }}</div>
                                                <div class="detail-value">
                                                    {{ $submission->adults ?? 0 }} adults, 
                                                    {{ $submission->childrens ?? 0 }} children
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Preferred Choice') }}</div>
                                                <div class="detail-value">{{ $submission->preferred_choice ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Personal Information -->
                                <div class="submission-detail-section">
                                    <h4 class="mb-3">{{ __('Personal Information') }}</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Full Name') }}</div>
                                                <div class="detail-value">{{ $submission->first_name }} {{ $submission->last_name }}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Nationality') }}</div>
                                                <div class="detail-value">{{ $submission->nationality ?? '-' }}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Mother\'s Name') }}</div>
                                                <div class="detail-value">{{ ($submission->mother_name ?? '-') . ' ' . ($submission->mother_last ?? '') }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Marital Status') }}</div>
                                                <div class="detail-value">{{ $submission->marital_status ?? '-' }}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Relationship') }}</div>
                                                <div class="detail-value">{{ $submission->relationship ?? '-' }}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Traveler Relation') }}</div>
                                                <div class="detail-value">{{ $submission->traveler_relation ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    @if(!empty($submission->persnol_img))
                                        <div class="row mt-3">
                                            <div class="col-md-3">
                                                <div class="detail-label">{{ __('Personal Photo') }}</div>
                                                <img src="{{ filter_var($submission->persnol_img, FILTER_VALIDATE_URL) ? $submission->persnol_img : asset('https://visa.riyadhtrips.com/' . $submission->persnol_img) }}" 
                                                     alt="{{ $submission->first_name }}" class="img-thumbnail" style="max-height: 150px;">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Previous Visa Information -->
                                <div class="submission-detail-section">
                                    <h4 class="mb-3">{{ __('Previous Visa Information') }}</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Previous Visa') }}</div>
                                                <div class="detail-value">{{ $submission->last_visa ?? '-' }}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Schengen Visa') }}</div>
                                                <div class="detail-value">{{ $submission->schengen_visa ?? '-' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('US Visa Before') }}</div>
                                                <div class="detail-value">{{ $submission->usvisa_before ?? '-' }}</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('US Visa Cancelled') }}</div>
                                                <div class="detail-value">{{ $submission->usvisa_cancel ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('US Relatives') }}</div>
                                                <div class="detail-value">{{ $submission->us_relatives ?? '-' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Last Country Visited') }}</div>
                                                <div class="detail-value">{{ $submission->last_country ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    @if(!empty($submission->Visacancel_explain))
                                        <div class="row mt-3">
                                            <div class="col-md-12">
                                                <div class="detail-item">
                                                    <div class="detail-label">{{ __('Visa Cancellation Explanation') }}</div>
                                                    <div class="detail-value">{{ $submission->Visacancel_explain }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Payment Information -->
                                <div class="submission-detail-section">
                                    <h4 class="mb-3">{{ __('Payment Information') }}</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Payment Status') }}</div>
                                                <div class="detail-value">
                                                    <span class="badge badge-{{ strtolower($submission->payment_status) == 'paid' ? 'success' : (strtolower($submission->payment_status) == 'pending' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($submission->payment_status ?? '-') }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Payment Method') }}</div>
                                                <div class="detail-value">{{ $submission->payment_method ?? '-' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Visa Price') }}</div>
                                                <div class="detail-value">{{ number_format($submission->visa_price ?? 0, 2) }} SR</div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Transaction ID') }}</div>
                                                <div class="detail-value">{{ $submission->transactionid ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Paid By') }}</div>
                                                <div class="detail-value">{{ $submission->paid_by ?? '-' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <div class="detail-label">{{ __('Paid By Relation') }}</div>
                                                <div class="detail-value">{{ $submission->paid_by_relation ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Appointment Information -->
                                @if(!empty($submission->appointment))
                                    <div class="submission-detail-section">
                                        <h4 class="mb-3">{{ __('Appointment Information') }}</h4>
                                        <div class="detail-item">
                                            <div class="detail-value">{{ $submission->appointment }}</div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Documents Section -->
                                <div class="submission-detail-section">
                                    <h4 class="mb-3">{{ __('Uploaded Documents') }}</h4>
                                    <div class="row">
                                        <!-- Check each document field and display if available -->
                                        @php
                                            $documentFields = [
                                                'passport_url' => 'Passport',
                                                'family_card' => 'Family Card',
                                                'salary_id_uploads' => 'Salary ID',
                                                'marital_status_uploads' => 'Marital Status Document',
                                                'account_states_uploads' => 'Account Statement',
                                                'civil_url' => 'Civil Document',
                                                'last_visa_uploads' => 'Previous Visa',
                                                'us_visa_upload' => 'US Visa',
                                                'relation_passport' => 'Relation Passport'
                                            ];
                                            $hasDocuments = false;
                                        @endphp
                                        
                                        @foreach($documentFields as $field => $label)
                                            @if(!empty($submission->{$field}))
                                                @php $hasDocuments = true; @endphp
                                                <div class="col-md-3">
                                                    <div class="document-card">
                                                        <div class="card-body">
                                                            <h5 class="card-title">{{ __($label) }}</h5>
                                                            <a href="{{ filter_var($submission->{$field}, FILTER_VALIDATE_URL) ? $submission->{$field} : asset('https://visa.riyadhtrips.com/' . $submission->{$field}) }}" 
                                                               target="_blank" class="btn btn-sm btn-primary">
                                                                <i class="fa fa-download"></i> {{ __('View Document') }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                        
                                        @if(!$hasDocuments)
                                            <div class="col-md-12">
                                                <div class="alert alert-info">
                                                    {{ __('No documents were uploaded with this submission.') }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Actions Section -->
                                <div class="text-center mt-4 mb-4">
                                    @if($visa->canEdit())
                                        <a href="{{ route('visa.customer.edit_submission', [$visa->id, $submission->id]) }}" class="btn btn-primary btn-lg">
                                            <i class="fa fa-edit"></i> {{ __('Edit Submission') }}
                                        </a>
                                    @endif
                                    
                                    <a href="{{ route('visa.customer.history') }}" class="btn btn-secondary btn-lg ml-2">
                                        <i class="fa fa-arrow-left"></i> {{ __('Back to History') }}
                                    </a>
                                    
                                    @if($visa->canCancel())
                                        <form action="{{ route('visa.customer.cancel', $visa->id) }}" method="POST" class="d-inline ml-2" 
                                              onsubmit="return confirm('{{ __('Are you sure you want to cancel this visa application?') }}')">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-lg">
                                                <i class="fa fa-times"></i> {{ __('Cancel Application') }}
                                            </button>
                                        </form>
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
        
        /* Floating Edit Button */
        .floating-edit-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 999;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            transition: all 0.3s ease;
        }
        .floating-edit-btn:hover {
            transform: scale(1.1);
        }
        
        /* Editable section highlight */
        .editable-section {
            position: relative;
        }
        .editable-section::after {
            content: '\270E';
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 16px;
            color: #007bff;
            opacity: 0.5;
        }
        .editable-section:hover::after {
            opacity: 1;
        }
    </style>
@endsection