{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.auth')

@section('title', 'Register')

@section('content')
    <h2 class="auth-title">Create Account</h2>
    <p class="auth-subtitle">Start your digital marketing journey today</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input 
                type="text" 
                class="form-control @error('name') is-invalid @enderror" 
                id="name" 
                name="name" 
                value="{{ old('name') }}"
                placeholder="John Doe"
                required 
                autofocus
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input 
                type="email" 
                class="form-control @error('email') is-invalid @enderror" 
                id="email" 
                name="email" 
                value="{{ old('email') }}"
                placeholder="you@example.com"
                required
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone Number</label>
            <div class="input-group">
                <span class="input-group-text">+91</span>
                <input 
                    type="tel" 
                    class="form-control @error('phone') is-invalid @enderror" 
                    id="phone" 
                    name="phone" 
                    value="{{ old('phone') }}"
                    placeholder="9999999999"
                    pattern="[6-9][0-9]{9}"
                    maxlength="10"
                    required
                >
            </div>
            @error('phone')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="company_name" class="form-label">Company Name <span class="text-muted">(Optional)</span></label>
            <input 
                type="text" 
                class="form-control @error('company_name') is-invalid @enderror" 
                id="company_name" 
                name="company_name" 
                value="{{ old('company_name') }}"
                placeholder="Your Company"
            >
            @error('company_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="industry_id" class="form-label">Industry <span class="text-muted">(Optional)</span></label>
            <select class="form-select @error('industry_id') is-invalid @enderror" id="industry_id" name="industry_id">
                <option value="">Select your industry</option>
                @foreach($industries as $industry)
                    <option value="{{ $industry->id }}" {{ old('industry_id') == $industry->id ? 'selected' : '' }}>
                        {{ $industry->name }}
                    </option>
                @endforeach
            </select>
            @error('industry_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group-password">
                <input 
                    type="password" 
                    class="form-control @error('password') is-invalid @enderror" 
                    id="password" 
                    name="password"
                    placeholder="Min. 8 characters"
                    minlength="8"
                    required
                >
                <button type="button" class="password-toggle">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <div class="input-group-password">
                <input 
                    type="password" 
                    class="form-control" 
                    id="password_confirmation" 
                    name="password_confirmation"
                    placeholder="Confirm your password"
                    required
                >
                <button type="button" class="password-toggle">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <div class="mb-4">
            <div class="form-check">
                <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" name="terms" id="terms" required>
                <label class="form-check-label" for="terms">
                    I agree to the <a href="#" class="text-primary">Terms of Service</a> and <a href="#" class="text-primary">Privacy Policy</a>
                </label>
            </div>
            @error('terms')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="fas fa-user-plus me-2"></i>Create Account
        </button>
    </form>

    <div class="auth-footer">
        Already have an account? <a href="{{ route('login') }}">Sign in</a>
    </div>
@endsection