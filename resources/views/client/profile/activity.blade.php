{{-- resources/views/client/profile/activity.blade.php --}}
@extends('layouts.client')

@section('title', 'Activity Log')
@section('page-title', 'Activity Log')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Activity</h5>
            <span class="text-muted small">Last {{ $activities->count() }} records</span>
        </div>
        <div class="list-group list-group-flush">
            @forelse($activities as $log)
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>{{ Str::title(str_replace('_', ' ', $log->log_type)) }}</strong>
                            <p class="mb-0 small text-muted">
                                {{ $log->description }}
                            </p>
                        </div>
                        <small class="text-muted">
                            {{ $log->created_at->diffForHumans() }}
                        </small>
                    </div>
                </div>
            @empty
                <div class="list-group-item">
                    <x-empty-state 
                        icon="fas fa-history"
                        title="No activity recorded"
                        message="Your account activity will appear here."
                    />
                </div>
            @endforelse
        </div>
        @if($activities->hasPages())
            <div class="card-footer">
                <x-pagination :paginator="$activities" />
            </div>
        @endif
    </div>
@endsection