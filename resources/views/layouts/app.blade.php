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
            display: flex;
            flex-direction: column;
            font-family: 'Courier New', monospace;
        }
        
        /* Unified Container for Consistent Width */
        .unified-container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 0 15px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        /* Top Header */
        .board-header {
            background: rgba(255, 255, 255, 0.95);
            padding: clamp(15px, 3vh, 25px);
            border-bottom: clamp(1px, 0.2vw, 3px) solid #000;
            flex-shrink: 0;
            width: 100%;
            box-sizing: border-box;
        }
        
        .current-time-display {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 5px;
        }
        
        .time-large {
            font-size: clamp(2rem, 3vw, 3rem);
            font-weight: bold;
            color: #000;
        }
        
        .time-seconds {
            font-size: clamp(1rem, 1.5vw, 1.5rem);
            color: #666;
        }
        
        .time-period {
            font-size: clamp(1rem, 1.3vw, 1.3rem);
            color: #666;
            margin-left: clamp(5px, 0.8vw, 10px);
        }
        
        .date-display, .islamic-date-display {
            font-size: clamp(1.2rem, 1.5vw, 1.6rem);
            font-weight: bold;
            color: #000;
        }
        
        /* Main Content Area */
        .board-main-content {
            flex-grow: 1;
            padding: 20px 0;
            display: flex;
            align-items: stretch;
            gap: 15px;
            width: 100%;
            box-sizing: border-box;
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
        }
        
        .prayer-list {
            margin-bottom: clamp(15px, 2vh, 25px);
            position: relative;
            z-index: 2;
        }
        
        .prayer-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: clamp(8px, 1vw, 12px);
            margin-bottom: clamp(8px, 1.5vh, 12px);
            text-align: center;
            align-items: center;
        }
        
        .prayer-name {
            font-weight: bold;
            font-size: clamp(1.2rem, 1.8vw, 1.8rem);
        }
        
        .prayer-time, .prayer-jamaat {
            font-size: clamp(1.4rem, 2vw, 2rem);
            font-weight: bold;
        }
        
        .next-prayer-info {
            text-align: center;
            font-style: italic;
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
            font-size: clamp(1.2rem, 1.5vw, 1.6rem);
            font-weight: bold;
            text-align: center;
            margin-bottom: clamp(15px, 2vh, 20px);
            color: #000;
        }
        
        .hadeeth-content {
            height: calc(100% - 60px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(10px, 1.5vh, 15px);
        }
        
        .hadeeth-text {
            text-align: center;
            width: 100%;
            max-width: 100%;
        }
        
        .arabic-hadeeth {
            font-family: 'Amiri', serif;
            font-size: clamp(1rem, 1.5vw, 1.4rem);
            direction: rtl;
            margin-bottom: clamp(10px, 1.5vh, 15px);
            line-height: 1.6;
            padding: 0 clamp(5px, 0.8vw, 10px);
        }
        
        .english-hadeeth {
            font-size: clamp(0.9rem, 1.3vw, 1.2rem);
            margin-bottom: clamp(8px, 1.5vh, 12px);
            line-height: 1.4;
            padding: 0 clamp(5px, 0.8vw, 10px);
        }
        
        .hadeeth-reference {
            font-size: clamp(0.8rem, 1.1vw, 1rem);
            color: #666;
            font-style: italic;
            padding: 0 clamp(5px, 0.8vw, 10px);
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
            font-size: clamp(1.2rem, 1.5vw, 1.6rem);
            font-weight: bold;
            text-align: center;
            margin-bottom: clamp(15px, 2vh, 20px);
        }
        
        .announcements-content {
            height: calc(100% - 60px);
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: flex-start;
            gap: clamp(12px, 1.5vh, 15px);
            padding: clamp(5px, 1vh, 10px);
            overflow: hidden;
        }
        
        .announcements-content.dynamic-layout {
            justify-content: space-evenly;
        }
        
        .announcements-content.centered-layout {
            justify-content: center;
        }
        
        .announcements-content.compact-layout {
            gap: clamp(8px, 1vh, 12px);
        }
        
        .announcement-item {
            text-align: left;
            width: 100%;
            padding: clamp(8px, 1vh, 12px);
            background: rgba(255, 255, 255, 0.1);
            border-radius: clamp(5px, 0.8vh, 8px);
            border-left: clamp(3px, 0.4vw, 5px) solid #0b3d0b;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }
        
        .announcement-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-1px);
        }
        
        .announcement-title {
            font-size: clamp(1rem, 1.3vw, 1.3rem);
            font-weight: bold;
            margin-bottom: clamp(8px, 1vh, 10px);
        }
        
        .announcement-text {
            line-height: 1.4;
        }
        
        .announcement-placeholder {
            text-align: center;
            color: #999;
            font-style: italic;
        }
        
        /* Subtle visual enhancements for empty space */
        .announcements-content.centered-layout::before,
        .announcements-content.centered-layout::after {
            content: '';
            flex: 1;
            min-height: 20px;
        }
        
        .announcements-content.dynamic-layout .announcement-item {
            flex: 1;
            min-height: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        /* Responsive adjustments for different screen sizes */
        @media (max-height: 600px) {
            .announcements-content {
                gap: clamp(6px, 1vh, 10px);
            }
            
            .announcement-item {
                padding: clamp(6px, 0.8vh, 10px);
            }
        }
        
        @media (min-height: 800px) {
            .announcements-content {
                gap: clamp(15px, 2vh, 20px);
            }
            
            .announcement-item {
                padding: clamp(10px, 1.2vh, 15px);
            }
        }
        
        /* Bottom Additional Times */
        .board-bottom-times {
            background: rgba(255, 255, 255, 0.95);
            border-top: clamp(1px, 0.2vw, 3px) solid #000;
            padding: clamp(12px, 2.5vh, 20px) 0;
            flex-shrink: 0;
            width: 100%;
            box-sizing: border-box;
            margin: 0;
            /* Ensure consistent width with sliding text box */
            max-width: 100%;
        }
        
        .additional-times {
            display: flex;
            justify-content: space-around;
            align-items: center;
            gap: clamp(10px, 2vw, 20px);
        }
        
        .time-item {
            text-align: center;
            flex: 1;
        }
        
        .time-label {
            font-size: clamp(0.8rem, 1.5vw, 1.2rem);
            color: #666;
            margin-bottom: clamp(3px, 0.5vh, 8px);
            font-weight: bold;
        }
        
        .time-value {
            font-size: clamp(1.4rem, 3vw, 2.2rem);
            font-weight: bold;
            color: #000;
        }
        
        /* Scrolling Text Area */
        .scrolling-text-area {
            background: #F8B803;
            color: #fff;
            padding: clamp(15px, 2.5vh, 25px) 0;
            flex-shrink: 0;
            box-shadow: 0 -3px 10px rgba(0, 0, 0, 0.3);
            width: 100%;
            box-sizing: border-box;
            margin: 0;
            /* Ensure consistent width with special times box */
            max-width: 100%;
        }
        
        .scroll-separator {
            height: clamp(1px, 0.2vh, 3px);
            background: #fff;
            margin-bottom: clamp(5px, 1vh, 12px);
        }
        
        .scrolling-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: clamp(20px, 3vw, 35px);
        }
        
        .scroll-arrow {
            font-size: clamp(1.8rem, 3.5vw, 2.5rem);
            color: #ff0000;
            font-weight: 900;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        .scrolling-text {
            flex-grow: 1;
            overflow: hidden;
            white-space: nowrap;
            position: relative;
        }
        
        .scroll-wrapper {
            display: inline-block;
            animation: scroll-left 45s linear infinite;
            white-space: nowrap;
            will-change: transform;
        }
        
        .scroll-item {
            display: inline-block;
            margin-right: clamp(50px, 8vw, 100px);
            font-size: clamp(1.5rem, 3vw, 2.5rem);
            font-weight: 900;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.7);
            letter-spacing: 1px;
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
        
        /* Responsive adjustments for smaller screens */
        @media (max-width: 1200px) {
            .unified-container {
                padding: 0 10px;
            }
            
            .board-main-content {
                padding: clamp(10px, 2vh, 20px) 0;
            }
            
            .prayer-times-section,
            .hadeeth-section,
            .announcements-section {
                padding: clamp(12px, 2.5vh, 25px);
            }
            
            .board-bottom-times,
            .scrolling-text-area {
                padding: clamp(10px, 2vh, 18px) 0;
            }
        }
        
        /* Responsive adjustments for very large screens (4K and above) */
        @media (min-width: 2560px) {
            .unified-container {
                padding: 0 25px;
            }
            
            .board-main-content {
                padding: 30px 0;
                gap: 20px;
            }
            
            .prayer-times-section,
            .hadeeth-section,
            .announcements-section {
                padding: 30px;
            }
            
            .board-bottom-times,
            .scrolling-text-area {
                padding: 25px 0;
            }
            
            .prayer-name {
                font-size: 2rem;
            }
            
            .prayer-time, .prayer-jamaat {
                font-size: 2.2rem;
            }
            
            .hadeeth-header,
            .announcements-header {
                font-size: 1.8rem;
            }
            
            .arabic-hadeeth {
                font-size: 1.6rem;
            }
            
            .english-hadeeth {
                font-size: 1.4rem;
            }
            
            .announcement-title {
                font-size: 1.5rem;
            }
            
            .announcement-text {
            }
            
            .time-large {
                font-size: 3.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .unified-container {
                padding: 0 8px;
            }
            
            .board-header {
                padding: clamp(10px, 2vh, 20px);
            }
            
            .board-main-content {
                padding: clamp(10px, 2vh, 20px) 0;
                flex-direction: column;
                gap: clamp(8px, 1.5vh, 15px);
            }
            
            .board-bottom-times,
            .scrolling-text-area {
                padding: clamp(8px, 1.5vh, 15px) 0;
            }
            
            .prayer-times-section,
            .hadeeth-section,
            .announcements-section {
                height: auto;
                min-height: clamp(200px, 30vh, 300px);
            }
            
            .additional-times {
                flex-wrap: wrap;
                gap: clamp(8px, 1.5vh, 15px);
            }
        }
        
        /* Animation for scrolling text */
        @keyframes scroll-left {
            0% { 
                transform: translateX(0%); 
            }
            100% { 
                transform: translateX(-100%); 
            }
        }
        
        /* Pause animation on hover */
        .scrolling-text:hover .scroll-wrapper {
            animation-play-state: paused;
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
            animation: slideInFromRight 1s ease-out;
        }

        @keyframes slideInFromRight {
            0% {
                transform: translateX(100%);
                opacity: 0;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
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

        /* Countdown Popup Styles */
        .countdown-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: transparent;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .countdown-popup-content {
            background: linear-gradient(135deg, #1e4d2b 0%, #2d5a3d 50%, #1e4d2b 100%);
            border: 4px solid #F8B803;
            border-radius: 20px;
            padding: 40px 60px;
            text-align: center;
            color: white;
            font-family: 'Arial', sans-serif;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            animation: countdownPopupAppear 0.8s ease-out;
            max-width: 500px;
            width: 90%;
            pointer-events: auto;
        }

        .countdown-popup-header {
            margin-bottom: 20px;
        }

        .countdown-popup-title {
            font-size: 2.5rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            margin-bottom: 10px;
        }

        .countdown-popup-body {
            margin: 30px 0;
        }

        .countdown-popup-timer {
            font-size: 8rem;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            color: #F8B803;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.8);
            margin-bottom: 10px;
            animation: countdownPulse 1s ease-in-out infinite alternate;
        }

        .countdown-popup-label {
            font-size: 1.8rem;
            font-weight: 600;
            color: #ffffff;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
        }

        .countdown-popup-footer {
            margin-top: 20px;
        }

        .countdown-popup-prayer {
            font-size: 2rem;
            font-weight: bold;
            color: #F8B803;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        }

        @keyframes countdownPopupAppear {
            0% {
                opacity: 0;
                transform: scale(0.8) translateY(-50px);
            }
            50% {
                opacity: 0.8;
                transform: scale(1.05) translateY(-10px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes countdownPulse {
            0% {
                transform: scale(1);
                color: #F8B803;
            }
            100% {
                transform: scale(1.1);
                color: #FFD700;
            }
        }

        /* Responsive countdown popup styles */
        @media (max-width: 1200px) {
            .countdown-popup-content {
                padding: 30px 40px;
                max-width: 400px;
            }
            
            .countdown-popup-title {
                font-size: 2rem;
            }
            
            .countdown-popup-timer {
                font-size: 6rem;
            }
            
            .countdown-popup-prayer {
                font-size: 1.6rem;
            }
        }

        @media (max-width: 768px) {
            .countdown-popup-content {
                padding: 20px 30px;
                max-width: 350px;
            }
            
            .countdown-popup-title {
                font-size: 1.6rem;
            }
            
            .countdown-popup-timer {
                font-size: 4.5rem;
            }
            
            .countdown-popup-label {
                font-size: 1.4rem;
            }
            
            .countdown-popup-prayer {
                font-size: 1.4rem;
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
