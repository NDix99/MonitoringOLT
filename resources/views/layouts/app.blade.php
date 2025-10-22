<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - OLT Monitoring</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar.collapsed {
            width: 70px;
        }
        
        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-brand {
            color: white;
            text-decoration: none;
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .sidebar-brand i {
            margin-right: 0.5rem;
            font-size: 1.5rem;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-item {
            margin: 0.25rem 0;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 0;
        }
        
        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            border-right: 3px solid white;
        }
        
        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
            text-align: center;
        }
        
        .sidebar.collapsed .nav-link span {
            display: none;
        }
        
        .sidebar.collapsed .nav-link {
            justify-content: center;
        }
        
        .sidebar.collapsed .nav-link i {
            margin-right: 0;
        }
        
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: 70px;
        }
        
        .top-bar {
            background: white;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: between;
            align-items: center;
        }
        
        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #6c757d;
            cursor: pointer;
        }
        
        .user-dropdown {
            margin-left: auto;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ url('/') }}" class="sidebar-brand">
                <i class="fas fa-server"></i>
                <span>OLT Monitoring</span>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ url('/dashboard') }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('onus') ? 'active' : '' }}" href="{{ route('onus') }}">
                        <i class="fas fa-network-wired"></i>
                        <span>All ONUs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('ssh-settings') ? 'active' : '' }}" href="{{ route('ssh-settings') }}">
                        <i class="fas fa-terminal"></i>
                        <span>SSH Settings</span>
                    </a>
                </li>
                
                @auth
                    @if(Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#adminMenu">
                                <i class="fas fa-cogs"></i>
                                <span>Admin</span>
                                <i class="fas fa-chevron-down ms-auto"></i>
                            </a>
                            <div class="collapse" id="adminMenu">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                            <i class="fas fa-tachometer-alt"></i>
                                            <span>Admin Dashboard</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                                            <i class="fas fa-users"></i>
                                            <span>Manage Users</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.olts.*') ? 'active' : '' }}" href="{{ route('admin.olts.index') }}">
                                            <i class="fas fa-server"></i>
                                            <span>Manage OLTs</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif
                @endauth
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Bar -->
        <div class="top-bar">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="user-dropdown">
                @auth
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            {{ Auth::user()->name }}
                            @if(Auth::user()->isAdmin())
                                <span class="badge bg-warning ms-1">Admin</span>
                            @endif
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">{{ Auth::user()->email }}</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt me-1"></i>
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                @else
                    <div class="d-flex gap-2">
                        <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus me-1"></i>
                            Register
                        </a>
                    </div>
                @endauth
            </div>
        </div>

        <!-- Page Content -->
        <div class="p-4">
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Livewire Scripts -->
    @livewireScripts
    
    <script>
        // Sidebar Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            // Toggle sidebar
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            });
            
            // Mobile sidebar toggle
            if (window.innerWidth <= 768) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
            }
            
            // Close mobile sidebar when clicking outside
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
