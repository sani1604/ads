{{-- resources/views/admin/campaign-reports/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Campaign Reports')

@section('content')
    {{-- Aggregate Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">{{ number_format($aggregateStats->total_impressions ?? 0) }}</div>
                <div class="stat-card-label">Impressions</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">{{ number_format($aggregateStats->total_clicks ?? 0) }}</div>
                <div class="stat-card-label">Clicks</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">{{ number_format($aggregateStats->total_leads ?? 0) }}</div>
                <div class="stat-card-label">Leads</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">
                    ₹{{ number_format($aggregateStats->total_spend ?? 0, 0) }}
                </div>
                <div class="stat-card-label">Total Spend</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
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
                <div class="col-md-2">
                    <label class="form-label">Platform</label>
                    <select name="platform" class="form-select">
                        <option value="">All</option>
                        @foreach(['facebook','instagram','google','linkedin'] as $p)
                            <option value="{{ $p }}" {{ request('platform')==$p ? 'selected' : '' }}>
                                {{ ucfirst($p) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <div class="input-group">
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Campaign name..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-fill">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.campaign-reports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Campaign Reports</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.campaign-reports.analytics', request()->query()) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-chart-line me-1"></i>Analytics
            </a>
            <a href="{{ route('admin.campaign-reports.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-download me-1"></i>Export CSV
            </a>
            <a href="{{ route('admin.campaign-reports.import-form') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-file-import me-1"></i>Import
            </a>
            <a href="{{ route('admin.campaign-reports.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Add Report
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Platform</th>
                        <th>Campaign</th>
                        <th class="text-end">Impr.</th>
                        <th class="text-end">Clicks</th>
                        <th class="text-end">Leads</th>
                        <th class="text-end">Spend</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailyReports as $report)
                        <tr>
                            <td>{{ $report->report_date->format('M d, Y') }}</td>
                            <td>{{ $report->user->company_name ?? $report->user->name }}</td>
                            <td class="text-capitalize">{{ $report->platform }}</td>
                            <td>{{ $report->campaign_name ?? '-' }}</td>
                            <td class="text-end">{{ number_format($report->impressions) }}</td>
                            <td class="text-end">{{ number_format($report->clicks) }}</td>
                            <td class="text-end">{{ number_format($report->leads) }}</td>
                            <td class="text-end">₹{{ number_format($report->spend, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state 
                                    icon="fas fa-chart-bar"
                                    title="No reports"
                                    message="Manual or imported campaign reports will appear here."
                                    :actionText="'Add Report'"
                                    :actionUrl="route('admin.campaign-reports.create')"
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