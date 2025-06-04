@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('Visa Submission Details') }}</h1>
            <div class="title-actions">
                @if($submissions->count() > 1)
                    <div class="dropdown mr-2">
                        <button class="btn btn-info dropdown-toggle" type="button" id="submissionDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-exchange-alt"></i> {{ __('Switch Submission') }} ({{ $submissions->count() }})
                        </button>
                        <div class="dropdown-menu" aria-labelledby="submissionDropdown">
                            @foreach($submissions as $key => $sub)
                                <a class="dropdown-item {{ $sub->id == $submission->id ? 'active' : '' }}" 
                                   href="{{ route('visa.admin.submission_detail_specific', ['id' => $visa->id, 'submission_id' => $sub->id]) }}">
                                    {{ __('Submission') }} #{{ $key+1 }} - {{ $sub->visa_name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
                <a href="{{ route('visa.admin.detail', $visa->id) }}" class="btn btn-primary">
                    <i class="fa fa-arrow-left"></i> {{ __('Back to Application') }}
                </a>
                <a href="{{ route('visa.admin.edit', $visa->id) }}" class="btn btn-success">
                    <i class="fa fa-edit"></i> {{ __('Edit') }}
                </a>
            </div>
        </div>
        
        @include('admin.message')

        <div class="row">
            <div class="col-md-8">
                <!-- Basic Information -->
                <div class="panel">
                    <div class="panel-title">{{ __('Application #:code', ['code' => $visa->unique_code]) }}</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="150">{{ __('Customer') }}:</td>
                                        <td><strong>{{ $submission->first_name }} {{ $submission->last_name }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Nationality') }}:</td>
                                        <td>{{ $submission->nationality ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Mother\'s Name') }}:</td>
                                        <td>{{ ($submission->mother_name ?? '-') . ' ' . ($submission->mother_last ?? '') }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Marital Status') }}:</td>
                                        <td>{{ $submission->marital_status ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Relationship') }}:</td>
                                        <td>{{ $submission->relationship ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="150">{{ __('Visa Type') }}:</td>
                                        <td><strong>{{ $submission->visa_name }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Country') }}:</td>
                                        <td>{{ $submission->country_name }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Embassy') }}:</td>
                                        <td>{{ $submission->embassy_name }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Trip Date') }}:</td>
                                        <td>{{ $submission->scheduled_trip_date ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Travelers') }}:</td>
                                        <td>{{ $submission->adults ?? 0 }} adults, {{ $submission->childrens ?? 0 }} children</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Travel Details -->
                <div class="panel">
                    <div class="panel-title">{{ __('Travel Information') }}</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="150">{{ __('Arrival Date') }}:</td>
                                        <td>{{ $submission->arrival_date ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Stay Length') }}:</td>
                                        <td>{{ $submission->stay_length ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Last Country Visited') }}:</td>
                                        <td>{{ $submission->last_country ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Travel Year') }}:</td>
                                        <td>{{ $submission->travel_year ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="150">{{ __('Previous Visa') }}:</td>
                                        <td>{{ $submission->last_visa ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Schengen Visa') }}:</td>
                                        <td>{{ $submission->schengen_visa ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('US Visa Before') }}:</td>
                                        <td>{{ $submission->usvisa_before ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('US Visa Cancelled') }}:</td>
                                        <td>{{ $submission->usvisa_cancel ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('US Relatives') }}:</td>
                                        <td>{{ $submission->us_relatives ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        @if(!empty($submission->Visacancel_explain))
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h5>{{ __('Visa Cancellation Explanation') }}</h5>
                                    <p>{{ $submission->Visacancel_explain }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="panel">
                    <div class="panel-title">{{ __('Payment Information') }}</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="150">{{ __('Paid By') }}:</td>
                                        <td>{{ $submission->paid_by ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Relation to Applicant') }}:</td>
                                        <td>{{ $submission->paid_by_relation ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Account Owner') }}:</td>
                                        <td>{{ $submission->account_owner ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="150">{{ __('Payment Status') }}:</td>
                                        <td>
                                            <span class="badge badge-{{ strtolower($submission->payment_status) == 'paid' ? 'success' : (strtolower($submission->payment_status) == 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($submission->payment_status ?? '-') }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Payment Method') }}:</td>
                                        <td>{{ ucfirst($submission->payment_method ?? '-') }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Transaction ID') }}:</td>
                                        <td>{{ $submission->transactionid ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Visa Price') }}:</td>
                                        <td><strong>{{ number_format($submission->visa_price ?? 0, 2) }} SR</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appointment Information -->
                @if(!empty($submission->appointment))
                    <div class="panel">
                        <div class="panel-title">{{ __('Appointment Notes') }}</div>
                        <div class="panel-body">
                            {!! nl2br(e($submission->appointment)) !!}
                        </div>
                    </div>
                @endif
            </div>
            <div class="col-md-4">
                <!-- Submission Info -->
                <div class="panel">
                    <div class="panel-title">{{ __('Submission Information') }}</div>
                    <div class="panel-body">
                        <div class="alert alert-info">
                            <p><strong>{{ __('Submission ID') }}:</strong> {{ $submission->id }}</p>
                            <p><strong>{{ __('Application Code') }}:</strong> {{ $visa->unique_code }}</p>
                            @if($submissions->count() > 1)
                                <p><strong>{{ __('Related Submissions') }}:</strong> 
                                    <span class="badge badge-warning">{{ $submissions->count() }}</span>
                                    {{ __('This applicant has multiple visa submissions') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if($submissions->count() > 1)
                    <div class="panel">
                        <div class="panel-body text-center">
                            <a href="{{ route('visa.admin.compare_submissions', $visa->id) }}" class="btn btn-info btn-lg">
                                <i class="fa fa-exchange-alt"></i> {{ __('Compare With Other Submissions') }}
                            </a>
                            <p class="text-muted mt-2 small">{{ __('Compare this submission with others from the same applicant') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Submission Timeline -->
                <div class="panel">
                    <div class="panel-title">{{ __('Submission Timeline') }}</div>
                    <div class="panel-body">
                        <ul class="timeline">
                            @foreach($submissions->sortBy('id') as $key => $sub)
                                <li class="timeline-item {{ $sub->id == $submission->id ? 'active' : '' }}">
                                    <div class="timeline-badge">
                                        <span>{{ $key + 1 }}</span>
                                    </div>
                                    <div class="timeline-panel">
                                        <div class="timeline-heading">
                                            <h6 class="timeline-title">
                                                {{ $sub->visa_name }} - {{ $sub->country_name }}
                                            </h6>
                                            <p>
                                                <small class="text-muted">
                                                    <i class="fa fa-calendar"></i> 
                                                    {{ $sub->scheduled_trip_date ?? 'N/A' }}
                                                </small>
                                            </p>
                                        </div>
                                        <div class="timeline-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge badge-{{ strtolower($sub->payment_status) == 'paid' ? 'success' : (strtolower($sub->payment_status) == 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($sub->payment_status ?? '-') }}
                                                </span>
                                                <a href="{{ route('visa.admin.submission_detail_specific', ['id' => $visa->id, 'submission_id' => $sub->id]) }}" 
                                                   class="btn btn-xs btn-{{ $sub->id == $submission->id ? 'secondary' : 'primary' }}">
                                                    {{ $sub->id == $submission->id ? __('Current') : __('View') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Applicant Image -->
                @if(!empty($submission->persnol_img))
                    <div class="panel">
                        <div class="panel-title">{{ __('Applicant Photo') }}</div>
                        <div class="panel-body text-center">
                            <img src="{{ filter_var($submission->persnol_img, FILTER_VALIDATE_URL) ? $submission->persnol_img : asset('https://visa.riyadhtrips.com/' . $submission->persnol_img) }}" 
                                 alt="{{ $submission->first_name }}" 
                                 class="img-fluid mb-2" 
                                 style="max-height: 200px;">
                        </div>
                    </div>
                @endif

                <!-- Documents -->
                <div class="panel">
                    <div class="panel-title">{{ __('Documents') }}</div>
                    <div class="panel-body">
                        <ul class="list-group">
                            @if(!empty($submission->passport_url))
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ __('Passport') }}
                                    <a href="{{ filter_var($submission->passport_url, FILTER_VALIDATE_URL) ? $submission->passport_url : asset('https://visa.riyadhtrips.com/' . $submission->passport_url) }}" 
                                       target="_blank" class="btn btn-sm btn-info">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </li>
                            @endif

                            @if(!empty($submission->family_card))
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ __('Family Card') }}
                                    <a href="{{ filter_var($submission->family_card, FILTER_VALIDATE_URL) ? $submission->family_card : asset('https://visa.riyadhtrips.com/' . $submission->family_card) }}" 
                                       target="_blank" class="btn btn-sm btn-info">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </li>
                            @endif

                            @if(!empty($submission->salary_id_uploads))
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ __('Salary ID') }}
                                    <a href="{{ filter_var($submission->salary_id_uploads, FILTER_VALIDATE_URL) ? $submission->salary_id_uploads : asset('https://visa.riyadhtrips.com/' . $submission->salary_id_uploads) }}" 
                                       target="_blank" class="btn btn-sm btn-info">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </li>
                            @endif

                            @if(!empty($submission->marital_status_uploads))
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ __('Marital Status Document') }}
                                    <a href="{{ filter_var($submission->marital_status_uploads, FILTER_VALIDATE_URL) ? $submission->marital_status_uploads : asset('https://visa.riyadhtrips.com/' . $submission->marital_status_uploads) }}" 
                                       target="_blank" class="btn btn-sm btn-info">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </li>
                            @endif

                            @if(!empty($submission->account_states_uploads))
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ __('Account Statement') }}
                                    <a href="{{ filter_var($submission->account_states_uploads, FILTER_VALIDATE_URL) ? $submission->account_states_uploads : asset('https://visa.riyadhtrips.com/' . $submission->account_states_uploads) }}" 
                                       target="_blank" class="btn btn-sm btn-info">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </li>
                            @endif

                            @if(!empty($submission->civil_url))
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ __('Civil Document') }}
                                    <a href="{{ filter_var($submission->civil_url, FILTER_VALIDATE_URL) ? $submission->civil_url : asset('https://visa.riyadhtrips.com/' . $submission->civil_url) }}" 
                                       target="_blank" class="btn btn-sm btn-info">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </li>
                            @endif

                            @if(!empty($submission->last_visa_uploads))
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ __('Previous Visa') }}
                                    <a href="{{ filter_var($submission->last_visa_uploads, FILTER_VALIDATE_URL) ? $submission->last_visa_uploads : asset('https://visa.riyadhtrips.com/' . $submission->last_visa_uploads) }}" 
                                       target="_blank" class="btn btn-sm btn-info">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </li>
                            @endif

                            @if(!empty($submission->us_visa_upload))
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ __('US Visa') }}
                                    <a href="{{ filter_var($submission->us_visa_upload, FILTER_VALIDATE_URL) ? $submission->us_visa_upload : asset('https://visa.riyadhtrips.com/' . $submission->us_visa_upload) }}" 
                                       target="_blank" class="btn btn-sm btn-info">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </li>
                            @endif

                            @if(!empty($submission->relation_passport))
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ __('Relation Passport') }}
                                    <a href="{{ filter_var($submission->relation_passport, FILTER_VALIDATE_URL) ? $submission->relation_passport : asset('https://visa.riyadhtrips.com/' . $submission->relation_passport) }}" 
                                       target="_blank" class="btn btn-sm btn-info">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <style>
        .dropdown-item.active {
            background-color: #007bff;
            color: white;
        }
        
        /* Timeline styles */
        .timeline {
            position: relative;
            padding: 20px 0;
            list-style: none;
            margin: 0;
        }

        .timeline:before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #e9ecef;
            left: 13px;
            margin-left: -1.5px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 25px;
            padding-left: 40px;
            min-height: 50px;
        }

        .timeline-badge {
            position: absolute;
            top: 0;
            left: 0;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background-color: #6c757d;
            color: #fff;
            text-align: center;
            line-height: 26px;
            font-size: 12px;
            z-index: 1;
        }

        .timeline-item.active .timeline-badge {
            background-color: #007bff;
        }

        .timeline-panel {
            position: relative;
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 10px 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .timeline-item.active .timeline-panel {
            background-color: #e3f2fd;
            border-left: 3px solid #007bff;
        }

        .timeline-title {
            margin-top: 0;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .timeline-body > p {
            margin-bottom: 0;
        }
    </style>
@endsection