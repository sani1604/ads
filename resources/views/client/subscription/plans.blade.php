@extends('layouts.client')

@section('title', 'Choose a Plan')
@section('page-title', 'Choose a Plan')

@section('content')
    {{-- Category Tabs --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        @foreach($categories as $index => $category)
            <button type="button"
                    class="btn btn-sm {{ $index === 0 ? 'btn-primary' : 'btn-outline-primary' }} category-tab"
                    data-category="{{ $category->id }}">
                <i class="{{ $category->icon }} me-1"></i>{{ $category->name }}
            </button>
        @endforeach
    </div>

    {{-- Plans per Category --}}
    @foreach($categories as $index => $category)
        <div class="category-packages {{ $index === 0 ? '' : 'd-none' }}" data-category="{{ $category->id }}">
            <div class="row g-4">
                @forelse($category->packages as $package)
                    <div class="col-lg-4">
                        <div class="card h-100 border-2 {{ $package->is_featured ? 'border-primary' : 'border-light' }}">
                            <div class="card-header bg-white border-bottom-0">
                                <h5 class="mb-1">{{ $package->name }}</h5>
                                <p class="text-muted small mb-0">{{ $package->short_description }}</p>
                                @if($package->is_featured)
                                    <span class="badge bg-primary mt-2">Most Popular</span>
                                @endif
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <span class="fs-3 fw-bold">₹{{ number_format($package->price, 0) }}</span>
                                    <span class="text-muted">/ {{ $package->billing_cycle }}</span><br>
                                    @if($package->has_discount)
                                        <small class="text-muted text-decoration-line-through">
                                            ₹{{ number_format($package->original_price, 0) }}
                                        </small>
                                        <small class="badge bg-success ms-1">
                                            -{{ $package->discount_percentage }}%
                                        </small>
                                    @endif
                                </div>

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
                                    <ul class="list-unstyled mb-0">
                                        @foreach($features as $feature)
                                            <li class="mb-1">
                                                <i class="fas fa-check text-success me-2"></i>{{ $feature }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted small mb-0">No features configured.</p>
                                @endif
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                @if($user->activeSubscription && $user->activeSubscription->package_id === $package->id)
                                    <button class="btn btn-success w-100" disabled>
                                        <i class="fas fa-check me-1"></i>Current Plan
                                    </button>
                                @else
                                    <a href="{{ route('client.subscription.checkout', $package) }}" class="btn btn-primary w-100">
                                        Choose Plan
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <x-empty-state 
                            icon="fas fa-box"
                            title="No packages in this category"
                            message="Please contact support."
                        />
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
<script>
document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.category-tab').forEach(t => {
            t.classList.remove('btn-primary');
            t.classList.add('btn-outline-primary');
        });
        this.classList.remove('btn-outline-primary');
        this.classList.add('btn-primary');

        const id = this.dataset.category;
        document.querySelectorAll('.category-packages').forEach(ct => {
            ct.classList.add('d-none');
        });
        document.querySelector(`.category-packages[data-category="${id}"]`).classList.remove('d-none');
    });
});
</script>
@endpush