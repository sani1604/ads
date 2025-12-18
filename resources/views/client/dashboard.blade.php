{{-- resources/views/client/dashboard.blade.php --}}
@extends('layouts.client')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    {{-- Welcome Banner --}}
    @if(!$subscription)
        <div class="alert alert-warning mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <h5 class="mb-1">No Active Subscription</h5>
                    <p class="mb-0">Subscribe to a plan to unlock all features and start generating leads.</p>
                </div>
                <a href="{{ route('client.subscription.plans') }}" class="btn btn-warning ms-auto">
                    <i class="fas fa-rocket me-1"></i> View Plans
                </a>
            </div>
        </div>
    @endif

    {{-- Stats Row --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <x-stat-card 
                title="Wallet Balance"
                :value="$user->formatted_wallet_balance"
                icon="fas fa-wallet"
                color="primary"
                :link="route('client.wallet.index')"
            />
        </div>
        <div class="col-md-6 col-xl-3">
            <x-stat-card 
                title="Leads This Month"
                :value="$stats['this_month']['leads'] ?? 0"
                icon="fas fa-user-plus"
                color="success"
                :change="$stats['changes']['leads'] ?? null"
                :changeType="($stats['changes']['leads'] ?? 0) >= 0 ? 'positive' : 'negative'"
                :link="route('client.leads.index')"
            />
        </div>
        <div class="col-md-6 col-xl-3">
            <x-stat-card 
                title="Impressions"
                :value="number_format($stats['this_month']['impressions'] ?? 0)"
                icon="fas fa-eye"
                color="info"
                :change="$stats['changes']['impressions'] ?? null"
                :changeType="($stats['changes']['impressions'] ?? 0) >= 0 ? 'positive' : 'negative'"
            />
        </div>
        <div class="col-md-6 col-xl-3">
            <x-stat-card 
                title="Ad Spend"
                value="{{ number_format($stats['this_month']['spend'] ?? 0, 0) }}"
                prefix="₹"
                icon="fas fa-chart-pie"
                color="warning"
                :link="route('client.reports.index')"
            />
        </div>
    </div>

    <div class="row g-4">
        {{-- Chart Section --}}
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Performance Overview</h5>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary active" data-days="7">7 Days</button>
                        <button class="btn btn-outline-primary" data-days="30">30 Days</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="300"></canvas>
                </div>
            </div>
        </div>

        {{-- Subscription Info --}}
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Subscription Status</h5>
                </div>
                <div class="card-body">
                    @if($subscription)
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $subscription->package->name }}</h6>
                                <small class="text-muted">{{ $subscription->package->serviceCategory->name }}</small>
                            </div>
                            <x-status-badge :status="$subscription->status" />
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Billing Period</small>
                                <small>{{ $subscription->days_remaining }} days left</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                @php
                                    $totalDays = $subscription->start_date->diffInDays($subscription->end_date);
                                    $progress = $totalDays > 0 ? (($totalDays - $subscription->days_remaining) / $totalDays) * 100 : 100;
                                @endphp
                                <div class="progress-bar" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                        
                        <div class="row text-center g-3">
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <div class="fw-bold">{{ $subscription->getCreativesUsedThisMonth() }}</div>
                                    <small class="text-muted">Creatives Used</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <div class="fw-bold">{{ $subscription->getCreativesRemainingThisMonth() }}</div>
                                    <small class="text-muted">Remaining</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">Next billing: {{ $subscription->next_billing_date->format('M d, Y') }}</small>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No active subscription</p>
                            <a href="{{ route('client.subscription.plans') }}" class="btn btn-primary btn-sm">
                                Choose a Plan
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-0">
        {{-- Recent Leads --}}
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Leads</h5>
                    <a href="{{ route('client.leads.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLeads as $lead)
                                <tr>
                                    <td>
                                        <a href="{{ route('client.leads.show', $lead) }}" class="text-decoration-none">
                                            {{ $lead->name }}
                                        </a>
                                        @if($lead->phone)
                                            <small class="text-muted d-block">{{ $lead->phone }}</small>
                                        @endif
                                    </td>
                                    <td><i class="{{ $lead->source_icon }} me-1"></i> {{ ucfirst($lead->source) }}</td>
                                    <td><x-status-badge :status="$lead->status" /></td>
                                    <td><small>{{ $lead->created_at->diffForHumans() }}</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fas fa-user-plus fa-2x mb-2 d-block"></i>
                                        No leads yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Pending Creatives --}}
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pending Creatives</h5>
                    <a href="{{ route('client.creatives.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    @forelse($pendingCreatives as $creative)
                        <div class="d-flex align-items-center mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            @if($creative->thumbnail_url)
                                <img src="{{ $creative->thumbnail_url }}" alt="" class="rounded me-3" width="60" height="60" style="object-fit: cover;">
                            @else
                                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <a href="{{ route('client.creatives.show', $creative) }}" class="text-decoration-none fw-medium">
                                    {{ $creative->title }}
                                </a>
                                <small class="text-muted d-block">{{ $creative->platform }} • {{ $creative->type_label }}</small>
                            </div>
                            <x-status-badge :status="$creative->status" />
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-paint-brush fa-2x mb-2 d-block"></i>
                            No pending creatives
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Performance Chart
    const ctx = document.getElementById('performanceChart').getContext('2d');
    const chartData = @json($chartData);
    
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Leads',
                    data: chartData.datasets.leads,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Clicks',
                    data: chartData.datasets.clicks,
                    borderColor: '#3b82f6',
                    backgroundColor: 'transparent',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    // Date range buttons
    document.querySelectorAll('[data-days]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('[data-days]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Fetch new data via AJAX
            fetch(`{{ route('client.reports.chart-data') }}?days=${this.dataset.days}`)
                .then(response => response.json())
                .then(data => {
                    chart.data.labels = data.labels;
                    chart.data.datasets[0].data = data.datasets.leads;
                    chart.data.datasets[1].data = data.datasets.clicks;
                    chart.update();
                });
        });
    });
</script>
@endpush