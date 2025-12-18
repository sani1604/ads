{{-- resources/views/client/reports/index.blade.php --}}
@extends('layouts.client')

@section('title', 'Reports')
@section('page-title', 'Reports & Analytics')

@section('content')
    {{-- Summary Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">
                    {{ number_format($stats['total_impressions'] ?? 0) }}
                </div>
                <div class="stat-card-label">Impressions</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">
                    {{ number_format($stats['total_clicks'] ?? 0) }}
                </div>
                <div class="stat-card-label">Clicks</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">
                    {{ number_format($stats['total_leads'] ?? 0) }}
                </div>
                <div class="stat-card-label">Leads</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">
                    ₹{{ number_format($stats['total_spend'] ?? 0, 2) }}
                </div>
                <div class="stat-card-label">Total Spend</div>
            </div>
        </div>
    </div>

    {{-- Filters + Export --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">From</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">To</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-primary flex-fill">
                        <i class="fas fa-filter me-1"></i>Apply
                    </button>
                    <a href="{{ route('client.reports.export', request()->query()) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-download me-1"></i>Export CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Charts --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Performance Over Time</h6>
                </div>
                <div class="card-body">
                    <canvas id="reportChart" height="280"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Platform Breakdown</h6>
                </div>
                <div class="card-body">
                    @forelse($platformBreakdown as $platform)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong class="text-capitalize">{{ $platform['platform'] }}</strong>
                                <small class="d-block text-muted">
                                    Leads: {{ $platform['leads'] }} • Spend: ₹{{ number_format($platform['spend'], 0) }}
                                </small>
                            </div>
                            <span class="badge bg-light text-dark">
                                CPL: {{ $platform['cpl'] > 0 ? '₹'.number_format($platform['cpl'], 2) : 'N/A' }}
                            </span>
                        </div>
                        @if(!$loop->last)
                            <hr class="my-2">
                        @endif
                    @empty
                        <p class="text-muted mb-0">No data for selected period.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Daily Reports Table --}}
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Daily Campaign Performance</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Platform</th>
                        <th>Campaign</th>
                        <th class="text-end">Impr.</th>
                        <th class="text-end">Clicks</th>
                        <th class="text-end">Leads</th>
                        <th class="text-end">Spend</th>
                        <th class="text-end">CPL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailyReports as $report)
                        <tr>
                            <td>{{ $report->report_date->format('M d, Y') }}</td>
                            <td class="text-capitalize">{{ $report->platform }}</td>
                            <td>{{ $report->campaign_name ?? '-' }}</td>
                            <td class="text-end">{{ number_format($report->impressions) }}</td>
                            <td class="text-end">{{ number_format($report->clicks) }}</td>
                            <td class="text-end">{{ number_format($report->leads) }}</td>
                            <td class="text-end">₹{{ number_format($report->spend, 2) }}</td>
                            <td class="text-end">
                                {{ $report->cpl > 0 ? '₹'.number_format($report->cpl, 2) : 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state 
                                    icon="fas fa-chart-line"
                                    title="No data"
                                    message="Your campaign performance data will appear here once campaigns are running."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($dailyReports->hasPages())
            <div class="card-footer">
                <x-pagination :paginator="$dailyReports" />
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    const ctx = document.getElementById('reportChart').getContext('2d');
    const chartData = @json($chartData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Leads',
                    data: chartData.datasets.leads,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Spend',
                    data: chartData.datasets.spend,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: false,
                    tension: 0.3,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Leads' }
                },
                y1: {
                    position: 'right',
                    beginAtZero: true,
                    grid: { drawOnChartArea: false },
                    title: { display: true, text: 'Spend (₹)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
</script>
@endpush