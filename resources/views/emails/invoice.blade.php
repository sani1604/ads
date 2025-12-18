{{-- resources/views/emails/invoice.blade.php --}}
@extends('emails.layouts.base')

@section('subject', 'Invoice ' . $invoice->invoice_number . ' from ' . config('app.name'))

@section('content')
    <p style="font-size:14px;color:#111827;margin:0 0 12px;">
        Hi {{ $invoice->user->name }},
    </p>

    <p style="font-size:14px;color:#111827;margin:0 0 12px;">
        Please find your invoice <strong>#{{ $invoice->invoice_number }}</strong> attached.
    </p>

    <table cellpadding="0" cellspacing="0" style="width:100%;font-size:14px;margin-bottom:16px;">
        <tr>
            <td style="color:#6b7280;">Amount</td>
            <td style="text-align:right;font-weight:bold;">₹{{ number_format($invoice->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Invoice Date</td>
            <td style="text-align:right;">{{ $invoice->invoice_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Due Date</td>
            <td style="text-align:right;">{{ $invoice->due_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Status</td>
            <td style="text-align:right;">{{ ucfirst($invoice->status) }}</td>
        </tr>
    </table>

    <p style="font-size:14px;color:#111827;margin:0 0 16px;">
        @if($invoice->status === 'paid')
            Thank you for your payment. You can download this invoice anytime from your client portal under <strong>Billing → Invoices</strong>.
        @else
            Please arrange payment before the due date. You can also view this invoice in your client portal under <strong>Billing → Invoices</strong>.
        @endif
    </p>

    <p style="font-size:14px;color:#111827;margin:0;">
        Regards,<br>
        {{ \App\Models\Setting::get('company_name', config('app.name')) }}
    </p>
@endsection