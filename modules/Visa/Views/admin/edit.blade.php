@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('Edit Visa Application') }}</h1>
            <div class="title-actions">
                <a href="{{ route('visa.admin.index') }}" class="btn btn-primary">
                    <i class="fa fa-arrow-left"></i> {{ __('Back to List') }}
                </a>
            </div>
        </div>
        
        @include('admin.message')

        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-title">{{ __('Application #:code', ['code' => $visa->unique_code]) }}</div>
                    <div class="panel-body">
                        <form action="{{ route('visa.admin.update', $visa->id) }}" method="POST">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="first_name">{{ __('First Name') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name', $visa->first_name) }}" required>
                                        @error('first_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="last_name">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name', $visa->last_name) }}" required>
                                        @error('last_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="user_id">{{ __('Customer') }} <span class="text-danger">*</span></label>
                                        <select name="user_id" id="user_id" class="form-control" required>
                                            <option value="">{{ __('Select Customer') }}</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ old('user_id', $visa->user_id) == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} ({{ $user->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('user_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">{{ __('Email') }} <span class="text-danger">*</span></label>
                                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $visa->email) }}" required>
                                        @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">{{ __('Phone') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $visa->phone) }}" required>
                                        @error('phone')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact_type">{{ __('Contact Type') }} <span class="text-danger">*</span></label>
                                        <select name="contact_type" id="contact_type" class="form-control" required>
                                            <option value="">{{ __('Select Contact Type') }}</option>
                                            <option value="mobile" {{ old('contact_type', $visa->contact_type) == 'mobile' ? 'selected' : '' }}>{{ __('Mobile') }}</option>
                                            <option value="home" {{ old('contact_type', $visa->contact_type) == 'home' ? 'selected' : '' }}>{{ __('Home') }}</option>
                                            <option value="work" {{ old('contact_type', $visa->contact_type) == 'work' ? 'selected' : '' }}>{{ __('Work') }}</option>
                                            <option value="emergency" {{ old('contact_type', $visa->contact_type) == 'emergency' ? 'selected' : '' }}>{{ __('Emergency') }}</option>
                                        </select>
                                        @error('contact_type')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="visa_name">{{ __('Visa Type') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="visa_name" id="visa_name" class="form-control" value="{{ old('visa_name', $visa->visa_name) }}" required>
                                        @error('visa_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="country_name">{{ __('Country') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="country_name" id="country_name" class="form-control" value="{{ old('country_name', $visa->country_name) }}" required>
                                        @error('country_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="embassy_name">{{ __('Embassy') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="embassy_name" id="embassy_name" class="form-control" value="{{ old('embassy_name', $visa->embassy_name) }}" required>
                                        @error('embassy_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="scheduled_trip_date">{{ __('Scheduled Trip Date') }} <span class="text-danger">*</span></label>
                                        <input type="date" name="scheduled_trip_date" id="scheduled_trip_date" class="form-control" value="{{ old('scheduled_trip_date', $visa->scheduled_trip_date ? $visa->scheduled_trip_date->format('Y-m-d') : '') }}" required>
                                        @error('scheduled_trip_date')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="adults">{{ __('Adults') }} <span class="text-danger">*</span></label>
                                        <input type="number" name="adults" id="adults" class="form-control" min="1" value="{{ old('adults', $visa->adults) }}" required>
                                        @error('adults')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="childrens">{{ __('Children') }}</label>
                                        <input type="number" name="childrens" id="childrens" class="form-control" min="0" value="{{ old('childrens', $visa->childrens) }}">
                                        @error('childrens')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="total_price">{{ __('Total Price') }} <span class="text-danger">*</span></label>
                                        <input type="number" name="total_price" id="total_price" class="form-control" step="0.01" min="0" value="{{ old('total_price', $visa->total_price) }}" required>
                                        @error('total_price')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="payment_status">{{ __('Payment Status') }} <span class="text-danger">*</span></label>
                                        <select name="payment_status" id="payment_status" class="form-control" required>
                                            <option value="">{{ __('Select Payment Status') }}</option>
                                            <option value="paid" {{ old('payment_status', $visa->payment_status) == 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                                            <option value="pending" {{ old('payment_status', $visa->payment_status) == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                            <option value="failed" {{ old('payment_status', $visa->payment_status) == 'failed' ? 'selected' : '' }}>{{ __('Failed') }}</option>
                                        </select>
                                        @error('payment_status')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="payment_method">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                                        <select name="payment_method" id="payment_method" class="form-control" required>
                                            <option value="">{{ __('Select Payment Method') }}</option>
                                            <option value="credit_card" {{ old('payment_method', $visa->payment_method) == 'credit_card' ? 'selected' : '' }}>{{ __('Credit Card') }}</option>
                                            <option value="bank_transfer" {{ old('payment_method', $visa->payment_method) == 'bank_transfer' ? 'selected' : '' }}>{{ __('Bank Transfer') }}</option>
                                            <option value="cash" {{ old('payment_method', $visa->payment_method) == 'cash' ? 'selected' : '' }}>{{ __('Cash') }}</option>
                                            <option value="other" {{ old('payment_method', $visa->payment_method) == 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                                        </select>
                                        @error('payment_method')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="status">{{ __('Application Status') }} <span class="text-danger">*</span></label>
                                        <select name="status" id="status" class="form-control" required>
                                            <option value="">{{ __('Select Status') }}</option>
                                            <option value="0" {{ old('status', $visa->status) == '0' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                            <option value="1" {{ old('status', $visa->status) == '1' ? 'selected' : '' }}>{{ __('Processing') }}</option>
                                            <option value="2" {{ old('status', $visa->status) == '2' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                                            <option value="3" {{ old('status', $visa->status) == '3' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                                            <option value="4" {{ old('status', $visa->status) == '4' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                                            <option value="5" {{ old('status', $visa->status) == '5' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                        </select>
                                        @error('status')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="relationship">{{ __('Relationship') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="relationship" id="relationship" class="form-control" value="{{ old('relationship', $visa->relationship) }}" required>
                                        @error('relationship')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="notify_customer" name="notify_customer" value="1">
                                            <label class="custom-control-label" for="notify_customer">{{ __('Notify customer via email') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="response_message">{{ __('Response/Notes to Customer') }}</label>
                                <textarea name="response_message" id="response_message" class="form-control" rows="4">{{ old('response_message', $visa->appointment) }}</textarea>
                                @error('response_message')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="{{ route('visa.admin.index') }}" class="btn btn-default mr-2">{{ __('Cancel') }}</a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-save"></i> {{ __('Save Changes') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <script>
        $(document).ready(function() {
            // Auto-populate user data when user is selected
            $('#user_id').change(function() {
                var userId = $(this).val();
                if (userId) {
                    // You can add AJAX call here to fetch user details and auto-populate fields
                }
            });
        });
    </script>
@endsection
