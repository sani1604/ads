{{-- resources/views/client/support/index.blade.php --}}
@extends('layouts.client')

@section('title', 'Support Tickets')
@section('page-title', 'Support Tickets')

@section('content')
    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-4 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-card-label">Total Tickets</div>
            </div>
        </div>
        <div class="col-4 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value text-primary">{{ $stats['open'] ?? 0 }}</div>
                <div class="stat-card-label">Open</div>
            </div>
        </div>
        <div class="col-4 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value text-success">{{ $stats['resolved'] ?? 0 }}</div>
                <div class="stat-card-label">Resolved</div>
            </div>
        </div>
    </div>

    {{-- Header + New Ticket --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Your Tickets</h5>
        <a href="{{ route('client.support.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> New Ticket
        </a>
    </div>

    {{-- Tickets Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Last Update</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td>
                                <a href="{{ route('client.support.show', $ticket) }}" class="fw-medium text-decoration-none">
                                    {{ $ticket->ticket_number }}
                                </a>
                                <small class="d-block text-muted">
                                    {{ Str::limit($ticket->subject, 60) }}
                                </small>
                            </td>
                            <td><span class="badge bg-secondary">{{ $ticket->category_label }}</span></td>
                            <td>{!! $ticket->priority_badge !!}</td>
                            <td>{!! $ticket->status_badge !!}</td>
                            <td>
                                <small>{{ $ticket->updated_at->diffForHumans() }}</small>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('client.support.show', $ticket) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state 
                                    icon="fas fa-headset"
                                    title="No support tickets yet"
                                    message="If you need any help related to billing, creatives, or leads, create a ticket and our team will assist you."
                                    :actionText="'Create Ticket'"
                                    :actionUrl="route('client.support.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
            <div class="card-footer">
                <x-pagination :paginator="$tickets" />
            </div>
        @endif
    </div>
@endsection