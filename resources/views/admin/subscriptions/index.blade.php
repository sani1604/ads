{{-- resources/views/admin/subscriptions/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Subscriptions')

@section('content')
    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-card-label">Total Subscriptions</div>
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
                <div class="stat-card-value text-warning">{{ $stats['expiring_7_days'] ?? 0 }}</div>
                <div class="stat-card-label">Expiring (7 days)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">₹{{ number_format($stats['mrr'] ?? 0, 0) }}</div>
                <div class="stat-card-label">MRR (Approx)</div>
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
                            <option value="{{ $c->id }}" {{ request('client') == $c->id ? 'selected' : '' }}>
                                {{ $c->company_name ?? $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Package</label>
                    <select name="package" class="form-select">
                        <option value="">All</option>
                        @foreach($packages as $p)
                            <option value="{{ $p->id }}" {{ request('package') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach(['pending','active','paused','cancelled','expired'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                                {{ ucfirst($s) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Expiring In</label>
                    <select name="expiring" class="form-select">
                        <option value="">Any</option>
                        <option value="7" {{ request('expiring') == 7 ? 'selected' : '' }}>7 days</option>
                        <option value="14" {{ request('expiring') == 14 ? 'selected' : '' }}>14 days</option>
                        <option value="30" {{ request('expiring') == 30 ? 'selected' : '' }}>30 days</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-fill">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Subscriptions</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.subscriptions.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-download me-1"></i>Export CSV
            </a>
            <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Create Manual Subscription
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Client</th>
                        <th>Package</th>
                        <th>Period</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                        <th>Next Billing</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                        <tr>
                            <td><code>{{ $sub->subscription_code }}</code></td>
                            <td>
                                <div class="small fw-semibold">
                                    {{ $sub->user->company_name ?? $sub->user->name }}
                                </div>
                                <small class="text-muted">{{ $sub->user->email }}</small>
                            </td>
                            <td>
                                {{ $sub->package->name }}
                                <div class="small text-muted">
                                    {{ $sub->package->serviceCategory->name ?? '' }}
                                </div>
                            </td>
                            <td>
                                <small>
                                    {{ $sub->start_date->format('M d, Y') }} – {{ $sub->end_date->format('M d, Y') }}
                                </small>
                            </td>
                            <td class="text-end">{{ $sub->formatted_amount }}</td>
                            <td>{!! $sub->status_badge !!}</td>
                            <td>
                                <small>{{ $sub->next_billing_date->format('M d, Y') }}</small>
                                @if($sub->is_expiring)
                                    <span class="badge bg-warning text-dark ms-1">Soon</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.subscriptions.show', $sub) }}">
                                                <i class="fas fa-eye me-2"></i>View
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.subscriptions.edit', $sub) }}">
                                                <i class="fas fa-edit me-2"></i>Edit
                                            </a>
                                        </li>
                                        @if($sub->canCancel())
                                            <li>
                                                <form action="{{ route('admin.subscriptions.cancel', $sub) }}" method="POST"
                                                      onsubmit="return confirm('Cancel this subscription?');">
                                                    @csrf
                                                    <input type="hidden" name="reason" value="Cancelled by admin panel">
                                                    <input type="hidden" name="immediate" value="1">
                                                    <button class="dropdown-item text-danger">
                                                        <i class="fas fa-ban me-2"></i>Cancel
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state 
                                    icon="fas fa-box"
                                    title="No subscriptions"
                                    message="Manual or checkout-based subscriptions will appear here."
                                    :actionText="'Create Subscription'"
                                    :actionUrl="route('admin.subscriptions.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscriptions->hasPages())
            <div class="card-footer">
                <x-pagination :paginator="$subscriptions" />
            </div>
        @endif
    </div>
@endsection