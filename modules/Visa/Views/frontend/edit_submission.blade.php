@extends('layouts.user')

@section('head')
    <link href="{{ asset('dist/frontend/module/user/css/user.css?_ver='.config('app.version')) }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset("dist/frontend/css/daterangepicker.css") }}" >
    <style>
        .visa-edit-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: 600;
            color: #495057;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        .form-group.required label:after {
            content: " *";
            color: red;
        }
        .help-block {
            color: #6c757d;
            font-size: 13px;
            margin-top: 5px;
        }
        .form-section-title {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #495057;
        }
        
        /* Submission tabs styling */
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
                                        <h3>{{ __('Edit Visa Submission') }}</h3>
                                        <p class="text-muted mb-0">{{ __('Application Code') }}: #{{ $visa->unique_code }}</p>
                                    </div>
                                    <div>
                                        <a href="{{ route('visa.customer.submission', [$visa->id, $submission->id]) }}" class="btn btn-secondary">
                                            <i class="fa fa-arrow-left"></i> {{ __('Back to Submission') }}
                                        </a>
                                    </div>
                                </div>
                                <!-- Edit Form -->
                                <form action="{{ route('visa.customer.update_submission', [$visa->id, $submission->id]) }}" method="post">
                                    @csrf
                                    
                                    <!-- Personal Information -->
                                    <div class="visa-edit-section">
                                        <h4 class="form-section-title">{{ __('Personal Information') }}</h4>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group required">
                                                    <label>{{ __('First Name') }}</label>
                                                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $submission->first_name) }}" required>
                                                    @if($errors->has('first_name'))
                                                        <span class="invalid-feedback">{{ $errors->first('first_name') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group required">
                                                    <label>{{ __('Last Name') }}</label>
                                                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $submission->last_name) }}" required>
                                                    @if($errors->has('last_name'))
                                                        <span class="invalid-feedback">{{ $errors->first('last_name') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>{{ __('Nationality') }}</label>
                                                    <input type="text" name="nationality" class="form-control" value="{{ old('nationality', $submission->nationality) }}">
                                                    @if($errors->has('nationality'))
                                                        <span class="invalid-feedback">{{ $errors->first('nationality') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>{{ __('Marital Status') }}</label>
                                                    <select name="marital_status" class="form-control">
                                                        <option value="">{{ __('-- Select Status --') }}</option>
                                                        <option value="Single" {{ old('marital_status', $submission->marital_status) == 'Single' ? 'selected' : '' }}>{{ __('Single') }}</option>
                                                        <option value="Married" {{ old('marital_status', $submission->marital_status) == 'Married' ? 'selected' : '' }}>{{ __('Married') }}</option>
                                                        <option value="Divorced" {{ old('marital_status', $submission->marital_status) == 'Divorced' ? 'selected' : '' }}>{{ __('Divorced') }}</option>
                                                        <option value="Widowed" {{ old('marital_status', $submission->marital_status) == 'Widowed' ? 'selected' : '' }}>{{ __('Widowed') }}</option>
                                                    </select>
                                                    @if($errors->has('marital_status'))
                                                        <span class="invalid-feedback">{{ $errors->first('marital_status') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>{{ __('Mother\'s First Name') }}</label>
                                                    <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name', $submission->mother_name) }}">
                                                    @if($errors->has('mother_name'))
                                                        <span class="invalid-feedback">{{ $errors->first('mother_name') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>{{ __('Mother\'s Last Name') }}</label>
                                                    <input type="text" name="mother_last" class="form-control" value="{{ old('mother_last', $submission->mother_last) }}">
                                                    @if($errors->has('mother_last'))
                                                        <span class="invalid-feedback">{{ $errors->first('mother_last') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>{{ __('Relationship') }}</label>
                                                    <input type="text" name="relationship" class="form-control" value="{{ old('relationship', $submission->relationship) }}">
                                                    <span class="help-block">{{ __('Your relationship to the application') }}</span>
                                                    @if($errors->has('relationship'))
                                                        <span class="invalid-feedback">{{ $errors->first('relationship') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Travel Information -->
                                    <div class="visa-edit-section">
                                        <h4 class="form-section-title">{{ __('Travel Information') }}</h4>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group required">
                                                    <label>{{ __('Scheduled Trip Date') }}</label>
                                                    <input type="text" name="scheduled_trip_date" class="form-control datepicker" value="{{ old('scheduled_trip_date', $submission->scheduled_trip_date) }}" required>
                                                    @if($errors->has('scheduled_trip_date'))
                                                        <span class="invalid-feedback">{{ $errors->first('scheduled_trip_date') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>{{ __('Arrival Date') }}</label>
                                                    <input type="text" name="arrival_date" class="form-control datepicker" value="{{ old('arrival_date', $submission->arrival_date) }}">
                                                    @if($errors->has('arrival_date'))
                                                        <span class="invalid-feedback">{{ $errors->first('arrival_date') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>{{ __('Stay Length') }}</label>
                                                    <input type="text" name="stay_length" class="form-control" value="{{ old('stay_length', $submission->stay_length) }}">
                                                    <span class="help-block">{{ __('How long do you plan to stay?') }}</span>
                                                    @if($errors->has('stay_length'))
                                                        <span class="invalid-feedback">{{ $errors->first('stay_length') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>{{ __('Preferred Choice') }}</label>
                                                    <input type="text" name="preferred_choice" class="form-control" value="{{ old('preferred_choice', $submission->preferred_choice) }}">
                                                    @if($errors->has('preferred_choice'))
                                                        <span class="invalid-feedback">{{ $errors->first('preferred_choice') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group required">
                                                    <label>{{ __('Number of Adults') }}</label>
                                                    <input type="number" name="adults" class="form-control" value="{{ old('adults', $submission->adults) }}" min="1" required>
                                                    @if($errors->has('adults'))
                                                        <span class="invalid-feedback">{{ $errors->first('adults') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>{{ __('Number of Children') }}</label>
                                                    <input type="number" name="childrens" class="form-control" value="{{ old('childrens', $submission->childrens) }}" min="0">
                                                    @if($errors->has('childrens'))
                                                        <span class="invalid-feedback">{{ $errors->first('childrens') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Visa Details Note -->
                                    <div class="visa-edit-section">
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle"></i> {{ __('Some visa details like visa type, country, and embassy cannot be edited. If you need to change these details, please cancel this application and submit a new one, or contact with support team.') }}
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>{{ __('Visa Type') }}</label>
                                                    <input type="text" class="form-control" value="{{ $submission->visa_name }}" readonly disabled>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>{{ __('Country') }}</label>
                                                    <input type="text" class="form-control" value="{{ $submission->country_name }}" readonly disabled>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>{{ __('Embassy') }}</label>
                                                    <input type="text" class="form-control" value="{{ $submission->embassy_name }}" readonly disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Form Actions -->
                                    <div class="text-center mt-4 mb-5">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fa fa-save"></i> {{ __('Save Changes') }}
                                        </button>
                                        <a href="{{ route('visa.customer.submission', [$visa->id, $submission->id]) }}" class="btn btn-secondary btn-lg ml-2">
                                            <i class="fa fa-times"></i> {{ __('Cancel') }}
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <script src="{{ asset('libs/daterange/moment.min.js') }}"></script>
    <script src="{{ asset('libs/daterange/daterangepicker.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.datepicker').daterangepicker({
                singleDatePicker: true,
                showCalendar: true,
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD'
                }
            }).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
            });
            
            // Set initial values for datepickers if they have values
            $('.datepicker').each(function() {
                if ($(this).val()) {
                    $(this).data('daterangepicker').setStartDate($(this).val());
                    $(this).data('daterangepicker').setEndDate($(this).val());
                }
            });
        });
    </script>
@endsection