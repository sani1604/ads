{{-- resources/views/admin/clients/show.blade.php --}}
@extends('layouts.admin')

@section('title', $client->company_name ?? $client->name)

@section('content')
    <div class="row g-4 mb-4">
        {{-- Client Overview --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body d-flex align-items-center">
                    <img src="{{ $client->avatar_url }}" class="rounded-circle me-3" width="64" height="64" alt="">
                    <div class="flex-grow-1">
                        <h4 class="mb-0">{{ $client->company_name ?? $client->name }}</h4>
                        <div class="text-muted">
                            {{ $client->name }} • {{ $client->email }}
                        </div>
                        <div class="mt-1">
                            <span class="badge {{ $client->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $client->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($client->activeSubscription)
                                <span class="badge bg-primary ms-1">
                                    {{ $client->activeSubscription->package->name }}
                                </span>
                            @else
                                <span class="badge bg-warning text-dark ms-1">
                                    No Active Subscription
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="mb-2">
                            <span class="text-muted small">Wallet</span>
                            <div class="fw-semibold">
                                ₹{{ number_format($client->wallet_balance, 2) }}
                            </div>
                        </div>
                        <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        <a href="{{ route('admin.clients.login-as', $client) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-user-secret me-1"></i>Login as
                        </a>
                    </div>
                </div>
            </div>

            {{-- Tabs --}}
            <ul class="nav nav-tabs mb-3" id="clientTabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview" type="button">Overview</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#billing" type="button">Billing</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#activity" type="button">Activity</button></li>
            </ul>

            <div class="tab-content">
                {{-- Overview Tab --}}
                <div class="tab-pane fade show active" id="overview">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">Contact Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted">Email</td>
                                            <td class="text-end">{{ $client->email }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Phone</td>
                                            <td class="text-end">{{ $client->phone ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Industry</td>
                                            <td class="text-end">{{ $client->industry?->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Joined</td>
                                            <td class="text-end">{{ $client->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">Address</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">
                                        {{ $client->full_address ?: 'Not provided' }}
                                    </p>
                                    @if($client->gst_number)
                                        <p class="mb-0 mt-2">GST: {{ $client->gst_number }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Stats --}}
                    <div class="row g-3 mt-3">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-card-value">{{ $stats['total_leads'] ?? 0 }}</div>
                                <div class="stat-card-label">Leads</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-card-value">{{ $stats['total_creatives'] ?? 0 }}</div>
                                <div class="stat-card-label">Creatives</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-card-value">₹{{ number_format($stats['total_spent'] ?? 0, 0) }}</div>
                                <div class="stat-card-label">Total Billed</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-card-value">{{ $client->supportTickets()->open()->count() }}</div>
                                <div class="stat-card-label">Open Tickets</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Billing Tab --}}
                <div class="tab-pane fade" id="billing">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6>Subscriptions</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Package</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($client->subscriptions as $sub)
                                            <tr>
                                                <td><code>{{ $sub->subscription_code }}</code></td>
                                                <td>{{ $sub->package->name }}</td>
                                                <td>{!! $sub->status_badge !!}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-muted small">No subscriptions.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Recent Invoices</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Type</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($client->invoices->take(5) as $inv)
                                            <tr>
                                                <td>{{ $inv->invoice_number }}</td>
                                                <td class="text-capitalize">{{ $inv->type }}</td>
                                                <td class="text-end">₹{{ number_format($inv->total_amount, 0) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-muted small">No invoices.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Activity Tab --}}
                <div class="tab-pane fade" id="activity">
                    <div class="card">
                        <div class="list-group list-group-flush">
                            @forelse($activities as $log)
                                <div class="list-group-item d-flex justify-content-between">
                                    <div>
                                        <strong>{{ Str::title(str_replace('_', ' ', $log->log_type)) }}</strong>
                                        <p class="mb-0 small text-muted">{{ $log->description }}</p>
                                    </div>
                                    <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                </div>
                            @empty
                                <div class="list-group-item text-muted">No recent activity.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Wallet Actions --}}
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Wallet Actions</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Current Balance: {{ $client->formatted_wallet_balance }}</p>
                    <form action="{{ route('admin.clients.credit-wallet', $client) }}" method="POST" class="mb-2">
                        @csrf
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="amount" class="form-control" placeholder="Credit amount">
                        </div>
                        <input type="text" name="description" class="form-control form-control-sm mb-2" placeholder="Description" required>
                        <button class="btn btn-success btn-sm w-100">
                            <i class="fas fa-plus me-1"></i>Credit Wallet
                        </button>
                    </form>
                    <form action="{{ route('admin.clients.debit-wallet', $client) }}" method="POST">
                        @csrf
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="amount" class="form-control" placeholder="Debit amount" max="{{ $client->wallet_balance }}">
                        </div>
                        <input type="text" name="description" class="form-control form-control-sm mb-2" placeholder="Description" required>
                        <button class="btn btn-outline-danger btn-sm w-100">
                            <i class="fas fa-minus me-1"></i>Debit Wallet
                        </button>
                    </form>
                </div>
            </div>

            {{-- Links --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Quick Links</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.leads.index', ['client' => $client->id]) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-user-plus me-2"></i>View Leads
                    </a>
                    <a href="{{ route('admin.creatives.index', ['client' => $client->id]) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-paint-brush me-2"></i>View Creatives
                    </a>
                    <a href="{{ route('admin.support-tickets.index', ['client' => $client->id]) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-headset me-2"></i>View Tickets
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection