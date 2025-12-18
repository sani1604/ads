@extends('layouts.admin')

@section('title', 'Create Invoice')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Create Manual Invoice</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.invoices.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Client</label>
                    <select name="user_id" class="form-select select2" required>
                        <option value="">Select client</option>
                        @foreach(\App\Models\User::clients()->orderBy('name')->get() as $c)
                            <option value="{{ $c->id }}">
                                {{ $c->company_name ?? $c->name }} ({{ $c->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select" required>
                        <option value="subscription">Subscription</option>
                        <option value="wallet_recharge">Wallet Recharge</option>
                        <option value="one_time">One Time</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Invoice Date</label>
                    <input type="date" name="invoice_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ now()->addDays(7)->format('Y-m-d') }}" required>
                </div>

                {{-- Simple single line item, can be extended to multiple --}}
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <input type="text" name="line_items[0][description]" class="form-control" placeholder="Description" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="line_items[0][quantity]" class="form-control" value="1" min="1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Rate (₹)</label>
                    <input type="number" step="0.01" name="line_items[0][rate]" class="form-control" value="0" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Discount (₹)</label>
                    <input type="number" step="0.01" name="discount_amount" class="form-control" value="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tax (%)</label>
                    <input type="number" step="0.1" name="tax_rate" class="form-control"
                           value="{{ \App\Models\Setting::get('tax_rate',18) }}">
                </div>

                <div class="col-12">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" rows="2" class="form-control"></textarea>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Invoice</button>
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection