{{-- resources/views/admin/subscriptions/show.blade.php --}}
@extends('layouts.admin')

@section('title', $subscription->subscription_code)

@section('content')
    {{-- Overview --}}
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-1">{{ $subscription->package->name }}</h5>
                <p class="mb-1 text-muted">
                    {{ $subscription->user->company_name ?? $subscription->user->name }} • 
                    {{ $subscription->package->serviceCategory->name ?? '' }}
                </p>
                <p class="mb-0">
                    Period: {{ $subscription->start_date->format('M d, Y') }} – {{ $subscription->end_date->format('M d, Y') }}
                </p>
            </div>
            <div class="text-end">
                {!! $subscription->status_badge !!}
                <div class="mt-2">
                    <div class="fw-semibold mb-1">{{ $subscription->formatted_amount }}</div>
                    <small class="text-muted">
                        Next Billing: {{ $subscription->next_billing_date->format('M d, Y') }}
                    </small>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    @if($subscription->canCancel())
                        <form action="{{ route('admin.subscriptions.cancel', $subscription) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Cancel this subscription?');">
                            @csrf
                            <input type="hidden" name="reason" value="Cancelled via admin show page">
                            <input type="hidden" name="immediate" value="1">
                            <button class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-ban me-1"></i>Cancel
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Usage & Billing --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-card-value">{{ $usageStats['creatives_used'] }}</div>
                <div class="stat-card-label">Creatives This Month</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-card-value">{{ $usageStats['creatives_remaining'] }}</div>
                <div class="stat-card-label">Creatives Remaining</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-card-value">{{ $usageStats['leads_this_month'] }}</div>
                <div class="stat-card-label">Leads This Month</div>
            </div>
        </div>
    </div>

    {{-- Tabs: Invoices / Transactions / Creatives / Leads --}}
    <ul class="nav nav-tabs mb-3" id="subTabs">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sub-invoices">Invoices</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sub-transactions">Transactions</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sub-creatives">Creatives</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sub-leads">Leads</button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- Invoices --}}
        <div class="tab-pane fade show active" id="sub-invoices">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscription->invoices as $invoice)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                                    </td>
                                    <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                    <td class="text-end">₹{{ number_format($invoice->total_amount, 0) }}</td>
                                    <td>{!! $invoice->status_badge !!}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted small">No invoices.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Transactions --}}
        <div class="tab-pane fade" id="sub-transactions">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Txn ID</th>
                                <th>Type</th>
                                <th>Method</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscription->transactions as $txn)
                                <tr>
                                    <td>{{ $txn->transaction_id }}</td>
                                    <td>{{ $txn->type_label }}</td>
                                    <td>{{ $txn->payment_method_label }}</td>
                                    <td class="text-end">₹{{ number_format($txn->total_amount, 0) }}</td>
                                    <td>{!! $txn->status_badge !!}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted small">No transactions.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Creatives --}}
        <div class="tab-pane fade" id="sub-creatives">
            <div class="card">
                <div class="list-group list-group-flush">
                    @forelse($subscription->creatives as $c)
                        <a href="{{ route('admin.creatives.show', $c) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">{{ $c->title }}</div>
                                    <small class="text-muted">{{ $c->platform }} • {{ $c->type_label }}</small>
                                </div>
                                {!! $c->status_badge !!}
                            </div>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">No creatives.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Leads --}}
        <div class="tab-pane fade" id="sub-leads">
            <div class="card">
                <div class="list-group list-group-flush">
                    @forelse($subscription->leads as $l)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $l->name }}</div>
                                <small class="text-muted">{{ $l->source }} • {{ $l->created_at->format('M d, Y') }}</small>
                            </div>
                            {!! $l->status_badge !!}
                        </div>
                    @empty
                        <div class="list-group-item text-muted">No leads.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary mt-3">
        <i class="fas fa-arrow-left me-1"></i>Back to Subscriptions
    </a>
@endsection