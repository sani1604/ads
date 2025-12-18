{{-- resources/views/admin/clients/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Clients')

@section('content')
    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-card-label">Total Clients</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value text-success">{{ $stats['active'] ?? 0 }}</div>
                <div class="stat-card-label">Active</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">{{ $stats['with_subscription'] ?? 0 }}</div>
                <div class="stat-card-label">With Active Sub.</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">{{ $stats['this_month'] ?? 0 }}</div>
                <div class="stat-card-label">Joined This Month</div>
            </div>
        </div>
    </div>

    {{-- Filters & Actions --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name, email, phone..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Industry</label>
                    <select name="industry" class="form-select">
                        <option value="">All Industries</option>
                        @foreach($industries as $industry)
                            <option value="{{ $industry->id }}" {{ request('industry') == $industry->id ? 'selected' : '' }}>
                                {{ $industry->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Subscription</label>
                    <select name="subscription" class="form-select">
                        <option value="">All</option>
                        <option value="active" {{ request('subscription') == 'active' ? 'selected' : '' }}>Has Active</option>
                        <option value="expired" {{ request('subscription') == 'expired' ? 'selected' : '' }}>No Active</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-fill">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Header + Export + Create --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Clients</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.clients.export') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-download me-1"></i>Export CSV
            </a>
            <a href="{{ route('admin.clients.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-user-plus me-1"></i>Add Client
            </a>
        </div>
    </div>

    {{-- Clients Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Contact</th>
                        <th>Industry</th>
                        <th>Subscription</th>
                        <th>Wallet</th>
                        <th>Joined</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $client->avatar_url }}" class="rounded-circle me-2" width="32" height="32" alt="">
                                    <div>
                                        <a href="{{ route('admin.clients.show', $client) }}" class="fw-semibold text-decoration-none">
                                            {{ $client->company_name ?? $client->name }}
                                        </a>
                                        <div>
                                            <span class="badge {{ $client->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $client->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $client->name }}</div>
                                <small class="text-muted d-block">{{ $client->email }}</small>
                                @if($client->phone)
                                    <small class="text-muted d-block">{{ $client->phone }}</small>
                                @endif
                            </td>
                            <td>{{ $client->industry?->name ?? '-' }}</td>
                            <td>
                                @if($client->activeSubscription)
                                    <small>{{ $client->activeSubscription->package->name }}</small><br>
                                    <small class="text-muted">
                                        Ends {{ $client->activeSubscription->end_date->format('M d, Y') }}
                                    </small>
                                @else
                                    <span class="text-muted small">No active sub.</span>
                                @endif
                            </td>
                            <td>₹{{ number_format($client->wallet_balance, 0) }}</td>
                            <td>
                                <small>{{ $client->created_at->format('M d, Y') }}</small>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.clients.show', $client) }}">
                                                <i class="fas fa-eye me-2"></i>View
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.clients.edit', $client) }}">
                                                <i class="fas fa-edit me-2"></i>Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.clients.login-as', $client) }}">
                                                <i class="fas fa-user-secret me-2"></i>Login as Client
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.clients.toggle-status', $client) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas fa-toggle-on me-2"></i>
                                                    {{ $client->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        </li>
                                        @can('delete', $client)
                                            <li>
                                                <form id="delete-client-{{ $client->id }}" action="{{ route('admin.clients.destroy', $client) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="dropdown-item text-danger"
                                                            onclick="if(confirm('Delete this client?')) document.getElementById('delete-client-{{ $client->id }}').submit();">
                                                        <i class="fas fa-trash me-2"></i>Delete
                                                    </button>
                                                </form>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state 
                                    icon="fas fa-users"
                                    title="No clients found"
                                    message="Start by adding your first client."
                                    :actionText="'Add Client'"
                                    :actionUrl="route('admin.clients.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($clients->hasPages())
            <div class="card-footer">
                <x-pagination :paginator="$clients" />
            </div>
        @endif
    </div>
@endsection