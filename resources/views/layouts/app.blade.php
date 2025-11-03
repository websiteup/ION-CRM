<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <!-- Summernote JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
</head>
<body>
    <div id="app" class="d-flex">
        @auth
            <!-- Sidebar -->
            <div class="sidebar" id="sidebar">
                <div class="sidebar-header">
                    <a href="{{ url('/') }}" class="sidebar-brand">
                        <i class="bi bi-speedometer2"></i>
                        <span class="sidebar-brand-text">{{ config('app.name', 'ION CRM') }}</span>
                    </a>
                    <button class="btn btn-link sidebar-toggle d-md-none" id="sidebarToggle">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                
                <nav class="sidebar-nav">
                    @if(Auth::user()->hasRole('admin'))
                        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('admin.clients.index') }}" class="sidebar-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i>
                            <span>Clienți</span>
                        </a>
                        <a href="{{ route('admin.services.index') }}" class="sidebar-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                            <i class="bi bi-briefcase"></i>
                            <span>Servicii</span>
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="bi bi-person-badge"></i>
                            <span>Utilizatori</span>
                        </a>
                        <a href="{{ route('admin.settings.index') }}" class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                            <i class="bi bi-gear"></i>
                            <span>Setări</span>
                        </a>
                    @endif
                    
                    <div class="sidebar-divider"></div>
                    
                    <a href="{{ route('admin.profile') }}" class="sidebar-link {{ request()->routeIs('admin.profile') ? 'active' : '' }}">
                        <i class="bi bi-person"></i>
                        <span>Profilul Meu</span>
                    </a>
                </nav>
                
                <div class="sidebar-footer">
                    <div class="sidebar-user">
                        <div class="sidebar-user-info">
                            @if(Auth::user()->profile_photo)
                                <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="{{ Auth::user()->name }}" class="sidebar-user-avatar">
                            @else
                                <div class="sidebar-user-avatar-placeholder">
                                    <i class="bi bi-person"></i>
                                </div>
                            @endif
                            <div class="sidebar-user-details">
                                <div class="sidebar-user-name">{{ Auth::user()->name }}</div>
                                <div class="sidebar-user-role">
                                    @if(Auth::user()->hasRole('admin'))
                                        <span class="badge bg-primary">Administrator</span>
                                    @else
                                        <span class="badge bg-secondary">Utilizator</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-link sidebar-user-dropdown" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="bi bi-person me-2"></i> Profilul Meu</a></li>
                                @if(Auth::user()->hasRole('admin'))
                                    <li><a class="dropdown-item" href="{{ route('admin.users.index') }}"><i class="bi bi-people me-2"></i> Utilizatori</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Wrapper -->
            <div class="main-content-wrapper">
                <!-- Top Bar -->
                <nav class="topbar">
                    <div class="topbar-left">
                        <button class="btn btn-link topbar-toggle d-md-none" id="topbarToggle">
                            <i class="bi bi-list"></i>
                        </button>
                        <div class="topbar-title">
                            @php
                                $pageTitle = 'Dashboard';
                                if (request()->routeIs('admin.dashboard')) {
                                    $pageTitle = 'Dashboard';
                                } elseif (request()->routeIs('admin.clients.*')) {
                                    $pageTitle = 'Clienți';
                                } elseif (request()->routeIs('admin.services.*')) {
                                    $pageTitle = 'Servicii';
                                } elseif (request()->routeIs('admin.users.*')) {
                                    $pageTitle = 'Utilizatori';
                                } elseif (request()->routeIs('admin.profile')) {
                                    $pageTitle = 'Profilul Meu';
                                } elseif (request()->routeIs('admin.settings.*')) {
                                    $pageTitle = 'Setări';
                                }
                            @endphp
                            {{ $pageTitle }}
                        </div>
                    </div>
                    <div class="topbar-right">
                        <!-- Reserved for future features -->
                    </div>
                </nav>

                <!-- Main Content -->
                <main class="main-content">
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </main>
            </div>
        @else
            <!-- Guest Layout (Login/Register) -->
            <div class="w-100">
                <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
                    <div class="container">
                        <a class="navbar-brand" href="{{ url('/') }}">
                            {{ config('app.name', 'Laravel') }}
                        </a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav ms-auto">
                                @if (Route::has('login'))
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                    </li>
                                @endif
                                @if (Route::has('register'))
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </nav>

                <main class="py-4">
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </main>
            </div>
        @endauth
    </div>

    @stack('scripts')
    
    <script>
        // Sidebar toggle for mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('topbarToggle');
            const sidebarClose = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.add('show');
                    document.body.style.overflow = 'hidden';
                });
            }
            
            if (sidebarClose) {
                sidebarClose.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    document.body.style.overflow = '';
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth < 768) {
                    if (sidebar && sidebar.classList.contains('show')) {
                        if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                            sidebar.classList.remove('show');
                            document.body.style.overflow = '';
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
