@extends('layouts.auth')

@section('title', 'Onboarding - Step 2')

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
</style>
@endpush

@section('content')
<div class="progress-steps">
    <div class="progress-step completed">1</div>
    <div class="progress-step active">2</div>
    <div class="progress-step">3</div>
    <div class="progress-step">4</div>
    <div class="progress-step">5</div>
</div>

<h2 class="auth-title">Contact Details</h2>
<p class="auth-subtitle">How can we reach you?</p>

<form method="POST" action="{{ route('onboarding.process', ['step' => 2]) }}">
    @csrf

    <div class="mb-3">
        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
        <input 
            type="tel" 
            class="form-control @error('phone') is-invalid @enderror" 
            id="phone" 
            name="phone" 
            value="{{ old('phone', $user->phone) }}"
            placeholder="+91 9876543210"
            required
        >
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="alt_phone" class="form-label">Alternative Phone <span class="text-muted">(Optional)</span></label>
        <input 
            type="tel" 
            class="form-control @error('alt_phone') is-invalid @enderror" 
            id="alt_phone" 
            name="alt_phone" 
            value="{{ old('alt_phone', $user->alt_phone) }}"
            placeholder="+91 9876543210"
        >
        @error('alt_phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="city" class="form-label">City <span class="text-danger">*</span></label>
        <input 
            type="text" 
            class="form-control @error('city') is-invalid @enderror" 
            id="city" 
            name="city" 
            value="{{ old('city', $user->city) }}"
            placeholder="Mumbai"
            required
        >
        @error('city')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="state" class="form-label">State <span class="text-danger">*</span></label>
        <select class="form-select @error('state') is-invalid @enderror" id="state" name="state" required>
            <option value="">Select State</option>
            @php
                $states = [
                    'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
                    'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka',
                    'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram',
                    'Nagaland', 'Odisha', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu',
                    'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal',
                    'Delhi', 'Chandigarh', 'Puducherry'
                ];
            @endphp
            @foreach($states as $state)
                <option value="{{ $state }}" {{ old('state', $user->state ?? '') == $state ? 'selected' : '' }}>
                    {{ $state }}
                </option>
            @endforeach
        </select>
        @error('state')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('onboarding.step', ['step' => 1]) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back
        </a>
        <button type="submit" class="btn btn-primary">
            Continue <i class="fas fa-arrow-right ms-2"></i>
        </button>
    </div>
</form>
@endsection