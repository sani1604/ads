{{-- resources/views/components/status-badge.blade.php --}}
@props([
    'status',
    'type' => 'default'
])

@php
    $badges = [
        // Subscription statuses
        'active' => ['class' => 'bg-success', 'icon' => 'fa-check-circle'],
        'pending' => ['class' => 'bg-warning text-dark', 'icon' => 'fa-clock'],
        'paused' => ['class' => 'bg-info', 'icon' => 'fa-pause-circle'],
        'cancelled' => ['class' => 'bg-danger', 'icon' => 'fa-times-circle'],
        'expired' => ['class' => 'bg-secondary', 'icon' => 'fa-calendar-times'],
        
        // Lead statuses
        'new' => ['class' => 'bg-primary', 'icon' => 'fa-star'],
        'contacted' => ['class' => 'bg-info', 'icon' => 'fa-phone'],
        'qualified' => ['class' => 'bg-success', 'icon' => 'fa-thumbs-up'],
        'converted' => ['class' => 'bg-success', 'icon' => 'fa-check-double'],
        'lost' => ['class' => 'bg-danger', 'icon' => 'fa-times'],
        'spam' => ['class' => 'bg-secondary', 'icon' => 'fa-ban'],
        
        // Creative statuses
        'draft' => ['class' => 'bg-secondary', 'icon' => 'fa-file'],
        'pending_approval' => ['class' => 'bg-warning text-dark', 'icon' => 'fa-hourglass-half'],
        'changes_requested' => ['class' => 'bg-info', 'icon' => 'fa-edit'],
        'approved' => ['class' => 'bg-success', 'icon' => 'fa-check'],
        'rejected' => ['class' => 'bg-danger', 'icon' => 'fa-times'],
        'published' => ['class' => 'bg-primary', 'icon' => 'fa-globe'],
        
        // Invoice/Transaction statuses
        'paid' => ['class' => 'bg-success', 'icon' => 'fa-check-circle'],
        'sent' => ['class' => 'bg-info', 'icon' => 'fa-paper-plane'],
        'overdue' => ['class' => 'bg-danger', 'icon' => 'fa-exclamation-circle'],
        'completed' => ['class' => 'bg-success', 'icon' => 'fa-check-circle'],
        'failed' => ['class' => 'bg-danger', 'icon' => 'fa-times-circle'],
        'processing' => ['class' => 'bg-info', 'icon' => 'fa-spinner fa-spin'],
        'refunded' => ['class' => 'bg-secondary', 'icon' => 'fa-undo'],
        
        // Ticket statuses
        'open' => ['class' => 'bg-primary', 'icon' => 'fa-envelope-open'],
        'in_progress' => ['class' => 'bg-info', 'icon' => 'fa-tasks'],
        'waiting_reply' => ['class' => 'bg-warning text-dark', 'icon' => 'fa-reply'],
        'resolved' => ['class' => 'bg-success', 'icon' => 'fa-check-circle'],
        'closed' => ['class' => 'bg-secondary', 'icon' => 'fa-lock'],
        
        // Priority
        'urgent' => ['class' => 'bg-danger', 'icon' => 'fa-exclamation'],
        'high' => ['class' => 'bg-warning text-dark', 'icon' => 'fa-arrow-up'],
        'medium' => ['class' => 'bg-info', 'icon' => 'fa-minus'],
        'low' => ['class' => 'bg-secondary', 'icon' => 'fa-arrow-down'],
        
        // Lead quality
        'hot' => ['class' => 'bg-danger', 'icon' => 'fa-fire'],
        'warm' => ['class' => 'bg-warning text-dark', 'icon' => 'fa-temperature-half'],
        'cold' => ['class' => 'bg-info', 'icon' => 'fa-snowflake'],
    ];
    
    $badge = $badges[$status] ?? ['class' => 'bg-secondary', 'icon' => 'fa-question'];
    $label = ucwords(str_replace('_', ' ', $status));
@endphp

<span class="badge {{ $badge['class'] }}">
    <i class="fas {{ $badge['icon'] }} me-1"></i>{{ $label }}
</span>