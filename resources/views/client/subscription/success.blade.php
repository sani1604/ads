@extends('layouts.client')

@section('title', 'Payment Successful')
@section('page-title', 'Payment Successful')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                {{-- Success Icon --}}
                <div class="mb-4">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                        <i class="fas fa-check-circle fa-4x text-success"></i>
                    </div>
                </div>

                {{-- Success Message --}}
                <h2 class="mb-2">Payment Successful!</h2>
                <p class="text-muted mb-4">
                    Thank you for your purchase. Your subscription is now active.
                </p>

                {{-- Subscription Details --}}
                <div class="bg-light rounded-3 p-4 mb-4 text-start">
                    <h6 class="text-muted mb-3">Subscription Details</h6>
                    <div class="row g-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Plan</small>
                            <strong>{{ $subscription->package->name ?? 'N/A' }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Amount Paid</small>
                            <strong class="text-success">₹{{ number_format($subscription->total_amount ?? 0, 2) }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Start Date</small>
                            <strong>{{ $subscription->starts_at ? $subscription->starts_at->format('d M Y') : 'N/A' }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">End Date</small>
                            <strong>{{ $subscription->ends_at ? $subscription->ends_at->format('d M Y') : 'N/A' }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Billing Cycle</small>
                            <strong class="text-capitalize">{{ $subscription->package->billing_cycle ?? 'Monthly' }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Status</small>
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i>Active
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Payment Reference --}}
                @if($subscription->razorpay_payment_id)
                <div class="bg-white border rounded p-3 mb-4">
                    <small class="text-muted">Payment Reference</small>
                    <div class="font-monospace small">{{ $subscription->razorpay_payment_id }}</div>
                </div>
                @endif

                {{-- Action Buttons --}}
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                    <a href="{{ route('client.dashboard') }}" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Go to Dashboard
                    </a>
                    <a href="{{ route('client.subscription.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-receipt me-2"></i>View Subscription
                    </a>
                </div>

                {{-- Help Text --}}
                <p class="text-muted small mt-4 mb-0">
                    <i class="fas fa-envelope me-1"></i>
                    A confirmation email has been sent to your registered email address.
                </p>
            </div>
        </div>

        {{-- What's Next Card --}}
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-rocket text-primary me-2"></i>What's Next?
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <span class="text-primary fw-bold">1</span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">Complete Your Profile</h6>
                        <p class="text-muted small mb-0">Add your business details for better service.</p>
                    </div>
                </div>
                <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <span class="text-primary fw-bold">2</span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">Submit Your First Creative</h6>
                        <p class="text-muted small mb-0">Upload your brand assets and get started.</p>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <span class="text-primary fw-bold">3</span>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">Track Your Leads</h6>
                        <p class="text-muted small mb-0">Monitor and manage your incoming leads.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @keyframes checkmark {
        0% { transform: scale(0); opacity: 0; }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); opacity: 1; }
    }
    
    .fa-check-circle {
        animation: checkmark 0.5s ease-out forwards;
    }
</style>
@endpush