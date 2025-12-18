{{-- resources/views/auth/verify.blade.php --}}
@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
    <div class="text-center mb-4">
        <div class="mb-3">
            <i class="fas fa-envelope-open-text fa-4x text-primary"></i>
        </div>
        <h2 class="auth-title">Verify Your Email</h2>
        <p class="auth-subtitle">We've sent a verification link to your email address</p>
    </div>

    @if(session('resent'))
        <div class="alert alert-success" role="alert">
            A fresh verification link has been sent to your email address.
        </div>
    @endif

    <p class="text-center text-muted mb-4">
        Before proceeding, please check your email for a verification link. 
        If you didn't receive the email, click below to request another.
    </p>

    <form method="POST" action="{{ route('verification.resend') }}" class="text-center">
        @csrf
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-redo me-2"></i>Resend Verification Email
        </button>
    </form>

    <div class="auth-footer mt-4">
        <form action="{{ route('logout') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-link text-muted p-0">
                <i class="fas fa-sign-out-alt me-1"></i> Logout
            </button>
        </form>
    </div>
@endsection