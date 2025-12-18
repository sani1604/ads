{{-- resources/views/client/invoices/index.blade.php --}}
@extends('layouts.client')

@section('title', 'Invoices')
@section('page-title', 'Invoices')

@section('content')
    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-card-label">Total Invoices</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value text-success">{{ $stats['paid'] ?? 0 }}</div>
                <div class="stat-card-label">Paid</div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="stat-card">
                <div class="stat-card-value">
                    ₹{{ number_format($stats['total_amount'] ?? 0, 2) }}
                </div>
                <div class="stat-card-label">Total Paid Amount</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All</option>
                        <option value="subscription" {{ request('type') == 'subscription' ? 'selected' : '' }}>Subscription</option>
                        <option value="wallet_recharge" {{ request('type') == 'wallet_recharge' ? 'selected' : '' }}>Wallet Recharge</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date Range</label>
                    <div class="input-group">
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i>Apply
                    </button>
                    <a href="{{ route('client.invoices.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Invoices Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>
                                <a href="{{ route('client.invoices.show', $invoice) }}" class="text-decoration-none">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td class="text-capitalize">{{ str_replace('_', ' ', $invoice->type) }}</td>
                            <td>
                                {{ $invoice->invoice_date->format('M d, Y') }}
                                @if($invoice->is_overdue && $invoice->status !== 'paid')
                                    <small class="badge bg-danger ms-1">Overdue</small>
                                @endif
                            </td>
                            <td class="text-end">
                                {{ $invoice->formatted_total }}
                            </td>
                            <td>
                                {!! $invoice->status_badge !!}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('client.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('client.invoices.download', $invoice) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-download"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state 
                                    icon="fas fa-file-invoice"
                                    title="No invoices yet"
                                    message="Your subscription and wallet recharge invoices will appear here."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
            <div class="card-footer">
                <x-pagination :paginator="$invoices" />
            </div>
        @endif
    </div>
@endsection