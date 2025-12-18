{{-- resources/views/client/transactions/index.blade.php --}}
@extends('layouts.client')

@section('title', 'Transactions')
@section('page-title', 'Transactions')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Payment Transactions</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Type</th>
                        <th>Method</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                        <tr>
                            <td><code>{{ $txn->transaction_id }}</code></td>
                            <td>{{ $txn->type_label }}</td>
                            <td>{{ $txn->payment_method_label }}</td>
                            <td class="text-end">{{ $txn->formatted_amount }}</td>
                            <td>{!! $txn->status_badge !!}</td>
                            <td>{{ $txn->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state 
                                    icon="fas fa-credit-card"
                                    title="No transactions yet"
                                    message="Your subscription and wallet recharge transactions will appear here."
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
@endsection