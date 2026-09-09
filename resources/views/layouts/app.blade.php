<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Portal') - Real Estate CRM V0.4</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/crm.css">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column justify-content-between">
        <div>
            <div class="p-3 d-flex align-items-center gap-2 border-bottom border-secondary border-opacity-25">
                <div class="bg-primary text-white rounded p-2 d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                    <i class="bi bi-building-fill fs-4"></i>
                </div>
                <div>
                    <h6 class="mb-0 text-white brand-font fw-bold">PropFlow CRM</h6>
                    <small class="text-secondary" style="font-size:0.75rem;">v0.4 Sales & Negotiation</small>
                </div>
            </div>
            
            <nav class="nav flex-column mt-3">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>

                <div class="px-3 my-2 text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Sales & Pipeline</div>
                @if(auth()->user()->hasPermissionTo('leads.view'))
                    <a class="nav-link {{ request()->routeIs('leads.index') || request()->routeIs('leads.show') || request()->routeIs('leads.create') || request()->routeIs('leads.edit') ? 'active' : '' }}" href="{{ route('leads.index') }}">
                        <i class="bi bi-person-lines-fill"></i> Lead Pipeline
                    </a>
                    <a class="nav-link {{ request()->routeIs('site-visits.*') ? 'active' : '' }}" href="{{ route('site-visits.index') }}">
                        <i class="bi bi-geo-alt-fill"></i> Site Visits Logistics
                    </a>
                    <a class="nav-link {{ request()->routeIs('offers.*') ? 'active' : '' }}" href="{{ route('offers.index') }}">
                        <i class="bi bi-tag-fill"></i> Offers & Negotiation
                    </a>
                    <a class="nav-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}" href="{{ route('calendar.index') }}">
                        <i class="bi bi-calendar3"></i> Unified Sales Calendar
                    </a>
                    <a class="nav-link {{ request()->routeIs('followups.*') ? 'active' : '' }}" href="{{ route('followups.index') }}">
                        <i class="bi bi-calendar-check-fill"></i> Follow-ups Schedule
                    </a>
                    <a class="nav-link {{ request()->routeIs('leads.duplicates') ? 'active' : '' }}" href="{{ route('leads.duplicates') }}">
                        <i class="bi bi-intersect"></i> Duplicate Checker
                    </a>
                    <a class="nav-link {{ request()->routeIs('leads.import.*') ? 'active' : '' }}" href="{{ route('leads.import.form') }}">
                        <i class="bi bi-file-earmark-arrow-up-fill"></i> CSV Import / Export
                    </a>
                    @if(auth()->user()->isAdmin())
                        <a class="nav-link {{ request()->routeIs('lead-config.*') ? 'active' : '' }}" href="{{ route('lead-config.index') }}">
                            <i class="bi bi-gear-wide-connected"></i> Sources & Statuses
                        </a>
                    @endif
                @endif

                <div class="px-3 my-2 text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Project & Inventory</div>
                @if(auth()->user()->hasPermissionTo('projects.view'))
                    <a class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}" href="{{ route('projects.index') }}">
                        <i class="bi bi-buildings-fill"></i> Projects Portal
                    </a>
                @endif

                @if(auth()->user()->hasPermissionTo('inventory.view'))
                    <a class="nav-link {{ request()->routeIs('inventory.grid') ? 'active' : '' }}" href="{{ route('inventory.grid') }}">
                        <i class="bi bi-distribute-vertical"></i> Inventory Visual Grid
                    </a>
                    <a class="nav-link {{ request()->routeIs('unit-types.*') ? 'active' : '' }}" href="{{ route('unit-types.index') }}">
                        <i class="bi bi-sliders2"></i> Configurable Unit Types
                    </a>
                @endif

                <div class="px-3 my-2 text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Core Administration</div>
                
                @if(auth()->user()->hasPermissionTo('users.view'))
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                        <i class="bi bi-people-fill"></i> User Accounts
                    </a>
                @endif

                <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                    <i class="bi bi-shield-lock-fill"></i> Roles & Permissions
                </a>

                <a class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}" href="{{ route('companies.index') }}">
                    <i class="bi bi-building"></i> Corporate Profile
                </a>

                <a class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}" href="{{ route('audit-logs.index') }}">
                    <i class="bi bi-journal-text"></i> Audit Logs
                </a>

                <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                    <i class="bi bi-gear-fill"></i> System Settings
                </a>

                <div class="px-3 my-2 text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Finance & Closures</div>
                @if(auth()->user()->hasPermissionTo('bookings.view'))
                    <a class="nav-link {{ request()->routeIs('bookings.*') ? 'active' : '' }}" href="{{ route('bookings.index') }}">
                        <i class="bi bi-file-earmark-check-fill"></i> Unit Bookings
                    </a>
                @endif

                @if(auth()->user()->hasPermissionTo('payments.view'))
                    <a class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}">
                        <i class="bi bi-receipt"></i> Payment Receipts
                    </a>
                @endif
            </nav>
        </div>

        <div class="p-3 border-top border-secondary border-opacity-25">
            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('profile.edit') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div style="line-height:1.2;">
                        <div class="text-white fw-bold small">{{ auth()->user()->name }}</div>
                        <small class="text-capitalize text-secondary" style="font-size:0.7rem;">{{ str_replace('_', ' ', auth()->user()->role) }}</small>
                    </div>
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light border-0" title="Logout">
                        <i class="bi bi-box-arrow-right fs-5 text-secondary"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Top Navbar -->
    <div class="top-navbar d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-0 brand-font">@yield('page-title', 'Dashboard')</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size: 0.78rem;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active">@yield('title', 'Dashboard')</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-light text-dark border px-3 py-2">
                <i class="bi bi-building text-primary me-1"></i> {{ auth()->user()->company->name ?? 'Default Builder' }}
            </span>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i> Please fix the validation errors below:
                <ul class="mb-0 mt-1 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
