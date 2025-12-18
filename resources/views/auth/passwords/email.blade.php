{{-- resources/views/auth/passwords/email.blade.php --}}
@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
    <h2 class="auth-title">Forgot Password?</h2>
    <p class="auth-subtitle">No worries, we'll send you reset instructions</p>

    @if(session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
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

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="fas fa-paper-plane me-2"></i>Send Reset Link
        </button>
    </form>

    <div class="auth-footer">
        <a href="{{ route('login') }}"><i class="fas fa-arrow-left me-1"></i> Back to Login</a>
    </div>
@endsection