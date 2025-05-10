@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('Visa Application Details') }}</h1>
            <div class="title-actions">
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
