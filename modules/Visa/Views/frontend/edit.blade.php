@extends('layouts.user')

@section('head')
    <link href="{{ asset('dist/frontend/module/user/css/user.css?_ver='.config('app.version')) }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset("dist/frontend/css/daterangepicker.css") }}" >
    <style>
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: 600;
            color: #495057;
        }
        .form-control {
            border-radius: 4px;
        }
        .required {
            color: #dc3545;
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
                                    <h3>{{ __('Edit Visa Application') }}</h3>
                                    <div>
                                        <a href="{{ route('visa.customer.detail', $visa->id) }}" class="btn btn-secondary">
                                            <i class="fa fa-arrow-left"></i> {{ __('Back') }}
                                        </a>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5>{{ __('Application #:code', ['code' => $visa->unique_code]) }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('visa.customer.update', $visa->id) }}" method="POST">
                                            @csrf
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="first_name">{{ __('First Name') }} <span class="required">*</span></label>
                                                        <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name', $visa->first_name) }}" required>
                                                        @error('first_name')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="last_name">{{ __('Last Name') }} <span class="required">*</span></label>
                                                        <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name', $visa->last_name) }}" required>
                                                        @error('last_name')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="email">{{ __('Email') }} <span class="required">*</span></label>
                                                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $visa->email) }}" required>
                                                        @error('email')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="phone">{{ __('Phone') }} <span class="required">*</span></label>
                                                        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $visa->phone) }}" required>
                                                        @error('phone')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="contact_type">{{ __('Contact Type') }} <span class="required">*</span></label>
                                                        <select name="contact_type" id="contact_type" class="form-control" required>
                                                            <option value="">{{ __('Select Contact Type') }}</option>
                                                            <option value="mobile" {{ old('contact_type', $visa->contact_type) == 'mobile' ? 'selected' : '' }}>{{ __('Mobile') }}</option>
                                                            <option value="home" {{ old('contact_type', $visa->contact_type) == 'home' ? 'selected' : '' }}>{{ __('Home') }}</option>
                                                            <option value="work" {{ old('contact_type', $visa->contact_type) == 'work' ? 'selected' : '' }}>{{ __('Work') }}</option>
                                                            <option value="emergency" {{ old('contact_type', $visa->contact_type) == 'emergency' ? 'selected' : '' }}>{{ __('Emergency') }}</option>
                                                        </select>
                                                        @error('contact_type')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="relationship">{{ __('Relationship') }} <span class="required">*</span></label>
                                                        <input type="text" name="relationship" id="relationship" class="form-control" value="{{ old('relationship', $visa->relationship) }}" required>
                                                        @error('relationship')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="scheduled_trip_date">{{ __('Scheduled Trip Date') }} <span class="required">*</span></label>
                                                        <input type="date" name="scheduled_trip_date" id="scheduled_trip_date" class="form-control" value="{{ old('scheduled_trip_date', $visa->scheduled_trip_date ? $visa->scheduled_trip_date->format('Y-m-d') : '') }}" required>
                                                        @error('scheduled_trip_date')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="adults">{{ __('Adults') }} <span class="required">*</span></label>
                                                                <input type="number" name="adults" id="adults" class="form-control" min="1" value="{{ old('adults', $visa->adults) }}" required>
                                                                @error('adults')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="childrens">{{ __('Children') }}</label>
                                                                <input type="number" name="childrens" id="childrens" class="form-control" min="0" value="{{ old('childrens', $visa->childrens) }}">
                                                                @error('childrens')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Read-only fields -->
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>{{ __('Visa Type') }}</label>
                                                        <input type="text" class="form-control" value="{{ $visa->visa_name }}" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>{{ __('Country') }}</label>
                                                        <input type="text" class="form-control" value="{{ $visa->country_name }}" readonly>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>{{ __('Embassy') }}</label>
                                                        <input type="text" class="form-control" value="{{ $visa->embassy_name }}" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>{{ __('Total Price') }}</label>
                                                        <input type="text" class="form-control" value="{{ $visa->formatted_price }}" readonly>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fa fa-save"></i> {{ __('Save Changes') }}
                                                </button>
                                                <a href="{{ route('visa.customer.detail', $visa->id) }}" class="btn btn-default">
                                                    {{ __('Cancel') }}
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
        </div>
    </div>
@endsection

@section('footer')
    <script>
        $(document).ready(function() {
            // Date picker initialization
            if(jQuery('#scheduled_trip_date').length) {
                jQuery('#scheduled_trip_date').daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true,
                    minYear: 2023,
                    maxYear: parseInt(moment().format('YYYY'),10) + 2,
                    locale: {
                        format: 'YYYY-MM-DD'
                    }
                });
            }
        });
    </script>
@endsection
