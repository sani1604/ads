@extends('layouts.auth')

@section('title', 'Onboarding - Complete')

@push('styles')
<style>
    .auth-card { max-width: 550px; }
    .progress-steps { display: flex; justify-content: center; margin-bottom: 30px; gap: 10px; }
    .progress-step {
        width: 40px; height: 40px; border-radius: 50%;
        background: #e2e8f0; display: flex; align-items: center;
        justify-content: center; font-weight: 600; color: #64748b;
        position: relative;
    }
    .progress-step.active { background: var(--primary-color, #6366f1); color: white; }
    .progress-step.completed { background: #22c55e; color: white; }
    .progress-step:not(:last-child)::after {
        content: ''; position: absolute; left: 100%; top: 50%;
        width: 30px; height: 2px; background: #e2e8f0; margin-left: 5px;
    }
    .progress-step.completed:not(:last-child)::after { background: #22c55e; }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .summary-item:last-child { border-bottom: none; }
    .summary-label { color: #64748b; }
    .summary-value { font-weight: 600; }
    
    .success-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }
    .success-icon i { font-size: 2.5rem; color: white; }
</style>
@endpush

@section('content')
<div class="progress-steps">
    <div class="progress-step completed">1</div>
    <div class="progress-step completed">2</div>
    <div class="progress-step completed">3</div>
    <div class="progress-step completed">4</div>
    <div class="progress-step active">5</div>
</div>

<div class="success-icon">
    <i class="fas fa-check"></i>
</div>

<h2 class="auth-title">Almost Done!</h2>
<p class="auth-subtitle">Review your information and complete setup</p>

<div class="card mb-4">
    <div class="card-body">
        <h6 class="text-muted mb-3">Profile Summary</h6>
        
        <div class="summary-item">
            <span class="summary-label">Company</span>
            <span class="summary-value">{{ $user->company_name ?? 'Not set' }}</span>
        </div>
        
        <div class="summary-item">
            <span class="summary-label">Industry</span>
            <span class="summary-value">{{ $user->industry->name ?? 'Not set' }}</span>
        </div>
        
        <div class="summary-item">
            <span class="summary-label">Phone</span>
            <span class="summary-value">{{ $user->phone ?? 'Not set' }}</span>
        </div>
        
        <div class="summary-item">
            <span class="summary-label">Location</span>
            <span class="summary-value">
                @if($user->city || $user->state)
                    {{ $user->city ?? '' }}{{ $user->city && $user->state ? ', ' : '' }}{{ $user->state ?? '' }}
                @else
                    Not set
                @endif
            </span>
        </div>
        
        <div class="summary-item">
            <span class="summary-label">Monthly Budget</span>
            <span class="summary-value">
                @php
                    $budgetLabels = [
                        'below_50k' => 'Below ₹50K',
                        '50k_1l' => '₹50K - ₹1 Lakh',
                        '1l_5l' => '₹1L - ₹5 Lakh',
                        '5l_10l' => '₹5L - ₹10 Lakh',
                        'above_10l' => 'Above ₹10 Lakh',
                    ];
                @endphp
                {{ $budgetLabels[$user->monthly_budget ?? ''] ?? 'Not set' }}
            </span>
        </div>
        
        <div class="summary-item">
            <span class="summary-label">Platforms</span>
            <span class="summary-value">
                @php
                    $platforms = $user->platforms ?? [];
                    if (is_string($platforms)) {
                        $platforms = json_decode($platforms, true) ?? [];
                    }
                @endphp
                {{ count($platforms) > 0 ? implode(', ', array_map('ucfirst', $platforms)) : 'Not set' }}
            </span>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('onboarding.process', ['step' => 5]) }}">
    @csrf

    <div class="mb-4">
        <div class="form-check">
            <input class="form-check-input @error('terms') is-invalid @enderror" 
                   type="checkbox" id="terms" name="terms" value="1" required>
            <label class="form-check-label" for="terms">
               I agree to the  <a href="#" target="_blank">Terms of Service</a>Terms of Service</a> and 
                <!--I agree to the <a href="{{ route('terms') ?? '#' }}" target="_blank">Terms of Service</a> and -->
                <a href="{{ route('privacy') ?? '#' }}" target="_blank">Privacy Policy</a>
            </label>
            @error('terms')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="marketing" name="marketing_consent" value="1"
                   {{ old('marketing_consent', $user->marketing_consent ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="marketing">
                Send me tips, updates, and promotional offers via email
            </label>
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('onboarding.step', ['step' => 4]) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back
        </a>
        <button type="submit" class="btn btn-success btn-lg">
            <i class="fas fa-rocket me-2"></i> Complete Setup
        </button>
    </div>
</form>
@endsection