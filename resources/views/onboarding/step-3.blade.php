@extends('layouts.auth')

@section('title', 'Onboarding - Step 3')

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
    
    .option-card {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }
    .option-card:hover { border-color: var(--primary-color, #6366f1); }
    .option-card.selected {
        border-color: var(--primary-color, #6366f1);
        background: rgba(99, 102, 241, 0.05);
    }
    .option-card input { display: none; }
    .option-card i { font-size: 2rem; margin-bottom: 10px; color: var(--primary-color, #6366f1); }
</style>
@endpush

@section('content')
<div class="progress-steps">
    <div class="progress-step completed">1</div>
    <div class="progress-step completed">2</div>
    <div class="progress-step active">3</div>
    <div class="progress-step">4</div>
    <div class="progress-step">5</div>
</div>

<h2 class="auth-title">Business Size</h2>
<p class="auth-subtitle">This helps us recommend the right plan for you</p>

<form method="POST" action="{{ route('onboarding.process', ['step' => 3]) }}">
    @csrf

    <div class="mb-4">
        <label class="form-label">Monthly Advertising Budget <span class="text-danger">*</span></label>
        <div class="row g-3">
            @php
                $budgets = [
                    'below_50k' => ['icon' => 'fas fa-seedling', 'label' => 'Below ₹50K'],
                    '50k_1l' => ['icon' => 'fas fa-leaf', 'label' => '₹50K - ₹1 Lakh'],
                    '1l_5l' => ['icon' => 'fas fa-tree', 'label' => '₹1L - ₹5 Lakh'],
                    '5l_10l' => ['icon' => 'fas fa-mountain', 'label' => '₹5L - ₹10 Lakh'],
                    'above_10l' => ['icon' => 'fas fa-building', 'label' => 'Above ₹10 Lakh'],
                ];
            @endphp
            @foreach($budgets as $value => $budget)
                <div class="col-6">
                    <label class="option-card w-100 {{ old('monthly_budget', $user->monthly_budget ?? '') == $value ? 'selected' : '' }}">
                        <input type="radio" name="monthly_budget" value="{{ $value }}" 
                               {{ old('monthly_budget', $user->monthly_budget ?? '') == $value ? 'checked' : '' }} required>
                        <i class="{{ $budget['icon'] }}"></i>
                        <div class="fw-semibold">{{ $budget['label'] }}</div>
                    </label>
                </div>
            @endforeach
        </div>
        @error('monthly_budget')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label class="form-label">Team Size <span class="text-danger">*</span></label>
        <select class="form-select @error('team_size') is-invalid @enderror" name="team_size" required>
            <option value="">Select team size</option>
            <option value="1" {{ old('team_size', $user->team_size ?? '') == '1' ? 'selected' : '' }}>Just me</option>
            <option value="2-5" {{ old('team_size', $user->team_size ?? '') == '2-5' ? 'selected' : '' }}>2-5 people</option>
            <option value="6-10" {{ old('team_size', $user->team_size ?? '') == '6-10' ? 'selected' : '' }}>6-10 people</option>
            <option value="11-50" {{ old('team_size', $user->team_size ?? '') == '11-50' ? 'selected' : '' }}>11-50 people</option>
            <option value="50+" {{ old('team_size', $user->team_size ?? '') == '50+' ? 'selected' : '' }}>50+ people</option>
        </select>
        @error('team_size')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('onboarding.step', ['step' => 2]) }}" class="btn btn-outline-secondary">
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
document.querySelectorAll('.option-card').forEach(card => {
    card.addEventListener('click', function() {
        const name = this.querySelector('input').name;
        document.querySelectorAll(`input[name="${name}"]`).forEach(input => {
            input.closest('.option-card').classList.remove('selected');
        });
        this.classList.add('selected');
        this.querySelector('input').checked = true;
    });
});
</script>
@endpush