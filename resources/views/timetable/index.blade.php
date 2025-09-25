@extends('layouts.app')

@section('title', $settings['masjid_name'] . ' - Prayer Timetable')

@section('content')
<!-- Fullscreen Controls (only visible in fullscreen mode) -->
<div class="fullscreen-controls">
    <button onclick="toggleFullscreen()" class="btn btn-light btn-sm" id="fullscreenControlBtn">
        <i class="bi bi-fullscreen-exit"></i> Exit Fullscreen
    </button>
</div>

<!-- TV-Optimized Layout -->
<div class="container-fluid tv-layout">
    <!-- Compact Header Section -->
    <div class="tv-header">
        <div class="row align-items-center">
            <div class="col-md-4">
                <h2 class="mb-1">{{ $settings['masjid_name'] }}</h2>
                <p class="mb-0 small">{{ $settings['location'] }}</p>
            </div>
            <div class="col-md-4 text-center">
                <h3 id="current-time" class="mb-1">{{ $now->format('h:i:s A') }}</h3>
                <p class="mb-0 small">{{ $now->format('l, F j, Y') }}</p>
            </div>
            <div class="col-md-4 text-end">
                <h3 class="mb-1">{{ $islamicDate['day'] }} {{ $islamicDate['month'] }} {{ $islamicDate['year'] }}</h3>
                <p class="mb-0 small">Islamic Date</p>
                <button onclick="toggleFullscreen()" class="btn btn-light btn-sm mt-1" id="fullscreenBtn">
                    <i class="bi bi-arrows-fullscreen"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="tv-main-content">
        <div class="row">
            <!-- Prayer Times Section -->
            <div class="col-lg-8">
                <div class="prayer-times-tv">
                    <h3 class="section-title text-center mb-3">Today's Prayer Times</h3>
                    @if($prayerTimes)
                        <div class="prayer-grid">
                            <div class="prayer-time-tv fajr">
                                <div class="prayer-name">Fajr</div>
                                <div class="prayer-time">{{ \Carbon\Carbon::parse($prayerTimes->fajr)->format('h:i A') }}</div>
                            </div>
                            <div class="prayer-time-tv sunrise">
                                <div class="prayer-name">Sunrise</div>
                                <div class="prayer-time">{{ \Carbon\Carbon::parse($prayerTimes->sun_rise)->format('h:i A') }}</div>
                            </div>
                            <div class="prayer-time-tv zohar">
                                <div class="prayer-name">Zohar</div>
                                <div class="prayer-time">{{ \Carbon\Carbon::parse($prayerTimes->zohar)->format('h:i A') }}</div>
                            </div>
                            <div class="prayer-time-tv asr">
                                <div class="prayer-name">Asr</div>
                                <div class="prayer-time">{{ \Carbon\Carbon::parse($prayerTimes->asr)->format('h:i A') }}</div>
                            </div>
                            <div class="prayer-time-tv maghrib">
                                <div class="prayer-name">Maghrib</div>
                                <div class="prayer-time">{{ \Carbon\Carbon::parse($prayerTimes->maghrib)->format('h:i A') }}</div>
                            </div>
                            <div class="prayer-time-tv isha">
                                <div class="prayer-name">Isha</div>
                                <div class="prayer-time">{{ \Carbon\Carbon::parse($prayerTimes->isha)->format('h:i A') }}</div>
                            </div>
                        </div>
                        
                        @if($prayerTimes->jumah_1)
                        <div class="jumah-times text-center mt-3">
                            <strong>Jumah Prayer:</strong> 
                            {{ \Carbon\Carbon::parse($prayerTimes->jumah_1)->format('h:i A') }}
                            @if($prayerTimes->jumah_2)
                                & {{ \Carbon\Carbon::parse($prayerTimes->jumah_2)->format('h:i A') }}
                            @endif
                        </div>
                        @endif
                    @else
                        <div class="alert alert-warning text-center">
                            <h5>No prayer times available for today</h5>
                        </div>
                    @endif
                </div>

                <!-- Compact Hadeeth Section -->
                @if($hadeeth)
                <div class="hadeeth-tv mt-4">
                    <h4 class="text-center mb-2" style="color: #d4af37;">Hadeeth of the Day</h4>
                    <div class="text-center">
                        <p class="arabic-text" style="font-family: 'Amiri', serif; direction: rtl; font-size: 1.1rem;">{{ $hadeeth->arabic_text }}</p>
                        <p class="english-text">{{ $hadeeth->english_translation }}</p>
                        <small class="reference text-muted">{{ $hadeeth->reference }}</small>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Sidebar -->
            <div class="col-lg-4">
                <!-- Next Prayer -->
                @if($nextPrayer)
                <div class="next-prayer-tv">
                    <h4 class="text-center">Next Prayer</h4>
                    <h2 class="text-center prayer-name">{{ ucfirst($nextPrayer['name']) }}</h2>
                    <h3 class="text-center prayer-time">{{ \Carbon\Carbon::parse($nextPrayer['time'])->format('h:i A') }}</h3>
                    <div id="countdown" class="text-center mt-2">
                        <span class="countdown-timer countdown-tv" data-seconds="{{ $nextPrayer['time_until'] }}"></span>
                    </div>
                </div>
                @endif

                <!-- Compact Announcements -->
                @if($announcements->count() > 0)
                <div class="announcements-tv mt-4">
                    <h4 class="text-center mb-3">Announcements</h4>
                    <div class="announcements-container">
                        @foreach($announcements->take(3) as $announcement)
                            <div class="announcement-tv mb-2" 
                                 style="background-color: {{ $announcement->background_color }}; 
                                        color: {{ $announcement->text_color }};">
                                <h6 class="mb-1">{{ $announcement->title }}</h6>
                                <p class="mb-0">{{ Str::limit($announcement->content, 80) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Scrolling Announcements Bar -->
    @if($announcements->count() > 0)
    <div class="tv-ticker">
        <div class="announcement-scroll">
            @foreach($announcements as $announcement)
                <span class="mx-5">{{ $announcement->title }}: {{ $announcement->content }}</span>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    // Countdown timer for next prayer
    function updateCountdown() {
        const countdownElement = document.querySelector('.countdown-timer');
        if (!countdownElement) return;
        
        let seconds = parseInt(countdownElement.getAttribute('data-seconds'));
        
        if (seconds <= 0) {
            location.reload(); // Refresh when prayer time is reached
            return;
        }
        
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const remainingSeconds = seconds % 60;
        
        const timeString = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
        countdownElement.textContent = timeString;
        
        countdownElement.setAttribute('data-seconds', seconds - 1);
    }
    
    // Update countdown every second
    setInterval(updateCountdown, 1000);
    updateCountdown(); // Initial call
    
    // Fullscreen functionality
    function toggleFullscreen() {
        const element = document.documentElement;
        const button = document.getElementById('fullscreenBtn');
        
        if (!document.fullscreenElement && !document.mozFullScreenElement && 
            !document.webkitFullscreenElement && !document.msFullscreenElement) {
            // Enter fullscreen
            if (element.requestFullscreen) {
                element.requestFullscreen();
            } else if (element.msRequestFullscreen) {
                element.msRequestFullscreen();
            } else if (element.mozRequestFullScreen) {
                element.mozRequestFullScreen();
            } else if (element.webkitRequestFullscreen) {
                element.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
            }
            
            button.innerHTML = '<i class="bi bi-fullscreen-exit"></i> Exit Fullscreen';
            document.body.classList.add('fullscreen-mode');
            document.body.classList.add('show-controls');
            
            // Hide controls after 3 seconds
            setTimeout(() => {
                document.body.classList.remove('show-controls');
            }, 3000);
            
        } else {
            // Exit fullscreen
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
            
            button.innerHTML = '<i class="bi bi-arrows-fullscreen"></i> Enter Fullscreen';
            document.body.classList.remove('fullscreen-mode');
        }
    }
    
    // Listen for fullscreen changes
    document.addEventListener('fullscreenchange', updateFullscreenButton);
    document.addEventListener('webkitfullscreenchange', updateFullscreenButton);
    document.addEventListener('mozfullscreenchange', updateFullscreenButton);
    document.addEventListener('MSFullscreenChange', updateFullscreenButton);
    
    function updateFullscreenButton() {
        const button = document.getElementById('fullscreenBtn');
        
        if (document.fullscreenElement || document.webkitFullscreenElement || 
            document.mozFullScreenElement || document.msFullscreenElement) {
            button.innerHTML = '<i class="bi bi-fullscreen-exit"></i> Exit Fullscreen';
            document.body.classList.add('fullscreen-mode');
        } else {
            button.innerHTML = '<i class="bi bi-arrows-fullscreen"></i> Enter Fullscreen';
            document.body.classList.remove('fullscreen-mode');
        }
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F11') {
            e.preventDefault();
            toggleFullscreen();
        } else if (e.key === 'Escape') {
            // Exit fullscreen on Escape (browser default)
            if (document.fullscreenElement) {
                toggleFullscreen();
            }
        } else if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
            if (document.fullscreenElement) {
                e.preventDefault(); // Prevent dev tools in fullscreen mode
            }
        }
    });
    
    // Prevent right-click in fullscreen mode
    document.addEventListener('contextmenu', e => {
        if (document.fullscreenElement) {
            e.preventDefault();
        }
    });
    
    // Show controls on mouse movement in fullscreen
    let mouseTimer;
    document.addEventListener('mousemove', function() {
        if (document.fullscreenElement) {
            document.body.classList.add('show-controls');
            
            clearTimeout(mouseTimer);
            mouseTimer = setTimeout(() => {
                document.body.classList.remove('show-controls');
            }, 3000);
        }
    });
</script>
@endsection
