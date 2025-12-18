{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    {{-- Top Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-card-value">{{ $stats['total_clients'] }}</div>
                        <div class="stat-card-label">Total Clients</div>
                    </div>
                    <div class="stat-card-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <a href="{{ route('admin.clients.index') }}" class="small text-primary text-decoration-none">
                    View Clients <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-card-value">{{ $stats['active_subscriptions'] }}</div>
                        <div class="stat-card-label">Active Subs.</div>
                    </div>
                    <div class="stat-card-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
                <a href="{{ route('admin.subscriptions.index') }}" class="small text-success text-decoration-none">
                    View Subscriptions <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-card-value">₹{{ number_format($stats['monthly_revenue'], 0) }}</div>
                        <div class="stat-card-label">Revenue (This Month)</div>
                    </div>
                    <div class="stat-card-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                </div>
                <span class="small text-muted">Total: ₹{{ number_format($stats['total_revenue'], 0) }}</span>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-card-value">{{ $stats['open_tickets'] }}</div>
                        <div class="stat-card-label">Open Tickets</div>
                    </div>
                    <div class="stat-card-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-headset"></i>
                    </div>
                </div>
                <a href="{{ route('admin.support-tickets.index') }}" class="small text-danger text-decoration-none">
                    View Tickets <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Revenue Chart --}}
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Revenue (Last 12 Months)</h6>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="260"></canvas>
                </div>
            </div>
        </div>

        {{-- Quick KPIs --}}
        <div class="col-xl-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Today</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>New Leads</span>
                        <span class="fw-semibold">{{ $stats['new_leads_today'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Pending Creatives</span>
                        <span class="fw-semibold">{{ $stats['pending_creatives'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Active Clients</span>
                        <span class="fw-semibold">{{ $stats['active_clients'] }}</span>
                    </div>
                </div>
            </div>

            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">Expiring Subscriptions (7 days)</h6>
                </div>
                <div class="card-body">
                    @forelse($expiringSubscriptions as $sub)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong>{{ $sub->user->company_name ?? $sub->user->name }}</strong>
                                <small class="d-block text-muted">{{ $sub->package->name }}</small>
                            </div>
                            <span class="badge bg-warning text-dark">
                                {{ $sub->days_remaining }} days left
                            </span>
                        </div>
                        @if(!$loop->last)
                            <hr class="my-2">
                        @endif
                    @empty
                        <p class="text-muted mb-0">No subscriptions expiring soon.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Recent Clients --}}
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Recent Clients</h6>
                    <a href="{{ route('admin.clients.index') }}" class="small text-primary">View all</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($recentClients as $client)
                        <a href="{{ route('admin.clients.show', $client) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex align-items-center">
                                <img src="{{ $client->avatar_url }}" class="rounded-circle me-3" width="32" height="32" alt="">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">
                                        {{ $client->company_name ?? $client->name }}
                                    </div>
                                    <small class="text-muted">{{ $client->email }}</small>
                                </div>
                                <small class="text-muted">{{ $client->created_at->diffForHumans() }}</small>
                            </div>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">No recent clients.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Recent Payments</h6>
                    <a href="{{ route('admin.transactions.index') }}" class="small text-primary">View all</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($recentTransactions as $txn)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $txn->user->company_name ?? $txn->user->name }}</div>
                                <small class="text-muted">{{ $txn->type_label }} • {{ $txn->payment_method_label }}</small>
                            </div>
                            <div class="text-end">
                                <div class="fw-semibold">₹{{ number_format($txn->total_amount, 0) }}</div>
                                <small class="text-muted">{{ $txn->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-muted">No recent payments.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Pending Creatives & Leads --}}
        <div class="col-xl-4">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Pending Creatives</h6>
                    <a href="{{ route('admin.creatives.index', ['status' => 'pending_approval']) }}" class="small text-primary">View all</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($pendingCreatives as $creative)
                        <a href="{{ route('admin.creatives.show', $creative) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="fas fa-paint-brush text-muted"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ Str::limit($creative->title, 30) }}</div>
                                    <small class="text-muted">{{ $creative->user->company_name ?? $creative->user->name }}</small>
                                </div>
                                <small class="text-muted">{{ $creative->created_at->diffForHumans() }}</small>
                            </div>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">No pending creatives.</div>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Recent Leads</h6>
                    <a href="{{ route('admin.leads.index') }}" class="small text-primary">View all</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($recentLeads as $lead)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ Str::limit($lead->name, 20) }}</div>
                                <small class="text-muted">
                                    {{ $lead->user->company_name ?? $lead->user->name }} • {{ ucfirst($lead->source) }}
                                </small>
                            </div>
                            <small class="text-muted">{{ $lead->created_at->diffForHumans() }}</small>
                        </div>
                    @empty
                        <div class="list-group-item text-muted">No recent leads.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueData = @json($revenueChart);

    new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: revenueData.labels,
            datasets: [{
                label: 'Revenue (₹)',
                data: revenueData.data,
                backgroundColor: 'rgba(99, 102, 241, 0.7)',
                borderRadius: 6,
                maxBarThickness: 32
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => '₹' + value
                    },
                    grid: { drawBorder: false }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
</script>
@endpush