{{-- resources/views/client/wallet/recharge.blade.php --}}
@extends('layouts.client')

@section('title', 'Recharge Wallet')
@section('page-title', 'Recharge Wallet')

@section('content')
    <div class="row g-4">
        {{-- Recharge Form --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Add Money to Wallet</h5>
                </div>
                <div class="card-body">
                    <form id="rechargeForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Amount (INR)</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number"
                                       class="form-control"
                                       name="amount"
                                       min="{{ \App\Models\Setting::get('min_wallet_recharge', 5000) }}"
                                       step="500"
                                       value="{{ request('amount', \App\Models\Setting::get('min_wallet_recharge', 5000)) }}"
                                       required>
                            </div>
                            <small class="form-text text-muted">
                                Minimum recharge: ₹{{ \App\Models\Setting::get('min_wallet_recharge', 5000) }}
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Suggested Amounts</label><br>
                            @foreach($suggestedAmounts as $amt)
                                <button type="button" class="btn btn-outline-secondary btn-sm me-2 mb-2"
                                        onclick="document.querySelector('input[name=amount]').value={{ $amt }}">
                                    ₹{{ number_format($amt, 0) }}
                                </button>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            <button type="button" id="payButton" class="btn btn-primary">
                                <i class="fas fa-lock me-1"></i>Pay Securely
                            </button>
                            <a href="{{ route('client.wallet.index') }}" class="btn btn-outline-secondary ms-2">
                                Cancel
                            </a>
                        </div>
                    </form>

                    <p class="text-muted small mt-3 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        You will receive a tax invoice for this recharge. GST @ {{ \App\Models\Setting::get('tax_rate', 18) }}% will be applied.
                    </p>
                </div>
            </div>
        </div>

        {{-- Summary --}}
        <div class="col-lg-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="text-muted mb-3">Current Balance</h6>
                    <h3>{{ $user->formatted_wallet_balance }}</h3>
                    <hr>
                    <h6 class="text-muted mb-3">Why use Wallet?</h6>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2"><i class="fas fa-check text-success me-1"></i> Faster campaign launches</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-1"></i> Auto-renew subscriptions</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-1"></i> Single invoice for multiple spends</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('payButton').addEventListener('click', async function() {
    const btn = this;
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

    try {
        const amount = document.querySelector('input[name=amount]').value;

        const res = await fetch('{{ route("client.wallet.create-recharge-order") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ amount })
        });

        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Failed to create order');

        const opts = {
            key: data.data.key,
            amount: data.data.amount * 100,
            currency: data.data.currency,
            name: data.data.name,
            description: data.data.description,
            order_id: data.data.order_id,
            prefill: data.data.prefill,
            theme: { color: '#6366f1' },
            handler: async function (response) {
                const verifyRes = await fetch('{{ route("client.wallet.verify-recharge") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_signature: response.razorpay_signature
                    })
                });

                const verifyData = await verifyRes.json();
                if (verifyData.success) {
                    window.location.href = verifyData.redirect;
                } else {
                    alert(verifyData.message || 'Payment verification failed.');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            },
            modal: {
                ondismiss: function () {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            }
        };

        const rzp = new Razorpay(opts);
        rzp.open();
    } catch (e) {
        alert(e.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});
</script>
@endpush