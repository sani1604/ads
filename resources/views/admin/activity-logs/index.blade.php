{{-- resources/views/admin/activity-logs/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Activity Logs')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">User</label>
                    <select name="user" class="form-select select2">
                        <option value="">All</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user')==$u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->role }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All</option>
                        @foreach($logTypes as $t)
                            <option value="{{ $t }}" {{ request('type')==$t ? 'selected' : '' }}>
                                {{ $t }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date Range</label>
                    <div class="input-group">
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-fill">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Activity Logs</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.activity-logs.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-download me-1"></i>Export CSV
            </a>
            @can('delete-records', auth()->user())
            <form action="{{ route('admin.activity-logs.clear-old') }}" method="POST"
                  onsubmit="return confirm('Delete logs older than 90 days?');">
                @csrf
                <input type="hidden" name="days" value="90">
                <button class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-trash me-1"></i>Clear Old (90d+)
                </button>
            </form>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="list-group list-group-flush">
            @forelse($logs as $log)
                <a href="{{ route('admin.activity-logs.show', $log) }}" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fw-semibold">{{ $log->log_type }}</div>
                            <div class="small text-muted">{{ $log->description }}</div>
                            @if($log->ip_address)
                                <div class="small text-muted">IP: {{ $log->ip_address }}</div>
                            @endif
                        </div>
                        <div class="text-end">
                            <div class="small">
                                {{ $log->user?->name ?? 'System' }}
                            </div>
                            <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </a>
            @empty
                <div class="list-group-item">
                    <x-empty-state 
                        icon="fas fa-history"
                        title="No activity"
                        message="No activity logs found for selected filters."
                    />
                </div>
            @endforelse
        </div>
        @if($logs->hasPages())
            <div class="card-footer">
                <x-pagination :paginator="$logs" />
            </div>
        @endif
    </div>
@endsection