{{-- resources/views/components/empty-state.blade.php --}}
@props([
    'icon' => 'fas fa-inbox',
    'title' => 'No data found',
    'message' => 'There are no items to display at the moment.',
    'actionText' => null,
    'actionUrl' => null
])

<div class="text-center py-5">
    <div class="mb-4">
        <i class="{{ $icon }} fa-4x text-muted opacity-50"></i>
    </div>
    <h5 class="text-muted">{{ $title }}</h5>
    <p class="text-muted mb-4">{{ $message }}</p>
    @if($actionText && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> {{ $actionText }}
        </a>
    @endif
</div>