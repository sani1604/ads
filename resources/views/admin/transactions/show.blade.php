{{-- resources/views/admin/transactions/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Transaction ' . $transaction->transaction_id)

@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Transaction #{{ $transaction->transaction_id }}</h5>
                <small class="text-muted">
                    {{ $transaction->created_at->format('M d, Y h:i A') }} • {{ $transaction->type_label }}
                </small>
            </div>
            <div>
                {!! $transaction->status_badge !!}
            </div>
        </div>
        <div class="card-body">
            {{-- Client & basic info --}}
            <div class="row g-4 mb-3">
                <div class="col-md-6">
                    <h6 class="text-muted">Client</h6>
                    <p class="mb-0 fw-semibold">
                        {{ $transaction->user->company_name ?? $transaction->user->name }}
                    </p>
                    <p class="mb-0 text-muted">{{ $transaction->user->email }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="text-muted">Amount</h6>
                    <p class="mb-0 fw-semibold">{{ $transaction->formatted_amount }}</p>
                    <p class="mb-0 text-muted">
                        Method: {{ $transaction->payment_method_label }}
                    </p>
                </div>
            </div>

            <hr>

            {{-- Details --}}
            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="text-muted">Details</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Currency</td>
                            <td class="text-end">{{ $transaction->currency }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Base Amount</td>
                            <td class="text-end">₹{{ number_format($transaction->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tax</td>
                            <td class="text-end">₹{{ number_format($transaction->tax_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total</td>
                            <td class="text-end">₹{{ number_format($transaction->total_amount, 2) }}</td>
                        </tr>
                        @if($transaction->paid_at)
                            <tr>
                                <td class="text-muted">Paid At</td>
                                <td class="text-end">{{ $transaction->paid_at->format('M d, Y h:i A') }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">Gateway Info</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Razorpay Order ID</td>
                            <td class="text-end">{{ $transaction->razorpay_order_id ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Razorpay Payment ID</td>
                            <td class="text-end">{{ $transaction->razorpay_payment_id ?? '-' }}</td>
                        </tr>
                    </table>
                    @if($transaction->description)
                        <p class="mt-2 mb-0"><strong>Description:</strong> {{ $transaction->description }}</p>
                    @endif
                </div>
            </div>

            {{-- Link to invoice/subscription if exists --}}
            <hr>
            <div class="row">
                <div class="col-md-6">
                    @if($transaction->subscription)
                        <p class="mb-0">
                            <strong>Subscription:</strong>
                            <a href="{{ route('admin.subscriptions.show', $transaction->subscription) }}">
                                {{ $transaction->subscription->subscription_code }}
                            </a>
                        </p>
                    @endif
                </div>
                <div class="col-md-6 text-md-end">
                    @if($transaction->invoice)
                        <p class="mb-0">
                            <strong>Invoice:</strong>
                            <a href="{{ route('admin.invoices.show', $transaction->invoice) }}">
                                {{ $transaction->invoice->invoice_number }}
                            </a>
                        </p>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-footer bg-white">
            <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Transactions
            </a>
        </div>
    </div>
@endsection