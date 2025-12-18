{{-- resources/views/onboarding/step-1.blade.php --}}
@extends('layouts.auth')

@section('title', 'Onboarding - Step 1')

@push('styles')
<style>
    .auth-card {
        max-width: 550px;
    }
    
    .progress-steps {
        display: flex;
        justify-content: center;
        margin-bottom: 30px;
    }
    
    .progress-step {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: #64748b;
        position: relative;
    }
    
    .progress-step.active {
        background: var(--primary-color);
        color: white;
    }
    
    .progress-step.completed {
        background: #22c55e;
        color: white;
    }
    
    .progress-step:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 100%;
        top: 50%;
        width: 40px;
        height: 2px;
        background: #e2e8f0;
    }
    
    .progress-step.completed:not(:last-child)::after {
        background: #22c55e;
    }
</style>
@endpush

@section('content')
    <div class="progress-steps">
        <div class="progress-step active">1</div>
        <div class="progress-step">2</div>
        <div class="progress-step">3</div>
        <div class="progress-step">4</div>
        <div class="progress-step">5</div>
    </div>

    <h2 class="auth-title">Tell us about your business</h2>
    <p class="auth-subtitle">This helps us customize your experience</p>

    <form method="POST" action="{{ route('onboarding.process', ['step' => 1]) }}">
        @csrf

        <div class="mb-3">
            <label for="company_name" class="form-label">Company/Business Name <span class="text-danger">*</span></label>
            <input 
                type="text" 
                class="form-control @error('company_name') is-invalid @enderror" 
                id="company_name" 
                name="company_name" 
                value="{{ old('company_name', $user->company_name) }}"
                placeholder="Your Business Name"
                required
            >
            @error('company_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="industry_id" class="form-label">Industry <span class="text-danger">*</span></label>
            <select class="form-select @error('industry_id') is-invalid @enderror" id="industry_id" name="industry_id" required>
                <option value="">Select your industry</option>
                @foreach($industries as $industry)
                    <option value="{{ $industry->id }}" {{ old('industry_id', $user->industry_id) == $industry->id ? 'selected' : '' }}>
                        {{ $industry->name }}
                    </option>
                @endforeach
            </select>
            @error('industry_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="company_website" class="form-label">Website <span class="text-muted">(Optional)</span></label>
            <input 
                type="url" 
                class="form-control @error('company_website') is-invalid @enderror" 
                id="company_website" 
                name="company_website" 
                value="{{ old('company_website', $user->company_website) }}"
                placeholder="https://yourbusiness.com"
            >
            @error('company_website')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-between">
            <form action="{{ route('onboarding.skip') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-link text-muted">Skip for now</button>
            </form>
            <button type="submit" class="btn btn-primary">
                Continue <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </form>
@endsection