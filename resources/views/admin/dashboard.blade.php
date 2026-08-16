@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<style>
    .dashboard-shell {
        padding: 0.5rem 0 2rem;
    }

    .dashboard-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
        margin-bottom: 1.5rem;
    }

    .dashboard-topbar h1 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
    }

    .dashboard-topbar p {
        margin: 0.3rem 0 0;
        color: #6b7280;
    }

    .dashboard-meta {
        text-align: right;
        color: #4b5563;
        font-weight: 600;
    }

    .dashboard-alert {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
    }

    .stats-row {
        margin-bottom: 1.5rem;
    }

    .metric-card {
        height: 100%;
        border: 0;
        border-radius: 18px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
    }

    .metric-card .card-body {
        padding: 0.85rem 0.8rem;
        text-align: center;
    }

    .metric-icon {
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
    }

    .metric-value {
        font-size: 1.5rem;
        line-height: 1;
        font-weight: 800;
        margin-bottom: 0.25rem;
    }

    .metric-label {
        color: #6b7280;
        font-size: 0.85rem;
        margin: 0;
    }

    .dashboard-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .dashboard-card .card-header {
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .dashboard-card .card-body {
        padding: 0.85rem;
    }

    .section-subtitle {
        color: #6b7280;
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
    }

    .prayer-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.65rem;
    }

    .prayer-item {
        border-radius: 14px;
        padding: 0.65rem 0.65rem;
        background: linear-gradient(135deg, #6b7ef0 0%, #7550b3 100%);
        color: white;
        text-align: center;
    }

    .prayer-item-label {
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 0.35rem;
        opacity: 0.95;
    }

    .prayer-item-time {
        font-size: 1.3rem;
        line-height: 1.1;
        font-weight: 800;
    }

    .prayer-item-note {
        margin-top: 0.2rem;
        font-size: 0.7rem;
        opacity: 0.82;
    }

    .special-times-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.65rem;
        margin-top: 0.75rem;
    }

    .special-time-item {
        border-radius: 12px;
        padding: 0.65rem 0.75rem;
        background: #f7f5ff;
        border: 1px solid #e7e2ff;
    }

    .special-time-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 700;
        color: #6b5b95;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.2rem;
    }

    .special-time-value {
        font-size: 0.9rem;
        font-weight: 700;
        color: #2f2a45;
    }

    .sidebar-stack {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .next-prayer-card {
        border: 0;
        border-radius: 14px;
        color: white;
        background: linear-gradient(135deg, #f48363 0%, #f6b26b 100%);
        box-shadow: 0 10px 24px rgba(244, 131, 99, 0.18);
    }

    .next-prayer-card .card-body {
        padding: 1rem;
        text-align: center;
    }

    .next-prayer-label {
        font-size: 0.8rem;
        opacity: 0.9;
        margin-bottom: 0.2rem;
    }

    .next-prayer-name {
        font-size: 1.6rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .next-prayer-time {
        font-size: 1rem;
        font-weight: 700;
        margin-top: 0.2rem;
    }

    .countdown-value {
        display: inline-block;
        margin-top: 0.6rem;
        padding: 0.5rem 0.8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.18);
        font-size: 0.9rem;
        font-weight: 700;
    }

    .quick-actions {
        display: grid;
        gap: 0.5rem;
    }

    .special-times-form .form-label {
        font-weight: 600;
        color: #4b5563;
    }

    .special-times-form .form-control {
        border-radius: 12px;
        min-height: 48px;
    }

    .table thead th {
        color: #6b7280;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .empty-state {
        text-align: center;
        padding: 1.5rem 0.75rem;
        border: 1px dashed #d9dbe8;
        border-radius: 12px;
        background: #fafbff;
    }

    .empty-state i {
        font-size: 2.2rem;
        color: #a0a6b8;
    }

    @media (max-width: 1399px) {
        .prayer-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991px) {
        .dashboard-topbar {
            flex-direction: column;
            align-items: flex-start;
        }

        .dashboard-meta {
            text-align: left;
        }

        .prayer-grid,
        .special-times-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575px) {
        .dashboard-topbar {
            padding: 1rem;
        }

        .dashboard-topbar h1 {
            font-size: 1.65rem;
        }

        .prayer-grid,
        .special-times-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $todayDate = $today_prayer_times?->date ? \Carbon\Carbon::parse($today_prayer_times->date) : null;
    $countdownSeconds = $next_prayer ? max(0, (int) floor($next_prayer['time_until'])) : 0;
    $prayerCards = [
        ['label' => 'Fajr', 'value' => $today_prayer_times?->fajr, 'note' => $today_prayer_times?->fajr_jamaat ? 'Jamaat ' . \Carbon\Carbon::parse($today_prayer_times->fajr_jamaat)->format('h:i A') : null],
        ['label' => 'Zohar', 'value' => $today_prayer_times?->zohar, 'note' => $today_prayer_times?->zohar_jamaat ? 'Jamaat ' . \Carbon\Carbon::parse($today_prayer_times->zohar_jamaat)->format('h:i A') : null],
        ['label' => 'Asr', 'value' => $today_prayer_times?->asr, 'note' => $today_prayer_times?->asr_jamaat ? 'Jamaat ' . \Carbon\Carbon::parse($today_prayer_times->asr_jamaat)->format('h:i A') : null],
        ['label' => 'Maghrib', 'value' => $today_prayer_times?->maghrib, 'note' => $today_prayer_times?->maghrib_jamaat ? 'Jamaat ' . \Carbon\Carbon::parse($today_prayer_times->maghrib_jamaat)->format('h:i A') : null],
        ['label' => 'Isha', 'value' => $today_prayer_times?->isha, 'note' => $today_prayer_times?->isha_jamaat ? 'Jamaat ' . \Carbon\Carbon::parse($today_prayer_times->isha_jamaat)->format('h:i A') : null],
        ['label' => 'Sun Rise', 'value' => $today_prayer_times?->sun_rise, 'note' => 'Sunrise'],
    ];
    $specialTimes = [
        ['label' => "Jumu'ah 1", 'value' => $today_prayer_times?->jumah_1],
        ['label' => "Jumu'ah 2", 'value' => $today_prayer_times?->jumah_2],
        ['label' => 'Eid Prayer 1', 'value' => $today_prayer_times?->eid_prayer_1],
        ['label' => 'Eid Prayer 2', 'value' => $today_prayer_times?->eid_prayer_2],
    ];
@endphp

<div class="dashboard-shell">
    @if(session('success'))
        <div class="alert alert-success dashboard-alert mb-4">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger dashboard-alert mb-4">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger dashboard-alert mb-4">
            <strong class="d-block mb-2">Please fix the following:</strong>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="dashboard-topbar">
        <div>
            <h1><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
            <p>{{ $todayDate ? $todayDate->format('l, d M Y') : now()->format('l, d M Y') }}</p>
        </div>
        <div class="dashboard-meta">
            <div>Welcome, {{ auth()->user()->name }}</div>
            @if($next_prayer)
                <small>Next prayer: {{ ucfirst($next_prayer['name']) }} at {{ \Carbon\Carbon::parse($next_prayer['time'])->format('h:i A') }}</small>
            @endif
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-5 g-3 stats-row">
        <div class="col">
            <div class="card metric-card">
                <div class="card-body">
                    <div class="metric-icon text-primary"><i class="bi bi-clock-history"></i></div>
                    <div class="metric-value text-primary">{{ $stats['prayer_times_count'] ?? 0 }}</div>
                    <p class="metric-label">Prayer Times</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card metric-card">
                <div class="card-body">
                    <div class="metric-icon text-success"><i class="bi bi-megaphone"></i></div>
                    <div class="metric-value text-success">{{ $stats['announcements_count'] ?? 0 }}</div>
                    <p class="metric-label">Active Announcements</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card metric-card">
                <div class="card-body">
                    <div class="metric-icon text-purple"><i class="bi bi-images"></i></div>
                    <div class="metric-value text-purple">{{ $stats['media_count'] ?? 0 }}</div>
                    <p class="metric-label">Active Media</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card metric-card">
                <div class="card-body">
                    <div class="metric-icon text-orange"><i class="bi bi-calendar-event"></i></div>
                    <div class="metric-value text-orange">{{ $stats['media_schedules_count'] ?? 0 }}</div>
                    <p class="metric-label">Media Schedules</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card metric-card">
                <div class="card-body">
                    <div class="metric-icon text-warning"><i class="bi bi-gear"></i></div>
                    <div class="metric-value text-warning">{{ $stats['total_settings'] ?? 0 }}</div>
                    <p class="metric-label">Settings</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                    <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Today&apos;s Prayer Times</h5>
                    @if($today_prayer_times)
                        <a href="{{ route('admin.prayer-times.edit', $today_prayer_times) }}" class="btn btn-sm btn-light">
                            <i class="bi bi-pencil-square me-1"></i>
                            Edit Full Day
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($today_prayer_times)
                        <p class="section-subtitle">A quick view of today&apos;s prayer schedule and special times.</p>

                        <div class="prayer-grid">
                            @foreach($prayerCards as $item)
                                <div class="prayer-item">
                                    <div class="prayer-item-label">{{ $item['label'] }}</div>
                                    <div class="prayer-item-time">{{ $item['value'] ? \Carbon\Carbon::parse($item['value'])->format('h:i A') : '--:--' }}</div>
                                    @if($item['note'])
                                        <div class="prayer-item-note">{{ $item['note'] }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="bi bi-calendar2-x"></i>
                            <h6 class="mt-3">No prayer times are set for today</h6>
                            <p class="text-muted mb-3">Create today&apos;s prayer row first, then the dashboard will show the timings here.</p>
                            <a href="{{ route('admin.prayer-times.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>
                                Create Today&apos;s Prayer Times
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card dashboard-card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-stars me-2"></i>Today&apos;s Special Times</h6>
                </div>
                <div class="card-body">
                    @if($today_prayer_times)
                        <p class="section-subtitle">Quickly update today&apos;s Jumu&apos;ah and Eid times here.</p>

                        <div class="special-times-grid mb-3">
                            @foreach($specialTimes as $item)
                                <div class="special-time-item">
                                    <span class="special-time-label">{{ $item['label'] }}</span>
                                    <span class="special-time-value">{{ $item['value'] ? \Carbon\Carbon::parse($item['value'])->format('h:i A') : '--:--' }}</span>
                                </div>
                            @endforeach
                        </div>

                        <form method="POST" action="{{ route('admin.prayer-times.today-special-times') }}" class="special-times-form">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <label for="jumah_1" class="form-label">Jumu&apos;ah 1</label>
                                    <input type="time" class="form-control" id="jumah_1" name="jumah_1" value="{{ old('jumah_1', $today_prayer_times->jumah_1 ? \Carbon\Carbon::parse($today_prayer_times->jumah_1)->format('H:i') : '') }}">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label for="jumah_2" class="form-label">Jumu&apos;ah 2</label>
                                    <input type="time" class="form-control" id="jumah_2" name="jumah_2" value="{{ old('jumah_2', $today_prayer_times->jumah_2 ? \Carbon\Carbon::parse($today_prayer_times->jumah_2)->format('H:i') : '') }}">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label for="eid_prayer_1" class="form-label">Eid Prayer 1</label>
                                    <input type="time" class="form-control" id="eid_prayer_1" name="eid_prayer_1" value="{{ old('eid_prayer_1', $today_prayer_times->eid_prayer_1 ? \Carbon\Carbon::parse($today_prayer_times->eid_prayer_1)->format('H:i') : '') }}">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label for="eid_prayer_2" class="form-label">Eid Prayer 2</label>
                                    <input type="time" class="form-control" id="eid_prayer_2" name="eid_prayer_2" value="{{ old('eid_prayer_2', $today_prayer_times->eid_prayer_2 ? \Carbon\Carbon::parse($today_prayer_times->eid_prayer_2)->format('H:i') : '') }}">
                                </div>
                            </div>
                            <div class="d-grid gap-2 mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>
                                    Save Today&apos;s Special Times
                                </button>
                                <a href="{{ route('admin.prayer-times.edit', $today_prayer_times) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-pencil-square me-2"></i>
                                    Open Full Prayer Editor
                                </a>
                            </div>
                        </form>
                    @else
                        <div class="empty-state">
                            <i class="bi bi-stars"></i>
                            <h6 class="mt-3">Special times need today&apos;s prayer row</h6>
                            <p class="text-muted mb-3">Once today exists in Prayer Times, this quick editor will appear here.</p>
                            <a href="{{ route('admin.prayer-times.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>
                                Add Prayer Times
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="sidebar-stack">
                @if($next_prayer)
                    <div class="card next-prayer-card">
                        <div class="card-body">
                            <div class="next-prayer-label">Next Prayer</div>
                            <div class="next-prayer-name">{{ ucfirst($next_prayer['name']) }}</div>
                            <div class="next-prayer-time">{{ \Carbon\Carbon::parse($next_prayer['time'])->format('h:i A') }}</div>
                            <div class="countdown-value" id="countdown">00:00:00</div>
                        </div>
                    </div>
                @endif

                <div class="card dashboard-card">
                    <div class="card-header">
                        <h6 class="mb-0">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="{{ route('admin.prayer-times.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>
                                Add Prayer Times
                            </a>
                            <a href="{{ route('admin.announcements.create') }}" class="btn btn-success">
                                <i class="bi bi-plus-circle me-2"></i>
                                New Announcement
                            </a>
                            <a href="{{ route('admin.media.create') }}" class="btn btn-purple">
                                <i class="bi bi-plus-circle me-2"></i>
                                Add Media
                            </a>
                            <a href="{{ route('admin.media-schedules.create') }}" class="btn btn-orange">
                                <i class="bi bi-plus-circle me-2"></i>
                                Schedule Media
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card dashboard-card">
                    <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                        <h6 class="mb-0"><i class="bi bi-text-paragraph me-2"></i>Sliding Text Preview</h6>
                        <a href="{{ route('admin.sliding-texts.index') }}" class="btn btn-sm btn-light">Manage</a>
                    </div>
                    <div class="card-body">
                        @if(isset($slidingTexts) && $slidingTexts->count() > 0)
                            <div class="d-grid gap-2">
                                @foreach($slidingTexts->take(2) as $slidingText)
                                    <div class="p-3 rounded" style="background: {{ $slidingText->background_color }}22; border: 1px solid {{ $slidingText->background_color }}55;">
                                        <div style="color: {{ $slidingText->text_color }}; font-weight: 600; font-size: {{ max(14, min((int) $slidingText->font_size, 20)) }}px;">
                                            {{ $slidingText->text }}
                                        </div>
                                        <small class="text-muted d-block mt-1">Speed {{ $slidingText->animation_speed }}s</small>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="bi bi-text-center"></i>
                                <h6 class="mt-3">No sliding texts yet</h6>
                                <p class="text-muted mb-3">Create your first scrolling message for the timetable screen.</p>
                                <a href="{{ route('admin.sliding-texts.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    Add Sliding Text
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                    <h5 class="mb-0"><i class="bi bi-megaphone me-2"></i>Recent Announcements</h5>
                    <a href="{{ route('admin.announcements.index') }}" class="btn btn-sm btn-light">View All</a>
                </div>
                <div class="card-body">
                    @if($recent_announcements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Message</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_announcements as $announcement)
                                        <tr>
                                            <td><strong>{{ $announcement->title ?: '(No title)' }}</strong></td>
                                            <td>{{ Str::limit($announcement->content, 80) }}</td>
                                            <td>
                                                <span class="badge {{ $announcement->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $announcement->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>{{ $announcement->created_at->format('M j, Y') }}</td>
                                            <td class="text-end">
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
                        <div class="empty-state">
                            <i class="bi bi-megaphone"></i>
                            <h6 class="mt-3">No announcements yet</h6>
                            <p class="text-muted mb-3">Post your first update so it can appear on the timetable screen.</p>
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

@if($next_prayer)
    <script>
        let seconds = {{ $countdownSeconds }};

        function updateCountdown() {
            if (seconds <= 0) {
                location.reload();
                return;
            }

            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const remainingSeconds = Math.floor(seconds % 60);
            const countdown = document.getElementById('countdown');

            if (countdown) {
                countdown.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
            }

            seconds -= 1;
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    </script>
@endif
@endsection
