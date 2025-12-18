{{-- resources/views/admin/transactions/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Add Manual Transaction')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Add Manual Transaction</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.transactions.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Client</label>
                        <select name="user_id" class="form-select select2" required>
                            <option value="">Select client</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ request('client') == $c->id ? 'selected' : '' }}>
                                    {{ $c->company_name ?? $c->name }} ({{ $c->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <x-form.select 
                            name="type"
                            label="Type"
                            :required="true"
                            :options="[
                                'subscription'     => 'Subscription',
                                'wallet_recharge'  => 'Wallet Recharge',
                                'ad_spend'         => 'Ad Spend',
                                'adjustment'       => 'Adjustment',
                            ]"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.select 
                            name="payment_method"
                            label="Payment Method"
                            :required="true"
                            :options="[
                                'bank_transfer' => 'Bank Transfer',
                                'cash'          => 'Cash',
                                'manual'        => 'Manual Entry',
                            ]"
                            :selected="'bank_transfer'"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="amount"
                            label="Base Amount (₹)"
                            type="number"
                            :required="true"
                            step="0.01"
                        />
                    </div>
                    <div class="col-md-8">
                        <x-form.input 
                            name="description"
                            label="Description"
                            :required="true"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="payment_reference"
                            label="Payment Reference"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form.input 
                            name="paid_at"
                            label="Paid At"
                            type="datetime-local"
                            :value="now()->format('Y-m-d\TH:i')"
                        />
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="credit_wallet" name="credit_wallet" value="1">
                            <label class="form-check-label" for="credit_wallet">
                                Credit Wallet (for wallet_recharge/adjustment)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Transaction
                    </button>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary ms-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection