{{-- resources/views/auth/passwords/reset.blade.php --}}
@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
    <h2 class="auth-title">Reset Password</h2>
    <p class="auth-subtitle">Enter your new password below</p>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input 
                type="email" 
                class="form-control @error('email') is-invalid @enderror" 
                id="email" 
                name="email" 
                value="{{ $email ?? old('email') }}"
                required 
                autofocus
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <div class="input-group-password">
                <input 
                    type="password" 
                    class="form-control @error('password') is-invalid @enderror" 
                    id="password" 
                    name="password"
                    placeholder="Min. 8 characters"
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
            <label for="password_confirmation" class="form-label">Confirm New Password</label>
            <div class="input-group-password">
                <input 
                    type="password" 
                    class="form-control" 
                    id="password_confirmation" 
                    name="password_confirmation"
                    placeholder="Confirm password"
                    required
                >
                <button type="button" class="password-toggle">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-key me-2"></i>Reset Password
        </button>
    </form>
@endsection