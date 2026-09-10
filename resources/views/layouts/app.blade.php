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

                <div class="px-3 my-2 text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Marketing & Automation</div>
                <a class="nav-link {{ request()->routeIs('communication.templates.*') ? 'active' : '' }}" href="{{ route('communication.templates.index') }}">
                    <i class="bi bi-file-earmark-code"></i> Templates Engine
                </a>
                <a class="nav-link {{ request()->routeIs('communication.campaigns.*') ? 'active' : '' }}" href="{{ route('communication.campaigns.index') }}">
                    <i class="bi bi-megaphone-fill"></i> Marketing Campaigns
                </a>
                <a class="nav-link {{ request()->routeIs('communication.automations.*') ? 'active' : '' }}" href="{{ route('communication.automations.index') }}">
                    <i class="bi bi-lightning-charge-fill"></i> Event Automations
                </a>
                <a class="nav-link {{ request()->routeIs('communication.logs.*') ? 'active' : '' }}" href="{{ route('communication.logs.index') }}">
                    <i class="bi bi-envelope-paper"></i> Outbound Logs
                </a>
                <a class="nav-link {{ request()->routeIs('communication.optouts.*') ? 'active' : '' }}" href="{{ route('communication.optouts.index') }}">
                    <i class="bi bi-slash-circle-fill"></i> Customer Opt-Outs
                </a>

                <div class="px-3 my-2 text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Brokers & Channel Partners</div>
                <a class="nav-link {{ request()->routeIs('brokers.partners.*') ? 'active' : '' }}" href="{{ route('brokers.partners.index') }}">
                    <i class="bi bi-person-badge-fill"></i> Partner Registry
                </a>
                <a class="nav-link {{ request()->routeIs('brokers.rules.*') ? 'active' : '' }}" href="{{ route('brokers.rules.index') }}">
                    <i class="bi bi-diagram-3-fill"></i> Commission Schemes
                </a>
                <a class="nav-link {{ request()->routeIs('brokers.commissions.*') ? 'active' : '' }}" href="{{ route('brokers.commissions.index') }}">
                    <i class="bi bi-cash-coin"></i> Commissions & Payouts
                </a>
                <a class="nav-link {{ request()->routeIs('brokers.reports.*') ? 'active' : '' }}" href="{{ route('brokers.reports.index') }}">
                    <i class="bi bi-graph-up-arrow"></i> Partner Performance
                </a>

                <div class="px-3 my-2 text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Reporting & Analytics</div>
                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <i class="bi bi-bar-chart-line-fill"></i> Central Reports Hub
                </a>
            </nav>
        </div>

        <div class="p-3 border-top border-secondary border-opacity-25">
            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('profile.edit') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:36px; height:36px; font-size: 0.9rem;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div style="line-height:1.2;">
                        <div class="text-white fw-bold small">{{ auth()->user()->name }}</div>
                        <small class="text-capitalize text-secondary" style="font-size:0.7rem;">{{ str_replace('_', ' ', auth()->user()->role) }}</small>
                    </div>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger border-0 d-flex align-items-center gap-1 text-danger p-1" title="Logout">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Mobile Sidebar Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- Top Navbar -->
    <div class="top-navbar d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-light border d-lg-none p-1 px-2 shadow-sm me-1" type="button" id="sidebarToggle" title="Toggle Navigation Menu">
                <i class="bi bi-list fs-3 text-dark"></i>
            </button>
            <div>
                <h5 class="mb-0 brand-font">@yield('page-title', 'Dashboard')</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.78rem;">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active">@yield('title', 'Dashboard')</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 gap-md-3 ms-auto">
            <!-- Global CRM Search Input -->
            <div class="position-relative" style="max-width: 240px;" x-data="{ query: '', results: [], loading: false, show: false }" @click.outside="show = false">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" x-model="query" @input.debounce.300ms="
                        if (query.length >= 2) {
                            loading = true; show = true;
                            fetch('/search?q=' + encodeURIComponent(query))
                                .then(res => res.json())
                                .then(data => { results = data.results; loading = false; });
                        } else { results = []; show = false; }
                    " placeholder="Search..." class="form-control border-start-0 bg-white">
                </div>

                <!-- Live Search Dropdown -->
                <div x-show="show" x-cloak class="position-absolute start-0 end-0 bg-white shadow-lg rounded-3 border mt-1 p-2" style="z-index: 1050; max-height: 350px; overflow-y: auto;">
                    <template x-if="loading">
                        <div class="text-center py-2 text-secondary small"><span class="spinner-border spinner-border-sm me-1"></span> Searching CRM records...</div>
                    </template>
                    <template x-if="!loading && results.length === 0">
                        <div class="text-center py-2 text-secondary small">No matching CRM records found.</div>
                    </template>
                    <template x-for="item in results" :key="item.url">
                        <a :href="item.url" class="d-flex align-items-center gap-2 p-2 text-decoration-none text-dark border-bottom border-light hover-bg-light rounded">
                            <div class="p-2 rounded bg-light text-primary"><i class="bi" :class="item.icon"></i></div>
                            <div class="flex-grow-1" style="line-height: 1.2;">
                                <div class="fw-bold small" x-text="item.title"></div>
                                <small class="text-muted" style="font-size:0.75rem;" x-text="item.subtitle"></small>
                            </div>
                            <span class="badge" :class="item.badge_class" style="font-size:0.65rem;" x-text="item.badge"></span>
                        </a>
                    </template>
                </div>
            </div>

            <span class="badge bg-light text-dark border px-2 px-md-3 py-2 d-none d-sm-inline-block">
                <i class="bi bi-building text-primary me-1"></i> {{ auth()->user()->company->name ?? 'Default Builder' }}
            </span>

            <!-- Top Navbar User Dropdown -->
            <div class="dropdown">
                <button class="btn btn-light btn-sm border d-flex align-items-center gap-2 dropdown-toggle px-2 py-1 text-dark shadow-sm" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:30px; height:30px; font-size: 0.85rem;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="text-start d-none d-md-block" style="line-height:1.1;">
                        <div class="fw-bold small">{{ auth()->user()->name }}</div>
                        <small class="text-muted" style="font-size:0.65rem;">{{ str_replace('_', ' ', auth()->user()->role) }}</small>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-2" aria-labelledby="userMenuDropdown" style="min-width: 210px; z-index: 1060;">
                    <li class="px-2 py-2 bg-light rounded mb-2">
                        <div class="fw-bold text-dark small">{{ auth()->user()->name }}</div>
                        <small class="text-muted" style="font-size: 0.75rem;">{{ auth()->user()->email }}</small>
                    </li>
                    <li>
                        <a class="dropdown-item rounded small d-flex align-items-center gap-2 py-2" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person-gear text-primary"></i> Edit Profile
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item rounded small d-flex align-items-center gap-2 py-2 text-danger fw-semibold">
                                <i class="bi bi-box-arrow-right text-danger fs-6"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');

            if (toggleBtn && sidebar && backdrop) {
                function openSidebar() {
                    sidebar.classList.add('show');
                    backdrop.classList.add('show');
                    document.body.style.overflow = 'hidden';
                }
                function closeSidebar() {
                    sidebar.classList.remove('show');
                    backdrop.classList.remove('show');
                    document.body.style.overflow = '';
                }

                toggleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (sidebar.classList.contains('show')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });

                backdrop.addEventListener('click', closeSidebar);

                sidebar.querySelectorAll('.nav-link').forEach(link => {
                    link.addEventListener('click', function() {
                        if (window.innerWidth < 992) {
                            closeSidebar();
                        }
                    });
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
