<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel - Masjid Timetable')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .sidebar {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            border-radius: 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            border: none;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .box-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .box-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        
        .preview-container {
            min-height: 200px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 15px;
            background-color: #f9f9f9;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .box-preview {
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
        }
        
        .form-control-color {
            width: 60px;
            height: 38px;
            padding: 0;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }
        
        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="sidebar col-md-3 col-lg-2 d-md-block">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="navbar-brand text-white">Admin Panel</h4>
                        <small class="text-white-50">Masjid Timetable</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                               href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.boxes.*') ? 'active' : '' }}" 
                               href="{{ route('admin.boxes.index') }}">
                                <i class="bi bi-grid-3x3-gap"></i> Boxes Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.prayer-times.*') ? 'active' : '' }}" 
                               href="{{ route('admin.prayer-times.index') }}">
                                <i class="bi bi-clock"></i> Prayer Times
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}" 
                               href="{{ route('admin.announcements.index') }}">
                                <i class="bi bi-megaphone"></i> Announcements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.hadeeths.*') ? 'active' : '' }}" 
                               href="{{ route('admin.hadeeths.index') }}">
                                <i class="bi bi-book"></i> Hadeeths
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.media.*') ? 'active' : '' }}" 
                               href="{{ route('admin.media.index') }}">
                                <i class="bi bi-image"></i> Media
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.media-schedules.*') ? 'active' : '' }}" 
                               href="{{ route('admin.media-schedules.index') }}">
                                <i class="bi bi-calendar-event"></i> Media Schedules
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.sliding-texts.*') ? 'active' : '' }}" 
                               href="{{ route('admin.sliding-texts.index') }}">
                                <i class="bi bi-text-paragraph"></i> Sliding Texts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" 
                               href="{{ route('admin.settings.index') }}">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link" href="{{ route('timetable.index') }}" target="_blank">
                                <i class="bi bi-eye"></i> View Timetable
                            </a>
                        </li>
                    </ul>
                    
                    <div class="mt-auto pt-3">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn logout-btn w-100">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="main-content col-md-9 ms-sm-auto col-lg-10 px-md-4">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Boxes Management JS -->
    <script src="{{ asset('js/boxes-management.js') }}"></script>
    
    <!-- Mobile sidebar toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
            }
        });
    </script>
    
    @yield('scripts')
    @stack('scripts')
</body>
</html>