<div class="bravo-user-dashboard-visa-widget">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title d-flex justify-content-between align-items-center">
                <span>{{ __('Visa Applications') }}</span>
                <a href="{{ route('visa.customer.history') }}" class="btn btn-sm btn-primary">{{ __('View All') }}</a>
            </h5>
        </div>
        <div class="card-body">
            <!-- Summary Stats -->
            <div class="row mb-3">
                <div class="col-md-3 col-6">
                    <div class="stat-box bg-light p-2 text-center">
                        <div class="stat-value">{{ $summary['total'] }}</div>
                        <div class="stat-label small">{{ __('Total') }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-box bg-warning text-white p-2 text-center">
                        <div class="stat-value">{{ $summary['pending'] }}</div>
                        <div class="stat-label small">{{ __('Pending') }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-box bg-info text-white p-2 text-center">
                        <div class="stat-value">{{ $summary['processing'] }}</div>
                        <div class="stat-label small">{{ __('Processing') }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-box bg-success text-white p-2 text-center">
                        <div class="stat-value">{{ $summary['approved'] }}</div>
                        <div class="stat-label small">{{ __('Approved') }}</div>
                    </div>
                </div>
            </div>

            <!-- Recent Applications -->
            @if($recentVisas->count() > 0)
                <div class="recent-visas">
                    <h6 class="mb-2">{{ __('Recent Applications') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Visa') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentVisas as $visa)
                                    <tr>
                                        <td>{{ $visa->unique_code }}</td>
                                        <td>
                                            <div class="visa-info">
                                                <strong>{{ $visa->visa_name }}</strong><br>
                                                <small class="text-muted">{{ $visa->country_name }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $visa->status_class }} badge-sm">
                                                {{ $visa->status_name }}
                                            </span>
                                        </td>
                                        <td>{{ $visa->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('visa.customer.detail', $visa->id) }}" class="btn btn-xs btn-outline-primary">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="text-center text-muted py-3">
                    <p>{{ __('No visa applications yet') }}</p>
                    <a href="#" class="btn btn-outline-primary btn-sm">{{ __('Apply for Visa') }}</a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .bravo-user-dashboard-visa-widget .stat-box {
        border-radius: 4px;
        margin-bottom: 10px;
    }
    .bravo-user-dashboard-visa-widget .stat-value {
        font-size: 20px;
        font-weight: bold;
    }
    .bravo-user-dashboard-visa-widget .stat-label {
        font-size: 11px;
        text-transform: uppercase;
    }
    .bravo-user-dashboard-visa-widget .visa-info {
        line-height: 1.2;
    }
    .bravo-user-dashboard-visa-widget .badge-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    .bravo-user-dashboard-visa-widget .table th,
    .bravo-user-dashboard-visa-widget .table td {
        padding: 0.5rem;
        vertical-align: middle;
    }
</style>
