@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('Visa Statistics') }}</h1>
            <div class="title-actions">
                <a href="{{ route('visa.admin.index') }}" class="btn btn-primary">
                    <i class="fa fa-list"></i> {{ __('All Applications') }}
                </a>
            </div>
        </div>
        
        @include('admin.message')

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h3 class="display-4">{{ $stats['total'] }}</h3>
                        <p class="text-muted">{{ __('Total Applications') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3 class="display-4">{{ $stats['pending'] }}</h3>
                        <p class="text-white">{{ __('Pending') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3 class="display-4">{{ $stats['processing'] }}</h3>
                        <p class="text-white">{{ __('Processing') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3 class="display-4">{{ $stats['approved'] }}</h3>
                        <p class="text-white">{{ __('Approved') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h3 class="display-4">{{ $stats['rejected'] }}</h3>
                        <p class="text-white">{{ __('Rejected') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-dark text-white">
                    <div class="card-body text-center">
                        <h3 class="display-4">{{ $stats['cancelled'] }}</h3>
                        <p class="text-white">{{ __('Cancelled') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3 class="display-4">{{ number_format($stats['total_revenue'], 2) }} SR</h3>
                        <p class="text-white">{{ __('Total Revenue') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-secondary text-white">
                    <div class="card-body text-center">
                        <h3 class="display-4">{{ number_format($stats['pending_payment'], 2) }} SR</h3>
                        <p class="text-white">{{ __('Pending Payment') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Applications -->
        <div class="panel">
            <div class="panel-title">{{ __('Recent Visa Applications') }}</div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('#Code') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Visa Details') }}</th>
                                <th>{{ __('Price') }}</th>
                                <th>{{ __('Payment Status') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentApplications as $visa)
                                <tr>
                                    <td>#{{ $visa->unique_code }}</td>
                                    <td>
                                        <strong>{{ $visa->first_name }} {{ $visa->last_name }}</strong><br>
                                        <small>{{ $visa->email }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $visa->visa_name }}</strong><br>
                                        <small>{{ $visa->country_name }}</small>
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
                                        <a href="{{ route('visa.admin.detail', $visa->id) }}" class="btn btn-xs btn-primary">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('visa.admin.edit', $visa->id) }}" class="btn btn-xs btn-info">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Charts (can be added later) -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="panel">
                    <div class="panel-title">{{ __('Applications by Status') }}</div>
                    <div class="panel-body">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel">
                    <div class="panel-title">{{ __('Monthly Revenue') }}</div>
                    <div class="panel-body">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Status Chart
        var statusCtx = document.getElementById('statusChart').getContext('2d');
        var statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Processing', 'Approved', 'Rejected', 'Cancelled', 'Completed'],
                datasets: [{
                    data: [
                        {{ $stats['pending'] }}, 
                        {{ $stats['processing'] }}, 
                        {{ $stats['approved'] }}, 
                        {{ $stats['rejected'] }}, 
                        {{ $stats['cancelled'] }}, 
                        {{ $stats['completed'] }}
                    ],
                    backgroundColor: [
                        '#ffc107',
                        '#17a2b8',
                        '#28a745',
                        '#dc3545',
                        '#343a40',
                        '#007bff'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom'
                }
            }
        });

        // Revenue Chart
        var revenueCtx = document.getElementById('revenueChart').getContext('2d');
        var revenueChart = new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue (SR)',
                    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0], // Placeholder data - you can load actual monthly data
                    backgroundColor: '#007bff',
                    borderColor: '#0056b3',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endsection

@section('style')
    <style>
        .display-4 {
            font-size: 2rem;
            font-weight: 300;
        }
        .card {
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        #statusChart, #revenueChart {
            height: 300px !important;
        }
    </style>
@endsection
