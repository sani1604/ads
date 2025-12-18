{{-- resources/views/components/loading.blade.php --}}
@props([
    'size' => 'md',
    'text' => 'Loading...'
])

@php
    $sizeClass = match($size) {
        'sm' => 'spinner-border-sm',
        'lg' => '',
        default => ''
    };
@endphp

<div class="d-flex align-items-center justify-content-center py-4">
    <div class="spinner-border text-primary {{ $sizeClass }}" role="status">
        <span class="visually-hidden">{{ $text }}</span>
    </div>
    @if($text)
        <span class="ms-2 text-muted">{{ $text }}</span>
    @endif
</div>