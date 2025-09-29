<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masjid Timetable')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        body {
            font-family: {{ $settings['display_font_family'] ?? 'Arial, sans-serif' }};
            background-color: {{ $settings['display_background_color'] ?? '#ffffff' }};
            color: {{ $settings['display_text_color'] ?? '#000000' }};
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        
        /* Digital Information Board Layout */
        .digital-board {
            height: 100vh;
            background: linear-gradient(135deg, #0b3d0b 0%, #F8B803 55%, #8B7500 100%);
            display: flex;
            flex-direction: column;
            font-family: 'Courier New', monospace;
        }
        
        /* Top Header */
        .board-header {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-bottom: 2px solid #000;
            flex-shrink: 0;
        }
        
        .current-time-display {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 5px;
        }
        
        .time-large {
            font-size: 3rem;
            font-weight: bold;
            color: #000;
        }
        
        .time-seconds {
            font-size: 1.5rem;
            color: #666;
        }
        
        .time-period {
            font-size: 1.2rem;
            color: #666;
            margin-left: 10px;
        }
        
        .date-display, .islamic-date-display {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
        }
        
        /* Main Content Area */
        .board-main-content {
            flex-grow: 1;
            padding: 20px;
            display: flex;
            align-items: stretch;
        }
        
        /* Prayer Times Section */
        .prayer-times-section {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid #000;
            padding: 20px;
            height: 100%;
            position: relative;
        }
        
        /* Logo Background for Prayer Times Section */
        .prayer-times-section::before {
            content: '';
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 800px;
            height: 800px;
            background-image: var(--logo-bg-image);
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.2;
            z-index: 1;
            pointer-events: none;
        }
        
        .prayer-header {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
            position: relative;
            z-index: 2;
        }
        
        .prayer-col-header {
            font-size: 1.1rem;
        }
        
        .prayer-list {
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }
        
        .prayer-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
            text-align: center;
            align-items: center;
        }
        
        .prayer-name {
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .prayer-time, .prayer-jamaat {
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .next-prayer-info {
            text-align: center;
            font-style: italic;
            color: #666;
            position: relative;
            z-index: 2;
        }
        
        /* Hadeeth Section */
        .hadeeth-section {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid #000;
            padding: 20px;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .hadeeth-header {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            color: #000;
        }
        
        .hadeeth-content {
            height: calc(100% - 60px);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .hadeeth-text {
            text-align: center;
            width: 100%;
        }
        
        .arabic-hadeeth {
            font-family: 'Amiri', serif;
            font-size: 1.3rem;
            direction: rtl;
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .english-hadeeth {
            font-size: 1.1rem;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .hadeeth-reference {
            font-size: 0.9rem;
            color: #666;
            font-style: italic;
        }
        
        .hadeeth-placeholder {
            text-align: center;
            color: #999;
            font-style: italic;
        }
        
        /* Announcements Section */
        .announcements-section {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid #000;
            padding: 20px;
            height: 100%;
        }
        
        .announcements-header {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            color: #000;
        }
        
        .announcements-content {
            height: calc(100% - 60px);
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: flex-start;
            gap: 15px;
        }
        
        .announcement-item {
            text-align: center;
            width: 100%;
        }
        
        .announcement-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #000;
        }
        
        .announcement-text {
            font-size: 1rem;
            line-height: 1.4;
            color: #333;
        }
        
        .announcement-placeholder {
            text-align: center;
            color: #999;
            font-style: italic;
        }
        
        /* Bottom Additional Times */
        .board-bottom-times {
            background: rgba(255, 255, 255, 0.95);
            border-top: 2px solid #000;
            padding: 15px 20px;
            flex-shrink: 0;
        }
        
        .additional-times {
            display: flex;
            justify-content: space-around;
            align-items: center;
        }
        
        .time-item {
            text-align: center;
        }
        
        .time-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .time-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #000;
        }
        
        /* Scrolling Text Area */
        .scrolling-text-area {
            background: #000;
            color: #fff;
            padding: 10px 0;
            flex-shrink: 0;
        }
        
        .scroll-separator {
            height: 2px;
            background: #fff;
            margin-bottom: 10px;
        }
        
        .scrolling-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }
        
        .scroll-arrow {
            font-size: 1.5rem;
            color: #ff0000;
            font-weight: bold;
        }
        
        .scrolling-text {
            flex-grow: 1;
            text-align: center;
            overflow: hidden;
            white-space: nowrap;
        }
        
        .scroll-item {
            display: inline-block;
            margin-right: 50px;
            font-size: 1rem;
        }
        
        /* Logo Watermark */
        .logo-watermark {
            position: absolute;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0.3;
            z-index: 10;
        }
        
        .watermark-logo {
            max-height: 80px;
            max-width: 200px;
        }
        
        
        /* Fullscreen styles */
        .fullscreen-mode {
            background: linear-gradient(135deg, #0b3d0b 0%, #F8B803 55%, #8B7500 100%) !important;
        }
        
        /* Hide header fullscreen button in fullscreen mode */
        .fullscreen-mode #fullscreenBtn {
            display: none;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .time-large {
                font-size: 2.5rem;
            }
            
            .prayer-time, .prayer-jamaat {
                font-size: 1.5rem;
            }
            
            .arabic-hadeeth {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 768px) {
            .board-header {
                padding: 15px;
            }
            
            .time-large {
                font-size: 2rem;
            }
            
            .board-main-content {
                padding: 15px;
            }
            
            .additional-times {
                flex-wrap: wrap;
                gap: 10px;
            }
        }
        
        /* Animation for scrolling text */
        @keyframes scroll-left {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        
        .scrolling-text {
            animation: scroll-left 30s linear infinite;
        }

        /* Media Display Overlay Styles */
        .media-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: #000;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .media-container {
            width: 100%;
            height: 100%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .media-content {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .media-content img,
        .media-content video {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        /* Countdown Timer Styles */
        .media-countdown {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0b3d0b 0%, #8B7500 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-family: 'Courier New', monospace;
        }

        .countdown-timer {
            text-align: center;
            padding: 40px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .countdown-label {
            font-size: 2rem;
            margin-bottom: 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .countdown-prayer {
            font-size: 4rem;
            margin-bottom: 30px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .countdown-time {
            font-size: 6rem;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.5);
            letter-spacing: 5px;
        }

        /* Responsive countdown styles */
        @media (max-width: 1200px) {
            .countdown-label {
                font-size: 1.5rem;
            }
            
            .countdown-prayer {
                font-size: 3rem;
            }
            
            .countdown-time {
                font-size: 4rem;
            }
        }

        @media (max-width: 768px) {
            .countdown-timer {
                padding: 20px;
            }
            
            .countdown-label {
                font-size: 1.2rem;
            }
            
            .countdown-prayer {
                font-size: 2.5rem;
            }
            
            .countdown-time {
                font-size: 3rem;
            }
        }
    </style>
    
    @yield('styles')
</head>
<body>
    @yield('content')
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Auto-refresh functionality
        const autoRefreshInterval = {{ $settings['auto_refresh_interval'] ?? 60 }} * 1000;
        
        function refreshData() {
            // Refresh prayer times
            fetch('/api/prayer-times')
                .then(response => response.json())
                .then(data => {
                    // Update prayer times display
                    console.log('Prayer times updated', data);
                });
                
            // Refresh announcements
            fetch('/api/announcements')
                .then(response => response.json())
                .then(data => {
                    // Update announcements display
                    console.log('Announcements updated', data);
                });
                
            // Refresh next prayer
            fetch('/api/next-prayer')
                .then(response => response.json())
                .then(data => {
                    // Update next prayer countdown
                    console.log('Next prayer updated', data);
                });
        }
        
        // Set up auto-refresh
        setInterval(refreshData, autoRefreshInterval);
        
        // Update current time every second
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour12: true,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }
        
        setInterval(updateCurrentTime, 1000);
        updateCurrentTime(); // Initial call
    </script>
    
    @yield('scripts')
</body>
</html>
