{{-- resources/views/admin/transactions/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Transactions')

@section('content')
    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">{{ $stats['total_transactions'] ?? 0 }}</div>
                <div class="stat-card-label">Total Transactions</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value text-success">{{ $stats['completed'] ?? 0 }}</div>
                <div class="stat-card-label">Completed</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value text-warning">{{ $stats['pending'] ?? 0 }}</div>
                <div class="stat-card-label">Pending</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card-value">
                    ₹{{ number_format($stats['total_revenue'] ?? 0, 0) }}
                </div>
                <div class="stat-card-label">Total Amount (Completed)</div>
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
                        @foreach(['subscription','wallet_recharge','ad_spend','refund','adjustment'] as $t)
                            <option value="{{ $t }}" {{ request('type')==$t ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_',' ',$t)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Method</label>
                    <select name="payment_method" class="form-select">
                        <option value="">All</option>
                        @foreach(['razorpay','stripe','bank_transfer','cash','wallet','manual'] as $m)
                            <option value="{{ $m }}" {{ request('payment_method')==$m ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_',' ',$m)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach(['pending','processing','completed','failed','refunded'] as $s)
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
            </form>
        </div>
    </div>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Transactions</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.transactions.analytics', ['period' => '30days']) }}" 
               class="btn btn-outline-secondary btn-sm"
               id="showAnalytics">
                <i class="fas fa-chart-line me-1"></i>Analytics
            </a>
            <a href="{{ route('admin.transactions.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-download me-1"></i>Export CSV
            </a>
            <a href="{{ route('admin.transactions.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Add Manual Transaction
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Txn ID</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Method</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                        <tr>
                            <td>
                                <a href="{{ route('admin.transactions.show', $txn) }}" class="text-decoration-none">
                                    {{ $txn->transaction_id }}
                                </a>
                            </td>
                            <td>
                                <div class="small fw-semibold">
                                    {{ $txn->user->company_name ?? $txn->user->name }}
                                </div>
                                <small class="text-muted">{{ $txn->user->email }}</small>
                            </td>
                            <td>{{ $txn->type_label }}</td>
                            <td>{{ $txn->payment_method_label }}</td>
                            <td class="text-end">{{ $txn->formatted_amount }}</td>
                            <td>{!! $txn->status_badge !!}</td>
                            <td>{{ $txn->created_at->format('M d, Y h:i A') }}</td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.transactions.show', $txn) }}">
                                                <i class="fas fa-eye me-2"></i>View
                                            </a>
                                        </li>
                                        @if($txn->status === 'pending')
                                            <li>
                                                <form action="{{ route('admin.transactions.update-status', $txn) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="completed">
                                                    <button class="dropdown-item">
                                                        <i class="fas fa-check-circle me-2"></i>Mark Completed
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                        @if($txn->status === 'completed' && $txn->type !== 'refund')
                                            <li>
                                                <button class="dropdown-item text-warning" onclick="showRefundPrompt('{{ route('admin.transactions.refund', $txn) }}', {{ $txn->total_amount }})">
                                                    <i class="fas fa-undo me-2"></i>Refund
                                                </button>
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
                                    icon="fas fa-credit-card"
                                    title="No transactions"
                                    message="Transactions will be created automatically for payments or manually by staff."
                                    :actionText="'Add Manual Transaction'"
                                    :actionUrl="route('admin.transactions.create')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="card-footer">
                <x-pagination :paginator="$transactions" />
            </div>
        @endif
    </div>

    {{-- Refund Modal --}}
    <div class="modal fade" id="refundModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="refundForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Process Refund</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">
                            Max refundable: <span id="refundMax"></span>
                        </p>
                        <x-form.input 
                            name="amount"
                            label="Refund Amount (₹)"
                            type="number"
                            :required="true"
                        />
                        <x-form.textarea 
                            name="reason"
                            label="Reason"
                            rows="3"
                            :required="true"
                        />
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger">Process Refund</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function showRefundPrompt(action, maxAmount) {
    const form = document.getElementById('refundForm');
    form.action = action;
    document.getElementById('refundMax').textContent = '₹' + maxAmount.toFixed(2);
    new bootstrap.Modal(document.getElementById('refundModal')).show();
}
</script>
@endpush