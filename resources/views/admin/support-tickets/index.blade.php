{{-- resources/views/admin/support-tickets/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Support Tickets')

@section('content')
    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="stat-card">
                <div class="stat-card-value">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-card-label">Total Tickets</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-card">
                <div class="stat-card-value text-primary">{{ $stats['open'] ?? 0 }}</div>
                <div class="stat-card-label">Open</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-card">
                <div class="stat-card-value text-success">{{ $stats['resolved'] ?? 0 }}</div>
                <div class="stat-card-label">Resolved</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-card">
                <div class="stat-card-value text-danger">{{ $stats['urgent'] ?? 0 }}</div>
                <div class="stat-card-label">Urgent</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-card">
                <div class="stat-card-value text-warning">{{ $stats['unassigned'] ?? 0 }}</div>
                <div class="stat-card-label">Unassigned</div>
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
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="open" {{ request('status')=='open' ? 'selected' : '' }}>Open / Active</option>
                        <option value="closed" {{ request('status')=='closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All</option>
                        @foreach(['low','medium','high','urgent'] as $p)
                            <option value="{{ $p }}" {{ request('priority')==$p ? 'selected' : '' }}>
                                {{ ucfirst($p) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Assignee</label>
                    <select name="assignee" class="form-select">
                        <option value="">All</option>
                        <option value="unassigned" {{ request('assignee')=='unassigned' ? 'selected' : '' }}>Unassigned</option>
                        @foreach($staff as $s)
                            <option value="{{ $s->id }}" {{ request('assignee')==$s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Ticket # or subject" value="{{ request('search') }}">
                </div>
            </form>
        </div>
    </div>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Support Tickets</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.support-tickets.statistics') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-chart-line me-1"></i>Stats
            </a>
            <a href="{{ route('admin.support-tickets.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-download me-1"></i>Export CSV
            </a>
            <a href="{{ route('admin.support-tickets.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Create Ticket
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Client</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assignee</th>
                        <th>Updated</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td>
                                <a href="{{ route('admin.support-tickets.show', $ticket) }}" class="fw-semibold text-decoration-none">
                                    {{ $ticket->ticket_number }}
                                </a>
                                <div class="text-muted small">
                                    {{ Str::limit($ticket->subject, 50) }}
                                </div>
                            </td>
                            <td>{{ $ticket->user->company_name ?? $ticket->user->name }}</td>
                            <td>{{ $ticket->category_label }}</td>
                            <td>{!! $ticket->priority_badge !!}</td>
                            <td>{!! $ticket->status_badge !!}</td>
                            <td>{{ $ticket->assignee?->name ?? 'Unassigned' }}</td>
                            <td>
                                <small>{{ $ticket->updated_at->diffForHumans() }}</small>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.support-tickets.show', $ticket) }}">
                                                <i class="fas fa-eye me-2"></i>View
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.support-tickets.assign-self', $ticket) }}" method="POST">
                                                @csrf
                                                <button class="dropdown-item">
                                                    <i class="fas fa-user-check me-2"></i>Assign to Me
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.support-tickets.update-status', $ticket) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="resolved">
                                                <button class="dropdown-item">
                                                    <i class="fas fa-check-circle me-2"></i>Mark Resolved
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.support-tickets.destroy', $ticket) }}" method="POST"
                                                  onsubmit="return confirm('Delete this ticket?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="dropdown-item text-danger">
                                                    <i class="fas fa-trash me-2"></i>Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state 
                                    icon="fas fa-headset"
                                    title="No tickets"
                                    message="Support tickets created by clients or staff will appear here."
                                    :actionText="'Create Ticket'"
                                    :actionUrl="route('admin.support-tickets.create')"
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