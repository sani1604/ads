<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --primary-color: #6366f1;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #5558e3;
            border-color: #5558e3;
        }
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        a {
            color: var(--primary-color);
        }
        .footer {
            background: #1e293b;
            color: #94a3b8;
            padding: 60px 0 30px;
        }
        .footer a {
            color: #94a3b8;
            text-decoration: none;
        }
        .footer a:hover {
            color: #fff;
        }
        .footer h5 {
            color: #fff;
            font-size: 1rem;
            margin-bottom: 1rem;
        }
    </style>
    @stack('styles')
</head>
<body>
    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand text-primary" href="{{ url('/') }}">
                <i class="fas fa-bolt me-2"></i>{{ config('app.name') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('contact') }}">Contact</a>
                    </li>
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item ms-2">
                            <a class="btn btn-primary" href="{{ route('register') }}">Get Started</a>
                        </li>
                    @else
                        <li class="nav-item ms-2">
                            <a class="btn btn-primary" href="{{ route('client.dashboard') }}">
                                <i class="fas fa-home me-1"></i> Dashboard
                            </a>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="footer mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h4 class="text-white mb-3">
                        <i class="fas fa-bolt me-2"></i>{{ config('app.name') }}
                    </h4>
                    <p>Simplify your lead management and grow your business with our powerful platform.</p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="fs-5"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="fs-5"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="fs-5"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="fs-5"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h5>Product</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#">Features</a></li>
                        <li class="mb-2"><a href="#">Pricing</a></li>
                        <li class="mb-2"><a href="#">Integrations</a></li>
                        <li class="mb-2"><a href="#">API</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h5>Company</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#">About Us</a></li>
                        <li class="mb-2"><a href="#">Careers</a></li>
                        <li class="mb-2"><a href="#">Blog</a></li>
                        <li class="mb-2"><a href="{{ route('contact') }}">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h5>Legal</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('terms') }}">Terms of Service</a></li>
                        <li class="mb-2"><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                        <li class="mb-2"><a href="{{ route('refund') }}">Refund Policy</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h5>Support</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#">Help Center</a></li>
                        <li class="mb-2"><a href="#">Documentation</a></li>
                        <li class="mb-2"><a href="#">Status</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 border-secondary">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Made with <i class="fas fa-heart text-danger"></i> in India</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>