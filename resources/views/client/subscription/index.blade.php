@extends('layouts.client')

@section('title', 'Subscription')
@section('page-title', 'Subscription')

@section('content')
    @if($subscription)
        {{-- Current Subscription --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-box fa-2x text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="mb-1">{{ $subscription->package->name }}</h4>
                                <p class="text-muted mb-0">
                                    {{ $subscription->package->serviceCategory->name ?? '' }}
                                </p>
                            </div>
                            <div class="ms-3">
                                {!! $subscription->status_badge !!}
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-4">
                                <div class="border rounded p-3 text-center">
                                    <div class="fw-bold fs-4">{{ $subscription->formatted_amount }}</div>
                                    <small class="text-muted text-capitalize">
                                        / {{ $subscription->package->billing_cycle ?? 'month' }}
                                    </small>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="border rounded p-3 text-center">
                                    <div class="fw-bold fs-4">{{ $subscription->days_remaining }}</div>
                                    <small class="text-muted">Days Remaining</small>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="border rounded p-3 text-center">
                                    <div class="fw-bold fs-4">
                                        {{ $subscription->getCreativesRemainingThisMonth() }}
                                    </div>
                                    <small class="text-muted">Creatives Left This Month</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <p class="text-muted mb-2">
                            <i class="fas fa-calendar me-1"></i>
                            Renews on {{ $subscription->next_billing_date->format('M d, Y') }}
                        </p>
                        <a href="{{ route('client.subscription.plans') }}" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-up me-1"></i>Upgrade Plan
                        </a>
                        @if($subscription->canCancel())
                            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                Cancel
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Plan Features --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Plan Features</h5>
            </div>
            <div class="card-body">
                @php
                    $raw = $subscription->package->features ?? [];

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
                    <p class="text-muted mb-0">No features listed for this package.</p>
                @endif
            </div>
        </div>
    @else
        {{-- No active subscription --}}
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
                <h4>No Active Subscription</h4>
                <p class="text-muted mb-4">
                    Choose a plan to start working with our team and get your campaigns running.
                </p>
                <a href="{{ route('client.subscription.plans') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-rocket me-2"></i>View Plans
                </a>
            </div>
        </div>
    @endif

    {{-- Subscription History --}}
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Subscription History</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Package</th>
                        <th>Amount</th>
                        <th>Period</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptionHistory as $sub)
                        <tr>
                            <td><code>{{ $sub->subscription_code }}</code></td>
                            <td>{{ $sub->package->name ?? '-' }}</td>
                            <td>{{ $sub->formatted_amount }}</td>
                            <td>
                                {{ $sub->start_date->format('M d, Y') }} – {{ $sub->end_date->format('M d, Y') }}
                            </td>
                            <td>{!! $sub->status_badge !!}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No subscription history.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscriptionHistory->hasPages())
            <div class="card-footer">
                {{ $subscriptionHistory->links() }}
            </div>
        @endif
    </div>

    {{-- Cancel Modal --}}
    @if($subscription && $subscription->canCancel())
        <div class="modal fade" id="cancelModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('client.subscription.cancel', $subscription) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Cancel Subscription</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                Your plan will remain active until {{ $subscription->end_date->format('M d, Y') }}.
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reason (optional)</label>
                                <textarea name="reason" rows="3" class="form-control" placeholder="Please tell us why..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Keep Subscription</button>
                            <button class="btn btn-danger">Cancel Subscription</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection