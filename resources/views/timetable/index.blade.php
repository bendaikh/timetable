@extends('layouts.app')

@section('title', $settings['masjid_name'] . ' - Prayer Timetable')

@section('content')

<!-- Media Display Overlay (Fullscreen) -->
<div id="media-overlay" class="media-overlay" style="display: none;">
    <div class="media-container">
        <div id="media-content" class="media-content">
            <!-- Media content will be loaded here -->
        </div>
        <div id="media-countdown" class="media-countdown" style="display: none;">
            <div class="countdown-timer">
                <div class="countdown-label">Next Prayer</div>
                <div id="countdown-prayer-name" class="countdown-prayer"></div>
                <div id="countdown-time" class="countdown-time"></div>
            </div>
        </div>
    </div>
</div>

<!-- Digital Information Board Layout -->
<div class="container-fluid digital-board">
    <!-- Top Header Row -->
    <div class="board-header">
        <div class="row align-items-center">
            <!-- Current Time -->
            <div class="col-md-4">
                <div class="current-time-display">
                    <div class="time-large" id="current-time">{{ $now->format('h:i') }}</div>
                </div>
            </div>
            
            <!-- Gregorian Date -->
            <div class="col-md-4 text-center">
                <div class="date-display">
                    <div class="gregorian-date">{{ $now->format('D j M Y') }}</div>
                </div>
            </div>
            
            <!-- Islamic Date and Fullscreen Button -->
            <div class="col-md-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="islamic-date-display text-center" style="flex: 1;">
                        <div class="islamic-date">{{ $islamicDate['day'] }} {{ $islamicDate['month'] }} {{ $islamicDate['year'] }}</div>
                    </div>
                    <button onclick="toggleFullscreen()" class="btn btn-light btn-sm" id="fullscreenBtn">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="board-main-content">
        <div class="row h-100">
            <!-- Left Column - Prayer Times -->
            <div class="col-md-4">
                <div class="prayer-times-section" @if($settings['logo_path'] ?? false) style="--logo-bg-image: url('{{ app()->environment('production') ? url('public/storage/' . $settings['logo_path']) : asset('storage/' . $settings['logo_path']) }}')" @endif>
                    <div class="prayer-header">
                        <div class="prayer-col-header"></div>
                        <div class="prayer-col-header">Beginning</div>
                        <div class="prayer-col-header">Jamaat Time</div>
                    </div>
                    
                    @if($prayerTimes)
                        <div class="prayer-list">
                            <div class="prayer-row">
                                <div class="prayer-name">Fajr</div>
                                <div class="prayer-time">{{ \Carbon\Carbon::parse($prayerTimes->fajr)->format('h:i') }}</div>
                                <div class="prayer-jamaat">{{ \Carbon\Carbon::parse($prayerTimes->fajr)->addMinutes((int)$settings['fajr_jamaat_offset'])->format('h:i') }}</div>
                            </div>
                            <div class="prayer-row">
                                <div class="prayer-name">Zohar</div>
                                <div class="prayer-time">{{ \Carbon\Carbon::parse($prayerTimes->zohar)->format('h:i') }}</div>
                                <div class="prayer-jamaat">{{ \Carbon\Carbon::parse($prayerTimes->zohar)->addMinutes((int)$settings['zohar_jamaat_offset'])->format('h:i') }}</div>
                            </div>
                            <div class="prayer-row">
                                <div class="prayer-name">Asr</div>
                                <div class="prayer-time">{{ \Carbon\Carbon::parse($prayerTimes->asr)->format('h:i') }}</div>
                                <div class="prayer-jamaat">{{ \Carbon\Carbon::parse($prayerTimes->asr)->addMinutes((int)$settings['asr_jamaat_offset'])->format('h:i') }}</div>
                            </div>
                            <div class="prayer-row">
                                <div class="prayer-name">Maghrib</div>
                                <div class="prayer-time">--:--</div>
                                <div class="prayer-jamaat">{{ \Carbon\Carbon::parse($prayerTimes->maghrib)->addMinutes((int)$settings['maghrib_jamaat_offset'])->format('h:i') }}</div>
                            </div>
                            <div class="prayer-row">
                                <div class="prayer-name">Isha</div>
                                <div class="prayer-time">{{ \Carbon\Carbon::parse($prayerTimes->isha)->format('h:i') }}</div>
                                <div class="prayer-jamaat">{{ \Carbon\Carbon::parse($prayerTimes->isha)->addMinutes((int)$settings['isha_jamaat_offset'])->format('h:i') }}</div>
                            </div>
                        </div>
                        
                        <div class="next-prayer-info">
                            <div class="next-prayer-text">Next prayer in:</div>
                        </div>
                    @else
                        <div class="no-prayer-times">
                            <p>No prayer times available for today</p>
                        </div>
                    @endif
                </div>
                </div>

            <!-- Middle Column - Hadeeth of the Day -->
            <div class="col-md-4">
                <div class="hadeeth-section">
                    <div class="hadeeth-header">Hadeeth Of The Day</div>
                    <div class="hadeeth-content" id="hadeeth-content">
                        @if($hadeeths->count() > 0)
                            @foreach($hadeeths as $index => $hadeethItem)
                                <div class="hadeeth-text rotating-hadeeth" data-index="{{ $index }}" style="{{ $index === 0 ? 'display: block;' : 'display: none;' }}">
                                    <div class="arabic-hadeeth">{{ $hadeethItem->arabic_text }}</div>
                                    <div class="english-hadeeth">{{ $hadeethItem->english_translation }}</div>
                                    <div class="hadeeth-reference">{{ $hadeethItem->reference }}</div>
                                </div>
                            @endforeach
                        @else
                            <div class="hadeeth-placeholder">
                                <p>Displayed large, clear and nice</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column - Announcements -->
            <div class="col-md-4">
                <div class="announcements-section">
                    <div class="announcements-header">Announcements</div>
                    <div class="announcements-content" id="announcements-content">
                        @if($announcements->count() > 0)
                            @foreach($announcements as $index => $announcement)
                                <div class="announcement-item rotating-announcement" data-index="{{ $index }}" style="{{ $index < 2 ? 'display: block;' : 'display: none;' }}">
                                    <div class="announcement-title">{{ $announcement->title }}</div>
                                    <div class="announcement-text">{{ $announcement->content }}</div>
                                </div>
                            @endforeach
                        @else
                            <div class="announcement-placeholder">
                                <p>Announcements should be centered in large and clear text.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Additional Times -->
    <div class="board-bottom-times">
        <div class="row">
            <div class="col">
                <div class="additional-times">
                    <div class="time-item">
                        <div class="time-label">Sehri Ends</div>
                        <div class="time-value">{{ $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->fajr)->format('h:i') : '--:--' }}</div>
                    </div>
                    <div class="time-item">
                        <div class="time-label">Sun Rise</div>
                        <div class="time-value">{{ $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->sun_rise)->format('h:i') : '--:--' }}</div>
                    </div>
                    <div class="time-item">
                        <div class="time-label">Noon</div>
                        <div class="time-value">{{ $prayerTimes ? \Carbon\Carbon::parse($prayerTimes->zohar)->format('h:i') : '--:--' }}</div>
                    </div>
                    <div class="time-item">
                        <div class="time-label">Jumu'ah 1</div>
                        <div class="time-value">{{ $prayerTimes && $prayerTimes->jumah_1 ? \Carbon\Carbon::parse($prayerTimes->jumah_1)->format('h:i') : '--:--' }}</div>
                    </div>
                    <div class="time-item">
                        <div class="time-label">Jumu'ah 2</div>
                        <div class="time-value">{{ $prayerTimes && $prayerTimes->jumah_2 ? \Carbon\Carbon::parse($prayerTimes->jumah_2)->format('h:i') : '--:--' }}</div>
                    </div>
                    <div class="time-item">
                        <div class="time-label">Eid Prayer 1</div>
                        <div class="time-value">{{ $prayerTimes && $prayerTimes->eid_prayer_1 ? \Carbon\Carbon::parse($prayerTimes->eid_prayer_1)->format('h:i') : '--:--' }}</div>
                            </div>
                    <div class="time-item">
                        <div class="time-label">Eid Prayer 2</div>
                        <div class="time-value">{{ $prayerTimes && $prayerTimes->eid_prayer_2 ? \Carbon\Carbon::parse($prayerTimes->eid_prayer_2)->format('h:i') : '--:--' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scrolling Text Area -->
    <div class="scrolling-text-area">
        <div class="scrolling-content">
            <div class="scroll-arrow left-arrow">←</div>
            <div class="scrolling-text">
    @if($announcements->count() > 0)
            @foreach($announcements as $announcement)
                        <span class="scroll-item">{{ $announcement->title }}: {{ $announcement->content }}</span>
            @endforeach
                @else
                    <span class="scroll-item">SCROLLING TEXT</span>
                @endif
            </div>
            <div class="scroll-arrow right-arrow">→</div>
        </div>
    </div>

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

    // Media Display System
    let currentMedia = null;
    let mediaDisplayTimer = null;
    let countdownTimer = null;
    let mediaCheckInterval = null;

    // Initialize media display system
    function initMediaDisplay() {
        checkForMedia();
        // Check for media updates every 30 seconds
        mediaCheckInterval = setInterval(checkForMedia, 30000);
    }

    // Check for current media to display
    async function checkForMedia() {
        try {
            const response = await fetch('/api/current-media');
            const data = await response.json();
            
            if (data.media && data.media !== currentMedia) {
                displayMedia(data.media);
            }
            
            // Also check for countdown
            checkCountdown();
        } catch (error) {
            console.error('Error checking media:', error);
        }
    }

    // Display media in fullscreen overlay
    function displayMedia(media) {
        currentMedia = media;
        const overlay = document.getElementById('media-overlay');
        const content = document.getElementById('media-content');
        
        // Clear any existing timers
        clearTimeout(mediaDisplayTimer);
        clearTimeout(countdownTimer);
        
        // Hide countdown if showing
        document.getElementById('media-countdown').style.display = 'none';
        
        // Create media element
        let mediaElement;
        if (media.type === 'image') {
            mediaElement = document.createElement('img');
            mediaElement.src = media.file_url;
            mediaElement.alt = media.title;
            mediaElement.style.width = '100%';
            mediaElement.style.height = '100%';
            mediaElement.style.objectFit = 'contain';
        } else if (media.type === 'video') {
            mediaElement = document.createElement('video');
            mediaElement.src = media.file_url;
            mediaElement.autoplay = true;
            mediaElement.loop = true;
            mediaElement.muted = true;
            mediaElement.style.width = '100%';
            mediaElement.style.height = '100%';
            mediaElement.style.objectFit = 'contain';
        }
        
        // Clear content and add new media
        content.innerHTML = '';
        content.appendChild(mediaElement);
        
        // Show overlay
        overlay.style.display = 'flex';
        
        // Enter fullscreen
        if (!document.fullscreenElement) {
            toggleFullscreen();
        }
        
        // Set timer to hide media after duration
        mediaDisplayTimer = setTimeout(() => {
            hideMedia();
        }, media.display_duration * 1000);
    }

    // Hide media overlay
    function hideMedia() {
        const overlay = document.getElementById('media-overlay');
        overlay.style.display = 'none';
        currentMedia = null;
        clearTimeout(mediaDisplayTimer);
    }

    // Check for countdown timer
    async function checkCountdown() {
        try {
            const response = await fetch('/api/countdown-info');
            const data = await response.json();
            
            if (data.countdown && data.countdown.is_countdown_time) {
                showCountdown(data.countdown);
            } else {
                hideCountdown();
            }
        } catch (error) {
            console.error('Error checking countdown:', error);
        }
    }

    // Show countdown timer
    function showCountdown(countdownInfo) {
        // Don't show countdown if media is currently displaying
        if (currentMedia) return;
        
        const overlay = document.getElementById('media-overlay');
        const countdownDiv = document.getElementById('media-countdown');
        const prayerName = document.getElementById('countdown-prayer-name');
        const countdownTime = document.getElementById('countdown-time');
        
        prayerName.textContent = countdownInfo.prayer_name;
        
        // Show overlay
        overlay.style.display = 'flex';
        countdownDiv.style.display = 'flex';
        
        // Enter fullscreen
        if (!document.fullscreenElement) {
            toggleFullscreen();
        }
        
        // Start countdown timer
        startCountdownTimer(countdownInfo.prayer_time);
    }

    // Start countdown timer
    function startCountdownTimer(prayerTime) {
        const targetTime = new Date(prayerTime).getTime();
        
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = targetTime - now;
            
            if (distance < 0) {
                hideCountdown();
                // Refresh page to update prayer times
                location.reload();
                return;
            }
            
            const hours = Math.floor(distance / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('countdown-time').textContent = 
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        
        updateCountdown();
        countdownTimer = setInterval(updateCountdown, 1000);
    }

    // Hide countdown
    function hideCountdown() {
        const overlay = document.getElementById('media-overlay');
        const countdownDiv = document.getElementById('media-countdown');
        
        countdownDiv.style.display = 'none';
        overlay.style.display = 'none';
        
        clearInterval(countdownTimer);
    }

    // Initialize media display when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initMediaDisplay();
        initContentRotation();
    });

    // Content rotation for hadeeths and announcements
    function initContentRotation() {
        // Rotate hadeeths
        const hadeeths = document.querySelectorAll('.rotating-hadeeth');
        if (hadeeths.length > 1) {
            let currentHadeethIndex = 0;
            setInterval(() => {
                hadeeths[currentHadeethIndex].style.display = 'none';
                currentHadeethIndex = (currentHadeethIndex + 1) % hadeeths.length;
                hadeeths[currentHadeethIndex].style.display = 'block';
            }, {{ $settings['hadeeth_display_duration'] ?? 30 }} * 1000); // Use hadeeth display duration from settings
        }

        // Rotate announcements in pairs (two visible at a time, stacked)
        const announcements = document.querySelectorAll('.rotating-announcement');
        if (announcements.length > 2) {
            let pairStart = 0;
            function showPair(start) {
                announcements.forEach((el, i) => {
                    el.style.display = (i === start || i === (start + 1) % announcements.length) ? 'block' : 'none';
                });
            }
            showPair(pairStart);
            setInterval(() => {
                pairStart = (pairStart + 2) % announcements.length;
                showPair(pairStart);
            }, 15000); // 15 seconds per pair
        }
    }
</script>
@endsection
