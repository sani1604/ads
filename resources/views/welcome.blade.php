@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="mb-3">Grow your business with our Client Portal</h1>
                    <p class="mb-4">
                        See your ads, leads, creatives, and invoices in one simple dashboard.
                    </p>
                    <a href="{{ route('pricing') }}" class="btn btn-light btn-lg me-2">View Pricing</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">Get Started</a>
                </div>
                <div class="col-lg-6">
                    {{-- You can place a hero image or dashboard mockup here --}}
                </div>
            </div>
        </div>
    </section>
@endsection