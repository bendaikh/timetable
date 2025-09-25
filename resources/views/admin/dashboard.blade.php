<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Masjid Timetable</title>
    
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
        
        .stat-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .prayer-time-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .next-prayer-card {
            background: linear-gradient(135deg, #ff7e5f 0%, #feb47b 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 20px;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="p-4">
            <h4 class="text-white mb-4">
                <i class="bi bi-house-door-fill me-2"></i>
                Masjid Admin
            </h4>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.prayer-times.index') }}">
                        <i class="bi bi-clock me-2"></i>
                        Prayer Times
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.announcements.index') }}">
                        <i class="bi bi-megaphone me-2"></i>
                        Announcements
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.hadeeths.index') }}">
                        <i class="bi bi-book me-2"></i>
                        Hadeeths
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.settings.index') }}">
                        <i class="bi bi-gear me-2"></i>
                        Settings
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link" href="{{ route('timetable.index') }}" target="_blank">
                        <i class="bi bi-eye me-2"></i>
                        View Timetable
                    </a>
                </li>
            </ul>
            
            <div class="mt-5">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn logout-btn w-100">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm mb-4">
            <div class="container-fluid">
                <span class="navbar-brand">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </span>
                <div class="navbar-nav ms-auto">
                    <span class="nav-item nav-link">
                        Welcome, {{ auth()->user()->name }}
                    </span>
                </div>
            </div>
        </nav>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-icon text-primary">
                        <i class="bi bi-clock"></i>
                    </div>
                    <div class="stat-number text-primary">{{ $stats['prayer_times_count'] }}</div>
                    <div class="text-muted">Prayer Times</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-icon text-success">
                        <i class="bi bi-megaphone"></i>
                    </div>
                    <div class="stat-number text-success">{{ $stats['announcements_count'] }}</div>
                    <div class="text-muted">Active Announcements</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-icon text-info">
                        <i class="bi bi-book"></i>
                    </div>
                    <div class="stat-number text-info">{{ $stats['hadeeths_count'] }}</div>
                    <div class="text-muted">Active Hadeeths</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-icon text-warning">
                        <i class="bi bi-gear"></i>
                    </div>
                    <div class="stat-number text-warning">{{ $stats['total_settings'] }}</div>
                    <div class="text-muted">Settings</div>
                </div>
            </div>
        </div>

        <!-- Prayer Times and Next Prayer -->
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header card-header-custom">
                        <h5 class="mb-0">
                            <i class="bi bi-clock me-2"></i>
                            Today's Prayer Times
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($today_prayer_times)
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="prayer-time-display text-center">
                                        <h6>Fajr</h6>
                                        <h4>{{ \Carbon\Carbon::parse($today_prayer_times->fajr)->format('h:i A') }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="prayer-time-display text-center">
                                        <h6>Zohar</h6>
                                        <h4>{{ \Carbon\Carbon::parse($today_prayer_times->zohar)->format('h:i A') }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="prayer-time-display text-center">
                                        <h6>Asr</h6>
                                        <h4>{{ \Carbon\Carbon::parse($today_prayer_times->asr)->format('h:i A') }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="prayer-time-display text-center">
                                        <h6>Maghrib</h6>
                                        <h4>{{ \Carbon\Carbon::parse($today_prayer_times->maghrib)->format('h:i A') }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="prayer-time-display text-center">
                                        <h6>Isha</h6>
                                        <h4>{{ \Carbon\Carbon::parse($today_prayer_times->isha)->format('h:i A') }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="prayer-time-display text-center">
                                        <h6>Sun Rise</h6>
                                        <h4>{{ \Carbon\Carbon::parse($today_prayer_times->sun_rise)->format('h:i A') }}</h4>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                No prayer times set for today. Please add them.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Next Prayer -->
                @if($next_prayer)
                <div class="next-prayer-card mb-4">
                    <h5>Next Prayer</h5>
                    <h3>{{ ucfirst($next_prayer['name']) }}</h3>
                    <h2>{{ \Carbon\Carbon::parse($next_prayer['time'])->format('h:i A') }}</h2>
                    <div class="mt-3">
                        <span id="countdown" class="h4"></span>
                    </div>
                </div>
                @endif

                <!-- Quick Actions -->
                <div class="card shadow">
                    <div class="card-header card-header-custom">
                        <h6 class="mb-0">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.prayer-times.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>
                                Add Prayer Times
                            </a>
                            <a href="{{ route('admin.announcements.create') }}" class="btn btn-success">
                                <i class="bi bi-plus-circle me-2"></i>
                                New Announcement
                            </a>
                            <a href="{{ route('admin.hadeeths.create') }}" class="btn btn-info">
                                <i class="bi bi-plus-circle me-2"></i>
                                Add Hadeeth
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Announcements -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header card-header-custom">
                        <h5 class="mb-0">
                            <i class="bi bi-megaphone me-2"></i>
                            Recent Announcements
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($recent_announcements->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Content</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recent_announcements as $announcement)
                                        <tr>
                                            <td><strong>{{ $announcement->title }}</strong></td>
                                            <td>{{ Str::limit($announcement->content, 50) }}</td>
                                            <td>
                                                <span class="badge {{ $announcement->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $announcement->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>{{ $announcement->created_at->format('M j, Y') }}</td>
                                            <td>
                                                <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-megaphone display-4 text-muted"></i>
                                <p class="text-muted mt-3">No announcements yet. Create your first announcement!</p>
                                <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    Create Announcement
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Countdown timer for next prayer
        @if($next_prayer)
        let seconds = {{ $next_prayer['time_until'] }};
        
        function updateCountdown() {
            if (seconds <= 0) {
                location.reload();
                return;
            }
            
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const remainingSeconds = seconds % 60;
            
            const timeString = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
            document.getElementById('countdown').textContent = timeString;
            
            seconds--;
        }
        
        setInterval(updateCountdown, 1000);
        updateCountdown();
        @endif
    </script>
</body>
</html>
