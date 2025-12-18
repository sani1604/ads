{{-- resources/views/admin/activity-logs/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Log #' . $activityLog->id)

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Activity Log #{{ $activityLog->id }}</h5>
        </div>
        <div class="card-body">
            <p><strong>Type:</strong> {{ $activityLog->log_type }}</p>
            <p><strong>User:</strong> {{ $activityLog->user?->name ?? 'System' }}</p>
            <p><strong>Description:</strong> {{ $activityLog->description }}</p>
            <p><strong>Created At:</strong> {{ $activityLog->created_at->format('M d, Y h:i A') }}</p>
            @if($activityLog->ip_address)
                <p><strong>IP Address:</strong> {{ $activityLog->ip_address }}</p>
            @endif
            @if($activityLog->user_agent)
                <p><strong>User Agent:</strong> <small>{{ $activityLog->user_agent }}</small></p>
            @endif

            @if($activityLog->properties)
                <hr>
                <h6>Properties (JSON)</h6>
                <pre class="bg-light p-3 small" style="white-space: pre-wrap;">{{ json_encode($activityLog->properties, JSON_PRETTY_PRINT) }}</pre>
            @endif
        </div>
        <div class="card-footer bg-white">
            <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Logs
            </a>
        </div>
    </div>
@endsection