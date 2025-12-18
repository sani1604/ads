{{-- resources/views/pdf/invoice.blade.php --}}
@php
    $settings = \App\Models\Setting::getByGroup('invoice');
    $companyName = $settings['company_name'] ?? config('app.name');
    $companyAddress = $settings['company_address'] ?? '';
    $companyGst = $settings['company_gst'] ?? '';
    $companyPan = $settings['company_pan'] ?? '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 20px 24px;
        }
        h1, h2, h3, h4, h5 {
            margin: 0;
            font-weight: 600;
        }
        .header, .footer {
            width: 100%;
        }
        .header {
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
        }
        .company-name {
            font-size: 18px;
            font-weight: 700;
        }
        .invoice-title {
            font-size: 22px;
            font-weight: 700;
            text-align: right;
        }
        .invoice-number {
            font-size: 12px;
            text-align: right;
            margin-top: 4px;
        }
        .section-title {
            font-weight: 600;
            margin-bottom: 6px;
        }
        .address-block {
            font-size: 11px;
            line-height: 1.4;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .meta-table td {
            padding: 2px 0;
            font-size: 11px;
        }
        .meta-label {
            color: #6b7280;
            width: 80px;
        }
        .billto-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .billto-table td {
            vertical-align: top;
            width: 50%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 11px;
        }
        .items-table th {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            background-color: #f3f4f6;
            font-weight: 600;
        }
        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .totals-table {
            width: 40%;
            float: right;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 11px;
        }
        .totals-table td {
            padding: 4px 0;
        }
        .totals-label {
            text-align: left;
        }
        .totals-value {
            text-align: right;
        }
        .totals-table tr.total-row td {
            border-top: 1px solid #e5e7eb;
            font-weight: 600;
            padding-top: 6px;
        }

        .notes {
            margin-top: 25px;
            font-size: 11px;
        }
        .footer {
            position: fixed;
            bottom: 15px;
            left: 24px;
            right: 24px;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 4px;
        }
        .small {
            font-size: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td>
                <div class="company-name">{{ $companyName }}</div>
                @if($companyAddress)
                    <div class="small">{{ $companyAddress }}</div>
                @endif
                @if($companyGst)
                    <div class="small">GST: {{ $companyGst }}</div>
                @endif
                @if($companyPan)
                    <div class="small">PAN: {{ $companyPan }}</div>
                @endif
            </td>
            <td class="text-right" style="width: 40%;">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">#{{ $invoice->invoice_number }}</div>
            </td>
        </tr>
    </table>

    {{-- Meta --}}
    <table class="meta-table">
        <tr>
            <td class="meta-label">Invoice Date</td>
            <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Due Date</td>
            <td>{{ $invoice->due_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Status</td>
            <td>{{ ucfirst($invoice->status) }}</td>
        </tr>
    </table>

    {{-- Bill To / From --}}
    <table class="billto-table">
        <tr>
            <td>
                <div class="section-title">Bill To</div>
                <div class="address-block">
                    @php
                        $bill = $invoice->billing_address ?? [];
                    @endphp
                    <strong>{{ $bill['company'] ?? $invoice->user->company_name ?? $invoice->user->name }}</strong><br>
                    {{ $bill['name'] ?? $invoice->user->name }}<br>
                    @if(!empty($bill['address']))
                        {{ $bill['address'] }}<br>
                    @endif
                    @if(!empty($bill['city']) || !empty($bill['state']) || !empty($bill['postal_code']))
                        {{ $bill['city'] ?? '' }}
                        {{ !empty($bill['state']) ? ', '.$bill['state'] : '' }}
                        {{ !empty($bill['postal_code']) ? ' - '.$bill['postal_code'] : '' }}<br>
                    @endif
                    @if(!empty($bill['gst_number']))
                        GST: {{ $bill['gst_number'] }}
                    @endif
                </div>
            </td>
            <td>
                <div class="section-title">Bill From</div>
                <div class="address-block">
                    <strong>{{ $companyName }}</strong><br>
                    @if($companyAddress) {{ $companyAddress }}<br> @endif
                    @if($companyGst) GST: {{ $companyGst }}<br> @endif
                    @if($companyPan) PAN: {{ $companyPan }} @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Line Items --}}
    <table class="items-table">
        <thead>
        <tr>
            <th>Description</th>
            <th class="text-center" style="width: 50px;">Qty</th>
            <th class="text-right" style="width: 80px;">Rate</th>
            <th class="text-right" style="width: 90px;">Amount</th>
        </tr>
        </thead>
        <tbody>
        @foreach($invoice->getLineItemsForDisplay() as $item)
            <tr>
                <td>{{ $item['description'] }}</td>
                <td class="text-center">{{ $item['quantity'] ?? 1 }}</td>
                <td class="text-right">₹{{ number_format($item['rate'] ?? 0, 2) }}</td>
                <td class="text-right">₹{{ number_format($item['amount'] ?? 0, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <table class="totals-table">
        <tr>
            <td class="totals-label">Subtotal</td>
            <td class="totals-value">₹{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if($invoice->discount_amount > 0)
            <tr>
                <td class="totals-label">Discount</td>
                <td class="totals-value">-₹{{ number_format($invoice->discount_amount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td class="totals-label">{{ $invoice->tax_rate }}% {{ $settings['tax_name'] ?? 'GST' }}</td>
            <td class="totals-value">₹{{ number_format($invoice->tax_amount, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td class="totals-label">Total</td>
            <td class="totals-value">₹{{ number_format($invoice->total_amount, 2) }}</td>
        </tr>
    </table>

    {{-- Notes / Terms --}}
    <div style="clear: both;"></div>

    @if($invoice->notes)
        <div class="notes">
            <strong>Notes:</strong><br>
            {{ $invoice->notes }}
        </div>
    @endif

    @if(!empty($settings['invoice_terms']))
        <div class="notes">
            <strong>Terms & Conditions:</strong><br>
            {!! nl2br(e($settings['invoice_terms'])) !!}
        </div>
    @endif
</div>

<div class="footer">
    Generated by {{ config('app.name') }} • {{ now()->format('d M Y H:i') }}
</div>
</body>
</html>