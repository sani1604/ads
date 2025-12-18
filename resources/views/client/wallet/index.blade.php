{{-- resources/views/client/wallet/index.blade.php --}}
@extends('layouts.client')

@section('title', 'Wallet')
@section('page-title', 'Wallet')

@section('content')
    {{-- Wallet Balance Card --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 opacity-75">Available Balance</p>
                            <h2 class="mb-0">{{ $user->formatted_wallet_balance }}</h2>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="fas fa-wallet fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('client.wallet.recharge') }}" class="btn btn-light">
                            <i class="fas fa-plus me-1"></i> Add Money
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick recharge & info --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-3">Quick Recharge</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        @php $quick = [5000,10000,25000,50000]; @endphp
                        @foreach($quick as $amt)
                            <a href="{{ route('client.wallet.recharge', ['amount' => $amt]) }}" class="btn btn-outline-primary">
                                ₹{{ number_format($amt, 0) }}
                            </a>
                        @endforeach
                    </div>
                    <p class="text-muted small mt-3 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Wallet balance is used for ad spend and subscription renewals. You will receive invoices for every recharge.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Wallet Transactions --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Wallet Transactions</h5>
            <span class="text-muted small">Latest {{ $walletTransactions->count() }} records</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Balance After</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($walletTransactions as $txn)
                        <tr>
                            <td>
                                <div>{{ $txn->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $txn->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                {{ $txn->description ?? 'Wallet update' }}
                                @if($txn->transaction)
                                    <small class="text-muted d-block">
                                        Ref: {{ $txn->transaction->transaction_id }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                {!! $txn->type_badge !!}
                            </td>
                            <td class="text-end">
                                <span class="{{ $txn->amount_color }}">
                                    {{ $txn->formatted_amount }}
                                </span>
                            </td>
                            <td class="text-end">
                                ₹{{ number_format($txn->balance_after, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state 
                                    icon="fas fa-wallet"
                                    title="No wallet activity yet"
                                    message="When you recharge your wallet or we deduct ad spend, it will appear here."
                                    :actionText="'Add Money'"
                                    :actionUrl="route('client.wallet.recharge')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($walletTransactions->hasPages())
            <div class="card-footer">
                <x-pagination :paginator="$walletTransactions" />
            </div>
        @endif
    </div>
@endsection