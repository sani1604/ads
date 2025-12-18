{{-- resources/views/emails/subscription-activated.blade.php --}}
@extends('emails.layouts.base')

@section('subject', 'Your Subscription is Active')

@section('content')
    <p style="font-size:14px;color:#111827;margin:0 0 12px;">
        Hi {{ $subscription->user->name }},
    </p>

    <p style="font-size:14px;color:#111827;margin:0 0 12px;">
        Your subscription <strong>{{ $subscription->package->name }}</strong> is now <strong>active</strong>.
    </p>

    <table cellpadding="0" cellspacing="0" style="width:100%;font-size:14px;margin-bottom:16px;">
        <tr>
            <td style="color:#6b7280;">Plan</td>
            <td style="text-align:right;">{{ $subscription->package->name }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Billing</td>
            <td style="text-align:right;">
                ₹{{ number_format($subscription->total_amount, 2) }} / {{ $subscription->package->billing_cycle }}
            </td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Period</td>
            <td style="text-align:right;">
                {{ $subscription->start_date->format('d M Y') }} – {{ $subscription->end_date->format('d M Y') }}
            </td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Next Billing</td>
            <td style="text-align:right;">
                {{ $subscription->next_billing_date->format('d M Y') }}
            </td>
        </tr>
    </table>

    <p style="font-size:14px;color:#111827;margin:0 0 16px;">
        You can view your subscription details and billing history anytime from your dashboard.
    </p>

    <p style="margin:0;">
        <a href="{{ route('client.subscription.index') }}"
           style="display:inline-block;padding:10px 18px;background:#6366f1;color:#ffffff;text-decoration:none;border-radius:4px;font-size:14px;">
            Go to Subscription
        </a>
    </p>
@endsection