@extends('layouts.auth')

@section('title', 'Welcome!')

@push('styles')
<style>
    .auth-card { max-width: 550px; text-align: center; }
    
    .celebration-icon {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
        animation: pulse 2s infinite;
    }
    .celebration-icon i { font-size: 3.5rem; color: white; }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    .feature-list {
        text-align: left;
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        margin: 30px 0;
    }
    .feature-item {
        display: flex;
        align-items: center;
        padding: 10px 0;
    }
    .feature-item i {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #22c55e;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        margin-right: 12px;
    }
    
    .confetti {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1000;
    }
</style>
@endpush

@section('content')
<div class="celebration-icon">
    <i class="fas fa-trophy"></i>
</div>

<h2 class="auth-title">Welcome aboard, {{ $user->name }}! 🎉</h2>
<p class="auth-subtitle">Your account is ready. Let's get started!</p>

<div class="feature-list">
    <div class="feature-item">
        <i class="fas fa-check"></i>
        <span>Track leads from Facebook, Instagram & Google Ads</span>
    </div>
    <div class="feature-item">
        <i class="fas fa-check"></i>
        <span>Real-time notifications for new leads</span>
    </div>
    <div class="feature-item">
        <i class="fas fa-check"></i>
        <span>Detailed analytics and reports</span>
    </div>
    <div class="feature-item">
        <i class="fas fa-check"></i>
        <span>Creative management and approval workflow</span>
    </div>
</div>

<div class="d-grid gap-3">
    <a href="{{ route('client.subscription.plans') }}" class="btn btn-primary btn-lg">
        <i class="fas fa-crown me-2"></i> Choose a Plan
    </a>
    <a href="{{ route('client.dashboard') }}" class="btn btn-outline-secondary">
        <i class="fas fa-home me-2"></i> Go to Dashboard
    </a>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
// Confetti celebration
confetti({
    particleCount: 100,
    spread: 70,
    origin: { y: 0.6 }
});

setTimeout(() => {
    confetti({
        particleCount: 50,
        angle: 60,
        spread: 55,
        origin: { x: 0 }
    });
    confetti({
        particleCount: 50,
        angle: 120,
        spread: 55,
        origin: { x: 1 }
    });
}, 500);
</script>
@endpush