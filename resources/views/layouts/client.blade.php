{{-- resources/views/layouts/client.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Agency Portal') }}</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Custom Dashboard Styles --}}
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --sidebar-bg: #1e293b;
            --sidebar-width: 260px;
            --header-height: 65px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            color: #94a3b8;
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-brand {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 20px;
            border-bottom: 1px solid #334155;
        }

        .sidebar-brand a {
            color: white;
            text-decoration: none;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 10px 20px;
            color: #64748b;
            font-weight: 600;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav-item {
            margin: 2px 10px;
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .sidebar-nav-link:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .sidebar-nav-link.active {
            background: var(--primary-color);
            color: white;
        }

        .sidebar-nav-link i {
            width: 20px;
            margin-right: 12px;
            text-align: center;
        }

        .sidebar-nav-link .badge {
            margin-left: auto;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Top Header */
        .top-header {
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #64748b;
            cursor: pointer;
            display: none;
            margin-right: 15px;
        }

        .page-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-wallet {
            display: flex;
            align-items: center;
            background: #f1f5f9;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
        }

        .header-wallet i {
            color: var(--primary-color);
            margin-right: 8px;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #64748b;
            cursor: pointer;
        }

        .notification-btn .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.65rem;
        }

        .user-dropdown .dropdown-toggle {
            display: flex;
            align-items: center;
            background: none;
            border: none;
            cursor: pointer;
        }

        .user-dropdown .dropdown-toggle::after {
            margin-left: 8px;
        }

        .user-dropdown img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .user-dropdown .user-name {
            font-weight: 500;
            color: #1e293b;
        }

        .user-dropdown .user-role {
            font-size: 0.75rem;
            color: #64748b;
        }

        /* Content Area */
        .content-area {
            padding: 24px;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 24px;
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .page-header .breadcrumb {
            margin: 0;
            padding: 0;
            background: none;
        }

        .page-header .breadcrumb-item {
            font-size: 0.875rem;
        }

        .page-header .breadcrumb-item a {
            color: #64748b;
            text-decoration: none;
        }

        .page-header .breadcrumb-item.active {
            color: #94a3b8;
        }

        
        /* Stats Cards */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-card-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1;
        }

        .stat-card-label {
            color: #64748b;
            font-size: 0.875rem;
            margin-top: 5px;
        }

        .stat-card-change {
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
        }

        .stat-card-change.positive {
            color: #22c55e;
        }

        .stat-card-change.negative {
            color: #ef4444;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 20px;
            font-weight: 600;
        }


        /* Buttons */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        /* Tables */
        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
        }

        .table {
            margin: 0;
        }

        .table th {
            background: #f8fafc;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .table td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: block;
            }

            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                display: none;
            }

            .sidebar-overlay.show {
                display: block;
            }
        }

        /* Impersonation Banner */
        .impersonation-banner {
            background: #fef3c7;
            color: #92400e;
            padding: 10px 20px;
            text-align: center;
            font-weight: 500;
        }

        .impersonation-banner a {
            color: #92400e;
            text-decoration: underline;
        }
    </style>

    @stack('styles')
</head>
<body>
    {{-- Impersonation Banner --}}
    @if(session('impersonator_id'))
        <div class="impersonation-banner">
            <i class="fas fa-user-secret me-2"></i>
            You are currently logged in as {{ auth()->user()->name }}.
            <a href="{{ route('stop-impersonation') }}">Return to Admin Account</a>
        </div>
    @endif

    {{-- Sidebar --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="{{ route('client.dashboard') }}">
                <i class="fas fa-rocket me-2"></i>{{ config('app.name', 'Agency Portal') }}
            </a>
        </div>

        <div class="sidebar-menu">
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item">
                    <a href="{{ route('client.dashboard') }}" class="sidebar-nav-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-menu-title">Services</div>
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item">
                    <a href="{{ route('client.leads.index') }}" class="sidebar-nav-link {{ request()->routeIs('client.leads.*') ? 'active' : '' }}">
                        <i class="fas fa-user-plus"></i>
                        <span>Leads</span>
                        @if(auth()->user()->leads()->new()->count() > 0)
                            <span class="badge bg-success">{{ auth()->user()->leads()->new()->count() }}</span>
                        @endif
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('client.creatives.index') }}" class="sidebar-nav-link {{ request()->routeIs('client.creatives.*') ? 'active' : '' }}">
                        <i class="fas fa-paint-brush"></i>
                        <span>Creatives</span>
                        @php
                            $pendingCount = auth()->user()->creatives()->whereIn('status', ['pending_approval', 'changes_requested'])->count();
                        @endphp
                        @if($pendingCount > 0)
                            <span class="badge bg-warning">{{ $pendingCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('client.reports.index') }}" class="sidebar-nav-link {{ request()->routeIs('client.reports.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <span>Reports</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-menu-title">Billing</div>
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item">
                    <a href="{{ route('client.subscription.index') }}" class="sidebar-nav-link {{ request()->routeIs('client.subscription.*') ? 'active' : '' }}">
                        <i class="fas fa-box"></i>
                        <span>Subscription</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('client.wallet.index') }}" class="sidebar-nav-link {{ request()->routeIs('client.wallet.*') ? 'active' : '' }}">
                        <i class="fas fa-wallet"></i>
                        <span>Wallet</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('client.invoices.index') }}" class="sidebar-nav-link {{ request()->routeIs('client.invoices.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice"></i>
                        <span>Invoices</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-menu-title">Support</div>
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item">
                    <a href="{{ route('client.support.index') }}" class="sidebar-nav-link {{ request()->routeIs('client.support.*') ? 'active' : '' }}">
                        <i class="fas fa-headset"></i>
                        <span>Support Tickets</span>
                        @php
                            $openTickets = auth()->user()->supportTickets()->open()->count();
                        @endphp
                        @if($openTickets > 0)
                            <span class="badge bg-info">{{ $openTickets }}</span>
                        @endif
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('client.profile.edit') }}" class="sidebar-nav-link {{ request()->routeIs('client.profile.*') ? 'active' : '' }}">
                        <i class="fas fa-user-cog"></i>
                        <span>Profile Settings</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    {{-- Sidebar Overlay (Mobile) --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- Main Content --}}
    <div class="main-content">
        {{-- Top Header --}}
        <header class="top-header">
            <div class="header-left">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
            </div>

            <div class="header-right">
                {{-- Wallet Balance --}}
                <div class="header-wallet">
                    <i class="fas fa-wallet"></i>
                    <span>{{ auth()->user()->formatted_wallet_balance }}</span>
                </div>

                {{-- Notifications --}}
                <div class="dropdown">
                    <button class="notification-btn" data-bs-toggle="dropdown" id="notificationDropdown">
                        <i class="fas fa-bell"></i>
                        @if(auth()->user()->getUnreadNotificationsCount() > 0)
                            <span class="badge bg-danger">{{ auth()->user()->getUnreadNotificationsCount() }}</span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0" style="width: 320px;">
                        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Notifications</h6>
                            <a href="{{ route('client.notifications.mark-all-read') }}" class="text-primary small" 
                               onclick="event.preventDefault(); document.getElementById('mark-all-read-form').submit();">
                                Mark all as read
                            </a>
                            <form id="mark-all-read-form" action="{{ route('client.notifications.mark-all-read') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                        <div style="max-height: 300px; overflow-y: auto;">
                            @forelse(auth()->user()->notifications()->latest()->take(5)->get() as $notification)
                                <a href="{{ $notification->action_url ?? '#' }}" class="dropdown-item py-3 {{ !$notification->is_read ? 'bg-light' : '' }}">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <i class="{{ $notification->icon_class }} fa-lg"></i>
                                        </div>
                                        <div>
                                            <strong class="d-block">{{ $notification->title }}</strong>
                                            <small class="text-muted">{{ Str::limit($notification->message, 50) }}</small>
                                            <small class="text-muted d-block">{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="p-3 text-center text-muted">
                                    <i class="fas fa-bell-slash fa-2x mb-2"></i>
                                    <p class="mb-0">No notifications</p>
                                </div>
                            @endforelse
                        </div>
                        <div class="p-2 border-top text-center">
                            <a href="{{ route('client.notifications.index') }}" class="text-primary">View all notifications</a>
                        </div>
                    </div>
                </div>

                {{-- User Dropdown --}}
                <div class="dropdown user-dropdown">
                    <button class="dropdown-toggle" data-bs-toggle="dropdown">
                        <img src="{{ auth()->user()->avatar_url }}" alt="">
                        <div class="text-start d-none d-md-block">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            <span class="user-role d-block">{{ auth()->user()->company_name ?? 'Client' }}</span>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('client.profile.edit') }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('client.subscription.index') }}"><i class="fas fa-box me-2"></i>Subscription</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        {{-- Content Area --}}
        <div class="content-area">
            {{-- Flash Messages --}}
            @include('components.flash-messages')

            {{-- Page Content --}}
            @yield('content')
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Sidebar Toggle Script --}}
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        });

        document.getElementById('sidebarOverlay')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('show');
            this.classList.remove('show');
        });
    </script>
@vite(['resources/js/app-dashboard.js'])
    @stack('scripts')
</body>
</html>