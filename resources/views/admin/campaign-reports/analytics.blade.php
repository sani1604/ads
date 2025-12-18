{{-- resources/views/admin/campaign-reports/analytics.blade.php --}}
@extends('layouts.admin')

@section('title', 'Campaign Analytics')

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Campaign Performance Analytics</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Client</label>
                    <select name="client" class="form-select select2">
                        <option value="">All</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ request('client')==$c->id ? 'selected' : '' }}>
                                {{ $c->company_name ?? $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date', now()->subDays(30)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>Apply
                    </button>
                </div>
            </form>

            <div class="row g-4">
                <div class="col-md-8">
                    <canvas id="analyticsChart" height="280"></canvas>
                </div>
                <div class="col-md-4">
                    <h6>Summary</h6>
                    <ul class="list-unstyled small mb-0">
                        <li>Total Impressions: {{ number_format($summary->total_impressions ?? 0) }}</li>
                        <li>Total Clicks: {{ number_format($summary->total_clicks ?? 0) }}</li>
                        <li>Total Leads: {{ number_format($summary->total_leads ?? 0) }}</li>
                        <li>Total Spend: ₹{{ number_format($summary->total_spend ?? 0, 2) }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const analyticsData = @json($dailyData ?? []);
    const labels = Object.keys(analyticsData);
    const impressions = labels.map(d => analyticsData[d].impressions ?? 0);
    const leads = labels.map(d => analyticsData[d].leads ?? 0);

    const ctx = document.getElementById('analyticsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Impressions',
                    data: impressions,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Leads',
                    data: leads,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34,197,94,0.1)',
                    fill: false,
                    tension: 0.3,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Impressions' } },
                y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, title: { display: true, text: 'Leads' } }
            }
        }
    });
</script>
@endpush