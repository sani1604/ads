{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard') - {{ config('app.name', 'Agency Portal') }}</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    {{-- Flatpickr (Date Picker) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --sidebar-bg: #0f172a;
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

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 3px;
        }

        .sidebar-brand {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 20px;
            border-bottom: 1px solid #1e293b;
        }

        .sidebar-brand a {
            color: white;
            text-decoration: none;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .sidebar-brand .badge {
            font-size: 0.65rem;
            padding: 4px 8px;
            margin-left: 10px;
        }

        .sidebar-menu {
            padding: 15px 0;
        }

        .sidebar-menu-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 15px 20px 8px;
            color: #475569;
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
            padding: 11px 15px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 0.9rem;
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
            font-size: 0.95rem;
        }

        .sidebar-nav-link .badge {
            margin-left: auto;
            font-size: 0.65rem;
        }

        /* Submenu */
        .sidebar-submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .sidebar-nav-item.open .sidebar-submenu {
            max-height: 500px;
        }

        .sidebar-submenu .sidebar-nav-link {
            padding-left: 47px;
            font-size: 0.85rem;
        }

        .sidebar-nav-link .submenu-arrow {
            margin-left: auto;
            transition: transform 0.3s ease;
        }

        .sidebar-nav-item.open .submenu-arrow {
            transform: rotate(90deg);
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

        .header-search {
            position: relative;
            width: 300px;
        }

        .header-search input {
            width: 100%;
            padding: 8px 16px 8px 40px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .header-search input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .header-search i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-btn {
            background: none;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            cursor: pointer;
            position: relative;
            transition: background 0.2s ease;
        }

        .header-btn:hover {
            background: #f1f5f9;
        }

        .header-btn .badge {
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 0.6rem;
            padding: 3px 5px;
        }

        .user-dropdown .dropdown-toggle {
            display: flex;
            align-items: center;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            border-radius: 8px;
        }

        .user-dropdown .dropdown-toggle:hover {
            background: #f1f5f9;
        }

        .user-dropdown .dropdown-toggle::after {
            display: none;
        }

        .user-dropdown img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .user-dropdown .user-info {
            text-align: left;
        }

        .user-dropdown .user-name {
            font-weight: 500;
            color: #1e293b;
            font-size: 0.9rem;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-header-left h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .page-header .breadcrumb {
            margin: 0;
            padding: 0;
            background: none;
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
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 20px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-body {
            padding: 20px;
        }

        /* Tables */
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
            white-space: nowrap;
        }

        .table td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
        }

        .table tbody tr:hover {
            background: #f8fafc;
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

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        /* Action Buttons */
        .action-btns {
            display: flex;
            gap: 5px;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: white;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .action-btn.danger:hover {
            background: #fef2f2;
            color: #ef4444;
            border-color: #fecaca;
        }

        /* Badges */
        .badge {
            font-weight: 500;
            padding: 5px 10px;
        }

        /* Forms */
        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border-color: #d1d5db;
            padding: 10px 14px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        /* Filter Card */
        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
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

            .header-search {
                display: none;
            }
        }

        @media (max-width: 767.98px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .stat-card-value {
                font-size: 1.5rem;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    {{-- Sidebar --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="{{ route('admin.dashboard') }}">
                <i class="fas fa-rocket me-2"></i>{{ config('app.name', 'Portal') }}
            </a>
            <span class="badge bg-primary">Admin</span>
        </div>

        <div class="sidebar-menu">
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-menu-title">Client Management</div>
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item">
                    <a href="{{ route('admin.clients.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Clients</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('admin.subscriptions.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
                        <i class="fas fa-box"></i>
                        <span>Subscriptions</span>
                        @php $expiringCount = \App\Models\Subscription::expiring(7)->count(); @endphp
                        @if($expiringCount > 0)
                            <span class="badge bg-warning">{{ $expiringCount }}</span>
                        @endif
                    </a>
                </li>
            </ul>

            <div class="sidebar-menu-title">Services</div>
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item">
                    <a href="{{ route('admin.creatives.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.creatives.*') ? 'active' : '' }}">
                        <i class="fas fa-paint-brush"></i>
                        <span>Creatives</span>
                        @php $pendingCreatives = \App\Models\Creative::pendingApproval()->count(); @endphp
                        @if($pendingCreatives > 0)
                            <span class="badge bg-warning">{{ $pendingCreatives }}</span>
                        @endif
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('admin.leads.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.leads.*') ? 'active' : '' }}">
                        <i class="fas fa-user-plus"></i>
                        <span>Leads</span>
                        @php $todayLeads = \App\Models\Lead::today()->count(); @endphp
                        @if($todayLeads > 0)
                            <span class="badge bg-success">{{ $todayLeads }}</span>
                        @endif
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('admin.campaign-reports.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.campaign-reports.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i>
                        <span>Campaign Reports</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-menu-title">Billing</div>
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item">
                    <a href="{{ route('admin.invoices.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Invoices</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('admin.transactions.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Transactions</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="{{ route('admin.packages.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                        <i class="fas fa-cubes"></i>
                        <span>Packages</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-menu-title">Support</div>
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item">
                    <a href="{{ route('admin.support-tickets.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.support-tickets.*') ? 'active' : '' }}">
                        <i class="fas fa-headset"></i>
                        <span>Support Tickets</span>
                        @php $openTickets = \App\Models\SupportTicket::open()->count(); @endphp
                        @if($openTickets > 0)
                            <span class="badge bg-danger">{{ $openTickets }}</span>
                        @endif
                    </a>
                </li>
            </ul>

            @if(auth()->user()->isAdmin())
                <div class="sidebar-menu-title">Administration</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.users.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="fas fa-user-shield"></i>
                            <span>Staff Users</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.service-categories.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.service-categories.*') ? 'active' : '' }}">
                            <i class="fas fa-layer-group"></i>
                            <span>Service Categories</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.industries.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.industries.*') ? 'active' : '' }}">
                            <i class="fas fa-industry"></i>
                            <span>Industries</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.webhooks.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.webhooks.*') ? 'active' : '' }}">
                            <i class="fas fa-plug"></i>
                            <span>Webhooks</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.activity-logs.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
                            <i class="fas fa-history"></i>
                            <span>Activity Logs</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.settings.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            @endif
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
                <div class="header-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search clients, leads, tickets..." id="globalSearch">
                </div>
            </div>

            <div class="header-right">
                {{-- Quick Actions --}}
                <div class="dropdown">
                    <button class="header-btn" data-bs-toggle="dropdown" title="Quick Actions">
                        <i class="fas fa-plus"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('admin.clients.create') }}"><i class="fas fa-user-plus me-2"></i>Add Client</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.leads.create') }}"><i class="fas fa-bullseye me-2"></i>Add Lead</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.creatives.create') }}"><i class="fas fa-paint-brush me-2"></i>Upload Creative</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('admin.invoices.create') }}"><i class="fas fa-file-invoice me-2"></i>Create Invoice</a></li>
                    </ul>
                </div>

                {{-- Notifications --}}
                <div class="dropdown">
                    <button class="header-btn" data-bs-toggle="dropdown" title="Notifications">
                        <i class="fas fa-bell"></i>
                        @php
                            $adminNotifications = \App\Models\SupportTicket::open()->count() + 
                                                  \App\Models\Creative::pendingApproval()->count();
                        @endphp
                        @if($adminNotifications > 0)
                            <span class="badge bg-danger">{{ $adminNotifications }}</span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0" style="width: 320px;">
                        <div class="p-3 border-bottom">
                            <h6 class="mb-0">Notifications</h6>
                        </div>
                        <div style="max-height: 300px; overflow-y: auto;">
                            @php $pendingCreatives = \App\Models\Creative::with('user')->pendingApproval()->latest()->take(3)->get(); @endphp
                            @foreach($pendingCreatives as $creative)
                                <a href="{{ route('admin.creatives.show', $creative) }}" class="dropdown-item py-3">
                                    <div class="d-flex">
                                        <div class="me-3 text-warning">
                                            <i class="fas fa-paint-brush"></i>
                                        </div>
                                        <div>
                                            <strong>Creative Pending</strong>
                                            <small class="text-muted d-block">{{ $creative->title }} - {{ $creative->user->name }}</small>
                                        </div>
                                    </div>
                                </a>
                            @endforeach

                            @php $openTickets = \App\Models\SupportTicket::with('user')->open()->latest()->take(3)->get(); @endphp
                            @foreach($openTickets as $ticket)
                                <a href="{{ route('admin.support-tickets.show', $ticket) }}" class="dropdown-item py-3">
                                    <div class="d-flex">
                                        <div class="me-3 text-danger">
                                            <i class="fas fa-ticket-alt"></i>
                                        </div>
                                        <div>
                                            <strong>{{ $ticket->ticket_number }}</strong>
                                            <small class="text-muted d-block">{{ Str::limit($ticket->subject, 30) }}</small>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- User Dropdown --}}
                <div class="dropdown user-dropdown">
                    <button class="dropdown-toggle" data-bs-toggle="dropdown">
                        <img src="{{ auth()->user()->avatar_url }}" alt="">
                        <div class="user-info d-none d-md-block">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            <span class="user-role d-block">{{ ucfirst(auth()->user()->role) }}</span>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.activity-logs.index') }}"><i class="fas fa-history me-2"></i>Activity Log</a></li>
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

    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    {{-- Select2 --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Flatpickr --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Common Scripts --}}
    <script>
        // Sidebar Toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        });

        document.getElementById('sidebarOverlay')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('show');
            this.classList.remove('show');
        });

        // Initialize Select2
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5'
            });
        });

        // Initialize Flatpickr
        document.querySelectorAll('.datepicker').forEach(function(el) {
            flatpickr(el, {
                dateFormat: 'Y-m-d'
            });
        });

        document.querySelectorAll('.daterange').forEach(function(el) {
            flatpickr(el, {
                mode: 'range',
                dateFormat: 'Y-m-d'
            });
        });

        // Confirm Delete
        function confirmDelete(formId, message = 'Are you sure you want to delete this?') {
            if (confirm(message)) {
                document.getElementById(formId).submit();
            }
        }

        // CSRF Token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
@vite(['resources/js/app-dashboard.js'])
    @stack('scripts')
</body>
</html>