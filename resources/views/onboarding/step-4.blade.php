@extends('layouts.auth')

@section('title', 'Onboarding - Step 4')

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
    
    .platform-card {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }
    .platform-card:hover { border-color: var(--primary-color, #6366f1); }
    .platform-card.selected {
        border-color: var(--primary-color, #6366f1);
        background: rgba(99, 102, 241, 0.05);
    }
    .platform-card input { display: none; }
    .platform-card img { width: 48px; height: 48px; margin-bottom: 10px; }
    .platform-card i { font-size: 2.5rem; margin-bottom: 10px; }
    .platform-card .fa-facebook { color: #1877f2; }
    .platform-card .fa-instagram { color: #e4405f; }
    .platform-card .fa-google { color: #4285f4; }
    .platform-card .fa-youtube { color: #ff0000; }
    .platform-card .fa-linkedin { color: #0077b5; }
    .platform-card .fa-twitter { color: #1da1f2; }
</style>
@endpush

@section('content')
<div class="progress-steps">
    <div class="progress-step completed">1</div>
    <div class="progress-step completed">2</div>
    <div class="progress-step completed">3</div>
    <div class="progress-step active">4</div>
    <div class="progress-step">5</div>
</div>

<h2 class="auth-title">Advertising Platforms</h2>
<p class="auth-subtitle">Select the platforms you use or plan to use</p>

<form method="POST" action="{{ route('onboarding.process', ['step' => 4]) }}">
    @csrf

    <div class="mb-4">
        <label class="form-label">Which platforms do you advertise on? <span class="text-danger">*</span></label>
        <div class="row g-3">
            @php
                $platforms = [
                    'facebook' => ['icon' => 'fab fa-facebook', 'label' => 'Facebook'],
                    'instagram' => ['icon' => 'fab fa-instagram', 'label' => 'Instagram'],
                    'google' => ['icon' => 'fab fa-google', 'label' => 'Google Ads'],
                    'youtube' => ['icon' => 'fab fa-youtube', 'label' => 'YouTube'],
                    'linkedin' => ['icon' => 'fab fa-linkedin', 'label' => 'LinkedIn'],
                    'twitter' => ['icon' => 'fab fa-twitter', 'label' => 'Twitter/X'],
                ];
                $selectedPlatforms = old('platforms', $user->platforms ?? []);
                if (is_string($selectedPlatforms)) {
                    $selectedPlatforms = json_decode($selectedPlatforms, true) ?? [];
                }
            @endphp
            @foreach($platforms as $value => $platform)
                <div class="col-4">
                    <label class="platform-card w-100 {{ in_array($value, $selectedPlatforms) ? 'selected' : '' }}">
                        <input type="checkbox" name="platforms[]" value="{{ $value }}" 
                               {{ in_array($value, $selectedPlatforms) ? 'checked' : '' }}>
                        <i class="{{ $platform['icon'] }}"></i>
                        <div class="fw-semibold small">{{ $platform['label'] }}</div>
                    </label>
                </div>
            @endforeach
        </div>
        @error('platforms')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label class="form-label">Primary Goal</label>
        <select class="form-select @error('primary_goal') is-invalid @enderror" name="primary_goal">
            <option value="">Select your primary goal</option>
            <option value="lead_generation" {{ old('primary_goal', $user->primary_goal ?? '') == 'lead_generation' ? 'selected' : '' }}>Lead Generation</option>
            <option value="brand_awareness" {{ old('primary_goal', $user->primary_goal ?? '') == 'brand_awareness' ? 'selected' : '' }}>Brand Awareness</option>
            <option value="sales" {{ old('primary_goal', $user->primary_goal ?? '') == 'sales' ? 'selected' : '' }}>Direct Sales</option>
            <option value="app_installs" {{ old('primary_goal', $user->primary_goal ?? '') == 'app_installs' ? 'selected' : '' }}>App Installs</option>
            <option value="traffic" {{ old('primary_goal', $user->primary_goal ?? '') == 'traffic' ? 'selected' : '' }}>Website Traffic</option>
        </select>
        @error('primary_goal')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('onboarding.step', ['step' => 3]) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back
        </a>
        <button type="submit" class="btn btn-primary">
            Continue <i class="fas fa-arrow-right ms-2"></i>
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.platform-card').forEach(card => {
    card.addEventListener('click', function(e) {
        e.preventDefault();
        this.classList.toggle('selected');
        const checkbox = this.querySelector('input');
        checkbox.checked = !checkbox.checked;
    });
});
</script>
@endpush