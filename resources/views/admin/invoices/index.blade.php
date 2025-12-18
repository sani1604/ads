{{-- resources/views/admin/invoices/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Invoices')

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
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value text-danger">{{ $stats['overdue'] ?? 0 }}</div>
                <div class="stat-card-label">Overdue</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">
                    ₹{{ number_format($stats['total_revenue'] ?? 0, 0) }}
                </div>
                <div class="stat-card-label">Total Paid Amount</div>
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
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All</option>
                        <option value="subscription" {{ request('type')=='subscription' ? 'selected' : '' }}>Subscription</option>
                        <option value="wallet_recharge" {{ request('type')=='wallet_recharge' ? 'selected' : '' }}>Wallet</option>
                        <option value="one_time" {{ request('type')=='one_time' ? 'selected' : '' }}>One-time</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach(['draft','sent','paid','overdue','cancelled'] as $s)
                            <option value="{{ $s }}" {{ request('status')==$s ? 'selected' : '' }}>
                                {{ ucfirst($s) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <div class="input-group">
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-fill">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Invoices</h5>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.invoices.generate-recurring') }}" method="POST"
                  onsubmit="return confirm('Generate recurring invoices for due subscriptions?');">
                @csrf
                <button class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-sync me-1"></i>Generate Recurring
                </button>
            </form>
            <a href="{{ route('admin.invoices.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-download me-1"></i>Export CSV
            </a>
            <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Create Invoice
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Client</th>
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
                                <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-decoration-none">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td>
                                <div class="small fw-semibold">
                                    {{ $invoice->user->company_name ?? $invoice->user->name }}
                                </div>
                                <small class="text-muted">{{ $invoice->user->email }}</small>
                            </td>
                            <td class="text-capitalize">{{ $invoice->type }}</td>
                            <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                            <td class="text-end">{{ $invoice->formatted_total }}</td>
                            <td>{!! $invoice->status_badge !!}</td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.invoices.show', $invoice) }}">
                                                <i class="fas fa-eye me-2"></i>View
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.invoices.download', $invoice) }}">
                                                <i class="fas fa-download me-2"></i>Download PDF
                                            </a>
                                        </li>
                                        @if($invoice->status !== 'paid')
                                            <li>
                                                <button class="dropdown-item"
                                                        onclick="markPaid({{ $invoice->id }})">
                                                    <i class="fas fa-check-circle me-2"></i>Mark as Paid
                                                </button>
                                            </li>
                                        @endif
                                        @if($invoice->status !== 'paid')
                                            <li>
                                                <form action="{{ route('admin.invoices.cancel', $invoice) }}" method="POST"
                                                      onsubmit="return confirm('Cancel this invoice?');">
                                                    @csrf
                                                    <button class="dropdown-item text-warning">
                                                        <i class="fas fa-ban me-2"></i>Cancel
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                        @if($invoice->status !== 'paid')
                                            <li>
                                                <form action="{{ route('admin.invoices.destroy', $invoice) }}" method="POST"
                                                      onsubmit="return confirm('Delete this invoice?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="dropdown-item text-danger">
                                                        <i class="fas fa-trash me-2"></i>Delete
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
                            <td colspan="7">
                                <x-empty-state 
                                    icon="fas fa-file-invoice"
                                    title="No invoices"
                                    message="Invoices generated from subscriptions and wallet recharges will appear here."
                                    :actionText="'Create Invoice'"
                                    :actionUrl="route('admin.invoices.create')"
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

    {{-- Hidden Mark Paid form --}}
    <form id="markPaidForm" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="payment_method" value="manual">
        <input type="hidden" name="payment_reference" value="Marked as paid via admin">
    </form>
@endsection

@push('scripts')
<script>
function markPaid(id) {
    if (!confirm('Mark this invoice as paid?')) return;
    const form = document.getElementById('markPaidForm');
    form.action = `/admin/invoices/${id}/mark-paid`;
    form.submit();
}
</script>
@endpush