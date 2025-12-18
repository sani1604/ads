@extends('layouts.client')

@section('title', 'Checkout')
@section('page-title', 'Checkout')

@section('content')
    <div class="row g-4">
        {{-- Order Summary --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                            <i class="fas fa-box fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">{{ $package->name }}</h5>
                            <p class="text-muted mb-0">{{ $package->serviceCategory->name }}</p>
                        </div>
                        <div class="text-end">
                            <h4 class="mb-0">₹{{ number_format($package->price, 0) }}</h4>
                            <small class="text-muted">/{{ $package->billing_cycle }}</small>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">What's Included:</h6>
                    @php
                        $raw = $package->features ?? [];
                        if (is_array($raw)) {
                            $features = $raw;
                        } elseif (is_string($raw)) {
                            $decoded = json_decode($raw, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $features = $decoded;
                            } else {
                                $features = preg_split('/\r\n|\r|\n/', $raw, -1, PREG_SPLIT_NO_EMPTY);
                            }
                        } else {
                            $features = [];
                        }
                    @endphp

                    @if(count($features))
                        <div class="row">
                            @foreach($features as $feature)
                                <div class="col-md-6 mb-2">
                                    <i class="fas fa-check text-success me-2"></i>{{ $feature }}
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No features configured for this plan.</p>
                    @endif
                </div>
            </div>

            {{-- Billing Details --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Billing Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" value="{{ $user->phone }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company</label>
                            <input type="text" class="form-control" value="{{ $user->company_name ?? '-' }}" readonly>
                        </div>
                        @if($user->gst_number)
                            <div class="col-12">
                                <label class="form-label">GST Number</label>
                                <input type="text" class="form-control" value="{{ $user->gst_number }}" readonly>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Summary --}}
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 90px;">
                <div class="card-header">
                    <h5 class="mb-0">Payment Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td>Subtotal</td>
                            <td class="text-end">₹{{ number_format($package->price, 2) }}</td>
                        </tr>
                        @if($package->has_discount)
                            <tr class="text-success">
                                <td>Discount</td>
                                <td class="text-end">-₹{{ number_format($package->original_price - $package->price, 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td>GST (18%)</td>
                            <td class="text-end">₹{{ number_format($package->tax_amount, 2) }}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-bold fs-5">Total</td>
                            <td class="text-end fw-bold fs-5">₹{{ number_format($package->price_with_tax, 2) }}</td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    <button id="payButton" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-lock me-2"></i>Pay ₹{{ number_format($package->price_with_tax, 2) }}
                    </button>
                    <p class="text-muted text-center small mt-3 mb-0">
                        <i class="fas fa-shield-alt me-1"></i>
                        Secured by Razorpay
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('payButton').addEventListener('click', async function() {
    const button = this;
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

    try {
        const response = await fetch('{{ route("client.subscription.create-order") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                package_id: {{ $package->id }}
            })
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Failed to create order');
        }

        const options = {
            key: data.data.key,
            amount: data.data.amount * 100,
            currency: data.data.currency,
            name: data.data.name,
            description: data.data.description,
            order_id: data.data.order_id,
            prefill: data.data.prefill,
            theme: { color: '#6366f1' },
            handler: async function(response) {
                const verifyResponse = await fetch('{{ route("client.subscription.verify-payment") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_signature: response.razorpay_signature,
                        package_id: {{ $package->id }}
                    })
                });

                const verifyData = await verifyResponse.json();

                if (verifyData.success) {
                    window.location.href = verifyData.redirect;
                } else {
                    alert('Payment verification failed. Please contact support.');
                }
            },
            modal: {
                ondismiss: function() {
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-lock me-2"></i>Pay ₹{{ number_format($package->price_with_tax, 2) }}';
                }
            }
        };

        const rzp = new Razorpay(options);
        rzp.open();

    } catch (error) {
        alert(error.message);
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-lock me-2"></i>Pay ₹{{ number_format($package->price_with_tax, 2) }}';
    }
});
</script>
@endpush