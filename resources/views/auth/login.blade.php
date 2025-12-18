{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <h2 class="auth-title">Welcome Back!</h2>
    <p class="auth-subtitle">Sign in to continue to your dashboard</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

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
                autofocus
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label for="password" class="form-label">Password</label>
                <a href="{{ route('password.request') }}" class="small text-primary text-decoration-none">Forgot Password?</a>
            </div>
            <div class="input-group-password">
                <input 
                    type="password" 
                    class="form-control @error('password') is-invalid @enderror" 
                    id="password" 
                    name="password"
                    placeholder="Enter your password"
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

        <div class="mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">
                    Remember me
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="fas fa-sign-in-alt me-2"></i>Sign In
        </button>

        <div class="auth-divider">
            <span>or</span>
        </div>

        <a href="#" class="btn btn-outline-secondary w-100">
            <i class="fab fa-google me-2"></i>Sign in with Google
        </a>
    </form>

    <div class="auth-footer">
        Don't have an account? <a href="{{ route('register') }}">Sign up</a>
    </div>
@endsection