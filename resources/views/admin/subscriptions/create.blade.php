{{-- resources/views/admin/subscriptions/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Create Subscription')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Create Manual Subscription</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.subscriptions.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Client</label>
                        <select name="user_id" class="form-select select2" required>
                            <option value="">Select client</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ request('client') == $c->id ? 'selected' : '' }}>
                                    {{ $c->company_name ?? $c->name }} ({{ $c->email }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted small">
                            Only clients without active subscriptions are listed.
                        </small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Package</label>
                        <select name="package_id" class="form-select" required>
                            <option value="">Select package</option>
                            @foreach($packages as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->name }} – ₹{{ number_format($p->price, 0) }}/{{ $p->billing_cycle }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="start_date"
                            label="Start Date"
                            type="date"
                            :required="true"
                            :value="now()->format('Y-m-d')"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.select 
                            name="payment_method"
                            label="Payment Method"
                            :required="true"
                            :options="[
                                'cash'          => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                                'wallet'        => 'Wallet',
                                'manual'        => 'Manual Entry',
                            ]"
                            :selected="'bank_transfer'"
                        />
                    </div>
                    <div class="col-md-4">
                        <x-form.input 
                            name="discount_amount"
                            label="Discount (₹)"
                            type="number"
                            :value="0"
                        />
                    </div>
                    <div class="col-md-12">
                        <x-form.input 
                            name="payment_reference"
                            label="Payment Reference / Notes"
                        />
                    </div>
                    <div class="col-md-12">
                        <x-form.textarea 
                            name="notes"
                            label="Internal Notes (optional)"
                            rows="3"
                        />
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Subscription
                    </button>
                    <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary ms-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection