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
    
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

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
                    
                    @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('manager'))
                        <a href="{{ route('admin.projects.index') }}" class="sidebar-link {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">
                            <i class="bi bi-folder"></i>
                            <span>Proiecte</span>
                        </a>
                        <a href="{{ route('admin.boards.index') }}" class="sidebar-link {{ request()->routeIs('admin.boards.*') ? 'active' : '' }}">
                            <i class="bi bi-kanban"></i>
                            <span>Board-uri</span>
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
                                } elseif (request()->routeIs('admin.boards.*')) {
                                    $pageTitle = request()->routeIs('admin.boards.view') ? 'Board' : 'Board-uri';
                                } elseif (request()->routeIs('admin.projects.*')) {
                                    $pageTitle = request()->routeIs('admin.projects.view') ? 'Proiect' : 'Proiecte';
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

    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    
    <script>
        // Toastify helper function
        function showToastify(type, message) {
            const colors = {
                success: 'linear-gradient(to right, #00b09b, #96c93d)',
                error: 'linear-gradient(to right, #ff5f6d, #ffc371)',
                info: 'linear-gradient(to right, #4facfe, #00f2fe)',
                warning: 'linear-gradient(to right, #f093fb, #f5576c)'
            };
            
            Toastify({
                text: message,
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                stopOnFocus: true,
                style: {
                    background: colors[type] || colors.info,
                }
            }).showToast();
        }

        // Show toast from session flash
        @if(session('toastify'))
            showToastify('{{ session('toastify.type') }}', {!! json_encode(session('toastify.message')) !!});
        @endif

        // Listen for Livewire events
        document.addEventListener('livewire:init', () => {
            Livewire.on('toastify', (data) => {
                showToastify(data[0].type, data[0].message);
            });
        });

        // Legacy Livewire 2.x support
        window.addEventListener('toastify', event => {
            showToastify(event.detail.type, event.detail.message);
        });

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
    
    @stack('scripts')
</body>
</html>
