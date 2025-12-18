{{-- resources/views/components/stat-card.blade.php --}}
@props([
    'title',
    'value',
    'icon' => 'fas fa-chart-line',
    'color' => 'primary',
    'change' => null,
    'changeType' => 'positive',
    'link' => null,
    'prefix' => '',
    'suffix' => ''
])

<div class="stat-card">
    <div class="stat-card-header">
        <div>
            <div class="stat-card-value">{{ $prefix }}{{ $value }}{{ $suffix }}</div>
            <div class="stat-card-label">{{ $title }}</div>
            @if($change !== null)
                <div class="stat-card-change {{ $changeType }}">
                    <i class="fas fa-arrow-{{ $changeType === 'positive' ? 'up' : 'down' }} me-1"></i>
                    {{ $change }}% from last month
                </div>
            @endif
        </div>
        <div class="stat-card-icon bg-{{ $color }} bg-opacity-10 text-{{ $color }}">
            <i class="{{ $icon }}"></i>
        </div>
    </div>
    @if($link)
        <a href="{{ $link }}" class="text-{{ $color }} small text-decoration-none">
            View Details <i class="fas fa-arrow-right ms-1"></i>
        </a>
    @endif
</div>