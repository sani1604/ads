{{-- resources/views/client/invoices/show.blade.php --}}
@extends('layouts.client')

@section('title', 'Invoice ' . $invoice->invoice_number)
@section('page-title', 'Invoice Details')

@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Invoice #{{ $invoice->invoice_number }}</h5>
                <small class="text-muted">
                    Issued on {{ $invoice->invoice_date->format('M d, Y') }}
                </small>
            </div>
            <div class="d-flex gap-2">
                {!! $invoice->status_badge !!}
                <a href="{{ route('client.invoices.download', $invoice) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-download me-1"></i>PDF
                </a>
            </div>
        </div>
        <div class="card-body">
            {{-- Billing Info --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Billed To</h6>
                    <p class="mb-0 fw-semibold">{{ $invoice->billing_address['company'] ?? $invoice->user->company_name ?? $invoice->user->name }}</p>
                    <p class="mb-0">{{ $invoice->billing_address['name'] ?? $invoice->user->name }}</p>
                    @if(!empty($invoice->billing_address['address']))
                        <p class="mb-0">{{ $invoice->billing_address['address'] }}</p>
                    @endif
                    <p class="mb-0">
                        {{ $invoice->billing_address['city'] ?? '' }}
                        {{ $invoice->billing_address['state'] ? ', '.$invoice->billing_address['state'] : '' }}
                        {{ $invoice->billing_address['postal_code'] ?? '' }}
                    </p>
                    @if(!empty($invoice->billing_address['gst_number']))
                        <p class="mb-0">GST: {{ $invoice->billing_address['gst_number'] }}</p>
                    @endif
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <h6 class="text-muted mb-2">From</h6>
                    <p class="mb-0 fw-semibold">{{ \App\Models\Setting::get('company_name', config('app.name')) }}</p>
                    <p class="mb-0">{{ \App\Models\Setting::get('company_address') }}</p>
                    @if(\App\Models\Setting::get('company_gst'))
                        <p class="mb-0">GST: {{ \App\Models\Setting::get('company_gst') }}</p>
                    @endif
                    @if(\App\Models\Setting::get('company_pan'))
                        <p class="mb-0">PAN: {{ \App\Models\Setting::get('company_pan') }}</p>
                    @endif
                </div>
            </div>

            {{-- Line Items --}}
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th class="text-center" width="80">Qty</th>
                            <th class="text-end" width="120">Rate</th>
                            <th class="text-end" width="120">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->getLineItemsForDisplay() as $item)
                            <tr>
                                <td>{{ $item['description'] }}</td>
                                <td class="text-center">{{ $item['quantity'] ?? 1 }}</td>
                                <td class="text-end">₹{{ number_format($item['rate'] ?? 0, 2) }}</td>
                                <td class="text-end">₹{{ number_format($item['amount'] ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totals --}}
            <div class="row justify-content-end">
                <div class="col-md-6 col-lg-4">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td>Subtotal</td>
                            <td class="text-end">₹{{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        @if($invoice->discount_amount > 0)
                            <tr>
                                <td>Discount</td>
                                <td class="text-end text-success">-₹{{ number_format($invoice->discount_amount, 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td>{{ $invoice->tax_rate }}% {{ \App\Models\Setting::get('tax_name', 'GST') }}</td>
                            <td class="text-end">₹{{ number_format($invoice->tax_amount, 2) }}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-bold">Total</td>
                            <td class="text-end fw-bold fs-5">₹{{ number_format($invoice->total_amount, 2) }}</td>
                        </tr>
                        @if($invoice->status === 'paid' && $invoice->paid_at)
                            <tr>
                                <td class="text-muted small">Paid On</td>
                                <td class="text-end small text-muted">{{ $invoice->paid_at->format('M d, Y') }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Notes --}}
            @if($invoice->notes)
                <hr>
                <h6 class="text-muted">Notes</h6>
                <p class="mb-0">{{ $invoice->notes }}</p>
            @endif
        </div>
        <div class="card-footer bg-white d-flex justify-content-between">
            <a href="{{ route('client.invoices.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Invoices
            </a>
            <a href="{{ route('client.invoices.download', $invoice) }}" class="btn btn-primary">
                <i class="fas fa-download me-1"></i> Download PDF
            </a>
        </div>
    </div>
@endsection