{{-- resources/views/client/notifications/index.blade.php --}}
@extends('layouts.client')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">All Notifications</h5>
        <form action="{{ route('client.notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-check-double me-1"></i>Mark All as Read
            </button>
        </form>
    </div>

    <div class="card">
        <div class="list-group list-group-flush">
            @forelse($notifications as $notification)
                <a href="{{ $notification->action_url ?? '#' }}"
                   class="list-group-item list-group-item-action d-flex {{ !$notification->is_read ? 'bg-light' : '' }}">
                    <div class="me-3">
                        <i class="{{ $notification->icon_class }} fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-1">{{ $notification->title }}</h6>
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-0 small text-muted">{{ $notification->message }}</p>
                    </div>
                </a>
            @empty
                <div class="list-group-item">
                    <x-empty-state 
                        icon="fas fa-bell-slash"
                        title="No notifications"
                        message="You don't have any notifications yet."
                    />
                </div>
            @endforelse
        </div>
        @if($notifications->hasPages())
            <div class="card-footer">
                <x-pagination :paginator="$notifications" />
            </div>
        @endif
    </div>
@endsection