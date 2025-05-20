@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('Compare Visa Submissions') }}</h1>
            <div class="title-actions">
                <a href="{{ route('visa.admin.detail', $visa->id) }}" class="btn btn-primary">
                    <i class="fa fa-arrow-left"></i> {{ __('Back to Application') }}
                </a>
            </div>
        </div>
        
        @include('admin.message')

        <!-- Submission Selection Section -->
        <div class="panel">
            <div class="panel-title">{{ __('Select Submissions to Compare') }}</div>
            <div class="panel-body">
                <form method="GET" action="{{ route('visa.admin.compare_submissions', $visa->id) }}" class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>{{ __('First Submission') }}</label>
                            <select name="submission1" class="form-control" required>
                                <option value="">{{ __('-- Select Submission --') }}</option>
                                @foreach($submissions as $sub)
                                    <option value="{{ $sub->id }}" {{ request('submission1') == $sub->id ? 'selected' : '' }}>
                                        #{{ $sub->id }} - {{ $sub->visa_name }} ({{ $sub->country_name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>{{ __('Second Submission') }}</label>
                            <select name="submission2" class="form-control" required>
                                <option value="">{{ __('-- Select Submission --') }}</option>
                                @foreach($submissions as $sub)
                                    <option value="{{ $sub->id }}" {{ request('submission2') == $sub->id ? 'selected' : '' }}>
                                        #{{ $sub->id }} - {{ $sub->visa_name }} ({{ $sub->country_name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary form-control">
                                <i class="fa fa-exchange-alt"></i> {{ __('Compare') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(isset($submission1) && isset($submission2))
            <!-- Comparison Results -->
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-title">
                            {{ __('Comparison Results') }}
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-bordered comparison-table">
                                    <thead>
                                        <tr>
                                            <th width="20%">{{ __('Field') }}</th>
                                            <th width="40%">
                                                {{ __('Submission') }} #{{ $submission1->id }}
                                                <span class="badge badge-info">{{ $submission1->visa_name }}</span>
                                            </th>
                                            <th width="40%">
                                                {{ __('Submission') }} #{{ $submission2->id }}
                                                <span class="badge badge-info">{{ $submission2->visa_name }}</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Basic Information -->
                                        <tr class="table-info">
                                            <td colspan="3">
                                                <strong>{{ __('Basic Information') }}</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Visa Type') }}</td>
                                            <td>{{ $submission1->visa_name }}</td>
                                            <td class="{{ $submission1->visa_name !== $submission2->visa_name ? 'diff-highlight' : '' }}">
                                                {{ $submission2->visa_name }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Country') }}</td>
                                            <td>{{ $submission1->country_name }}</td>
                                            <td class="{{ $submission1->country_name !== $submission2->country_name ? 'diff-highlight' : '' }}">
                                                {{ $submission2->country_name }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Embassy') }}</td>
                                            <td>{{ $submission1->embassy_name }}</td>
                                            <td class="{{ $submission1->embassy_name !== $submission2->embassy_name ? 'diff-highlight' : '' }}">
                                                {{ $submission2->embassy_name }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('First Name') }}</td>
                                            <td>{{ $submission1->first_name }}</td>
                                            <td class="{{ $submission1->first_name !== $submission2->first_name ? 'diff-highlight' : '' }}">
                                                {{ $submission2->first_name }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Last Name') }}</td>
                                            <td>{{ $submission1->last_name }}</td>
                                            <td class="{{ $submission1->last_name !== $submission2->last_name ? 'diff-highlight' : '' }}">
                                                {{ $submission2->last_name }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Nationality') }}</td>
                                            <td>{{ $submission1->nationality ?? '-' }}</td>
                                            <td class="{{ ($submission1->nationality ?? '') !== ($submission2->nationality ?? '') ? 'diff-highlight' : '' }}">
                                                {{ $submission2->nationality ?? '-' }}
                                            </td>
                                        </tr>
                                        
                                        <!-- Travel Details -->
                                        <tr class="table-info">
                                            <td colspan="3">
                                                <strong>{{ __('Travel Information') }}</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Trip Date') }}</td>
                                            <td>{{ $submission1->scheduled_trip_date ?? '-' }}</td>
                                            <td class="{{ ($submission1->scheduled_trip_date ?? '') !== ($submission2->scheduled_trip_date ?? '') ? 'diff-highlight' : '' }}">
                                                {{ $submission2->scheduled_trip_date ?? '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Adults') }}</td>
                                            <td>{{ $submission1->adults ?? '0' }}</td>
                                            <td class="{{ ($submission1->adults ?? '') !== ($submission2->adults ?? '') ? 'diff-highlight' : '' }}">
                                                {{ $submission2->adults ?? '0' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Children') }}</td>
                                            <td>{{ $submission1->childrens ?? '0' }}</td>
                                            <td class="{{ ($submission1->childrens ?? '') !== ($submission2->childrens ?? '') ? 'diff-highlight' : '' }}">
                                                {{ $submission2->childrens ?? '0' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Arrival Date') }}</td>
                                            <td>{{ $submission1->arrival_date ?? '-' }}</td>
                                            <td class="{{ ($submission1->arrival_date ?? '') !== ($submission2->arrival_date ?? '') ? 'diff-highlight' : '' }}">
                                                {{ $submission2->arrival_date ?? '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Stay Length') }}</td>
                                            <td>{{ $submission1->stay_length ?? '-' }}</td>
                                            <td class="{{ ($submission1->stay_length ?? '') !== ($submission2->stay_length ?? '') ? 'diff-highlight' : '' }}">
                                                {{ $submission2->stay_length ?? '-' }}
                                            </td>
                                        </tr>
                                        
                                        <!-- Previous Visa Info -->
                                        <tr class="table-info">
                                            <td colspan="3">
                                                <strong>{{ __('Previous Visa Information') }}</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Previous Visa') }}</td>
                                            <td>{{ $submission1->last_visa ?? '-' }}</td>
                                            <td class="{{ ($submission1->last_visa ?? '') !== ($submission2->last_visa ?? '') ? 'diff-highlight' : '' }}">
                                                {{ $submission2->last_visa ?? '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Schengen Visa') }}</td>
                                            <td>{{ $submission1->schengen_visa ?? '-' }}</td>
                                            <td class="{{ ($submission1->schengen_visa ?? '') !== ($submission2->schengen_visa ?? '') ? 'diff-highlight' : '' }}">
                                                {{ $submission2->schengen_visa ?? '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('US Visa Before') }}</td>
                                            <td>{{ $submission1->usvisa_before ?? '-' }}</td>
                                            <td class="{{ ($submission1->usvisa_before ?? '') !== ($submission2->usvisa_before ?? '') ? 'diff-highlight' : '' }}">
                                                {{ $submission2->usvisa_before ?? '-' }}
                                            </td>
                                        </tr>
                                        
                                        <!-- Payment Information -->
                                        <tr class="table-info">
                                            <td colspan="3">
                                                <strong>{{ __('Payment Information') }}</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Payment Status') }}</td>
                                            <td>
                                                <span class="badge badge-{{ strtolower($submission1->payment_status ?? '') == 'paid' ? 'success' : (strtolower($submission1->payment_status ?? '') == 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($submission1->payment_status ?? '-') }}
                                                </span>
                                            </td>
                                            <td class="{{ ($submission1->payment_status ?? '') !== ($submission2->payment_status ?? '') ? 'diff-highlight' : '' }}">
                                                <span class="badge badge-{{ strtolower($submission2->payment_status ?? '') == 'paid' ? 'success' : (strtolower($submission2->payment_status ?? '') == 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($submission2->payment_status ?? '-') }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Payment Method') }}</td>
                                            <td>{{ $submission1->payment_method ?? '-' }}</td>
                                            <td class="{{ ($submission1->payment_method ?? '') !== ($submission2->payment_method ?? '') ? 'diff-highlight' : '' }}">
                                                {{ $submission2->payment_method ?? '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Visa Price') }}</td>
                                            <td>{{ number_format($submission1->visa_price ?? 0, 2) }} SR</td>
                                            <td class="{{ ($submission1->visa_price ?? '') !== ($submission2->visa_price ?? '') ? 'diff-highlight' : '' }}">
                                                {{ number_format($submission2->visa_price ?? 0, 2) }} SR
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Transaction ID') }}</td>
                                            <td>{{ $submission1->transactionid ?? '-' }}</td>
                                            <td class="{{ ($submission1->transactionid ?? '') !== ($submission2->transactionid ?? '') ? 'diff-highlight' : '' }}">
                                                {{ $submission2->transactionid ?? '-' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Document Comparison -->
            <div class="panel">
                <div class="panel-title">{{ __('Document Comparison') }}</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ __('Submission') }} #{{ $submission1->id }} {{ __('Documents') }}</h5>
                            <ul class="list-group">
                                @if(!empty($submission1->passport_url))
                                    <li class="list-group-item">
                                        <a href="{{ filter_var($submission1->passport_url, FILTER_VALIDATE_URL) ? $submission1->passport_url : asset('https://visa.riyadhtrips.com/' . $submission1->passport_url) }}" 
                                           target="_blank" class="btn btn-sm btn-info mr-2">
                                            <i class="fa fa-download"></i>
                                        </a>
                                        {{ __('Passport') }}
                                    </li>
                                @endif
                                @if(!empty($submission1->persnol_img))
                                    <li class="list-group-item">
                                        <a href="{{ filter_var($submission1->persnol_img, FILTER_VALIDATE_URL) ? $submission1->persnol_img : asset('https://visa.riyadhtrips.com/' . $submission1->persnol_img) }}" 
                                           target="_blank" class="btn btn-sm btn-info mr-2">
                                            <i class="fa fa-download"></i>
                                        </a>
                                        {{ __('Personal Photo') }}
                                    </li>
                                @endif
                                @if(!empty($submission1->family_card))
                                    <li class="list-group-item">
                                        <a href="{{ filter_var($submission1->family_card, FILTER_VALIDATE_URL) ? $submission1->family_card : asset('https://visa.riyadhtrips.com/' . $submission1->family_card) }}" 
                                           target="_blank" class="btn btn-sm btn-info mr-2">
                                            <i class="fa fa-download"></i>
                                        </a>
                                        {{ __('Family Card') }}
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>{{ __('Submission') }} #{{ $submission2->id }} {{ __('Documents') }}</h5>
                            <ul class="list-group">
                                @if(!empty($submission2->passport_url))
                                    <li class="list-group-item">
                                        <a href="{{ filter_var($submission2->passport_url, FILTER_VALIDATE_URL) ? $submission2->passport_url : asset('https://visa.riyadhtrips.com/' . $submission2->passport_url) }}" 
                                           target="_blank" class="btn btn-sm btn-info mr-2">
                                            <i class="fa fa-download"></i>
                                        </a>
                                        {{ __('Passport') }}
                                    </li>
                                @endif
                                @if(!empty($submission2->persnol_img))
                                    <li class="list-group-item">
                                        <a href="{{ filter_var($submission2->persnol_img, FILTER_VALIDATE_URL) ? $submission2->persnol_img : asset('https://visa.riyadhtrips.com/' . $submission2->persnol_img) }}" 
                                           target="_blank" class="btn btn-sm btn-info mr-2">
                                            <i class="fa fa-download"></i>
                                        </a>
                                        {{ __('Personal Photo') }}
                                    </li>
                                @endif
                                @if(!empty($submission2->family_card))
                                    <li class="list-group-item">
                                        <a href="{{ filter_var($submission2->family_card, FILTER_VALIDATE_URL) ? $submission2->family_card : asset('https://visa.riyadhtrips.com/' . $submission2->family_card) }}" 
                                           target="_blank" class="btn btn-sm btn-info mr-2">
                                            <i class="fa fa-download"></i>
                                        </a>
                                        {{ __('Family Card') }}
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('footer')
    <style>
        .comparison-table tr td:first-child {
            font-weight: bold;
        }
        
        .diff-highlight {
            background-color: #fff3cd;
            font-weight: bold;
        }
        
        .table-info {
            background-color: #e7f5ff;
        }
        
        .table-info td {
            font-size: 16px;
        }
    </style>
@endsection