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
        }
        
        .prayer-times {
            font-size: {{ $settings['prayer_time_font_size'] ?? '24' }}px;
        }
        
        .announcement-scroll {
            animation: scroll-left {{ $settings['announcement_scroll_speed'] ?? '3' }}s linear infinite;
        }
        
        @keyframes scroll-left {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        
        .islamic-pattern {
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="islamic" patternUnits="userSpaceOnUse" width="20" height="20"><path d="M10 10 L15 5 L10 0 L5 5 Z" fill="%23d4af37" opacity="0.1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23islamic)"/></svg>');
        }
        
        .hadeeth-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            border: 2px solid #d4af37;
        }
        
        .prayer-time-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin: 10px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .next-prayer {
            background: linear-gradient(135deg, #ff7e5f 0%, #feb47b 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        
        .masjid-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 30px 0;
            text-align: center;
            margin-bottom: 30px;
        }
        
        /* Fullscreen styles */
        :fullscreen {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        :-webkit-full-screen {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        :-moz-full-screen {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .fullscreen-mode {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }
        
        .fullscreen-mode .masjid-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        }
        
        /* Hide certain elements in fullscreen */
        .fullscreen-mode .navbar,
        .fullscreen-mode .admin-links {
            display: none !important;
        }
        
        /* Make prayer times larger in fullscreen */
        .fullscreen-mode .prayer-time-card {
            font-size: 1.2em;
            padding: 20px;
        }
        
        .fullscreen-mode .prayer-times {
            font-size: 1.3em;
        }
        
        .fullscreen-mode .current-time {
            font-size: 3.5rem !important;
        }
        
        .fullscreen-mode .next-prayer {
            font-size: 1.2em;
        }
        
        /* Fullscreen controls */
        .fullscreen-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            display: none; /* Hidden by default */
            transition: opacity 0.5s ease;
        }
        
        .fullscreen-mode .fullscreen-controls {
            display: block;
            opacity: 0;
        }
        
        .fullscreen-mode:hover .fullscreen-controls {
            opacity: 1;
        }
        
        /* Show controls for 3 seconds when entering fullscreen */
        .fullscreen-mode.show-controls .fullscreen-controls {
            opacity: 1;
        }
        
        /* TV-Specific Styles for Single-Screen Display */
        .tv-layout {
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .tv-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 15px;
            flex-shrink: 0;
        }
        
        .tv-main-content {
            flex-grow: 1;
            padding: 20px;
            overflow: hidden;
        }
        
        /* Prayer Times Grid for TV */
        .prayer-times-tv {
            height: 100%;
        }
        
        .prayer-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 15px;
            height: 250px;
            margin-bottom: 20px;
        }
        
        .prayer-time-tv {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .prayer-time-tv::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .prayer-time-tv:hover::before {
            opacity: 1;
        }
        
        .prayer-time-tv .prayer-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .prayer-time-tv .prayer-time {
            font-size: 1.4rem;
            font-weight: 700;
        }
        
        /* Color coding for different prayers */
        .prayer-time-tv.fajr { background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%); }
        .prayer-time-tv.sunrise { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .prayer-time-tv.zohar { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .prayer-time-tv.asr { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .prayer-time-tv.maghrib { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .prayer-time-tv.isha { background: linear-gradient(135deg, #30cfd0 0%, #91a7ff 100%); }
        
        /* Jumah times styling */
        .jumah-times {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 1.1rem;
            margin-top: 15px;
        }
        
        /* Compact Hadeeth for TV */
        .hadeeth-tv {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 12px;
            padding: 15px;
            border: 2px solid #d4af37;
            height: fit-content;
            max-height: 200px;
            overflow: hidden;
        }
        
        .hadeeth-tv .arabic-text {
            font-size: 1.1rem !important;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .hadeeth-tv .english-text {
            font-size: 0.95rem;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        .hadeeth-tv .reference {
            font-size: 0.8rem;
        }
        
        /* Next Prayer TV styling */
        .next-prayer-tv {
            background: linear-gradient(135deg, #ff7e5f 0%, #feb47b 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        
        .next-prayer-tv .prayer-name {
            font-size: 1.8rem;
            margin: 10px 0;
        }
        
        .next-prayer-tv .prayer-time {
            font-size: 1.5rem;
            margin: 10px 0;
        }
        
        .countdown-tv {
            font-size: 1.8rem;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 15px;
            border-radius: 8px;
            display: inline-block;
            min-width: 120px;
        }
        
        /* Announcements TV styling */
        .announcements-tv {
            max-height: 300px;
            overflow: hidden;
        }
        
        .announcements-container {
            max-height: 250px;
            overflow-y: auto;
            padding-right: 10px;
        }
        
        .announcement-tv {
            padding: 10px;
            border-radius: 8px;
            border-left: 4px solid #d4af37;
            background: rgba(255, 255, 255, 0.9) !important;
            color: #333 !important;
            font-size: 0.9rem;
        }
        
        .announcement-tv h6 {
            font-size: 1rem;
            color: #2c3e50;
        }
        
        /* TV Ticker styling */
        .tv-ticker {
            background: #2c3e50;
            color: white;
            padding: 8px 0;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
            height: 40px;
        }
        
        .tv-ticker .announcement-scroll {
            animation: scroll-left 20s linear infinite;
            white-space: nowrap;
            font-size: 1rem;
            line-height: 24px;
        }
        
        /* Responsive adjustments for different TV sizes */
        @media (min-height: 900px) {
            .prayer-grid {
                height: 300px;
            }
            
            .prayer-time-tv .prayer-name {
                font-size: 1.4rem;
            }
            
            .prayer-time-tv .prayer-time {
                font-size: 1.6rem;
            }
            
            .hadeeth-tv {
                max-height: 250px;
            }
        }
        
        @media (max-height: 768px) {
            .tv-main-content {
                padding: 15px;
            }
            
            .prayer-grid {
                height: 200px;
                gap: 10px;
            }
            
            .prayer-time-tv .prayer-name {
                font-size: 1rem;
            }
            
            .prayer-time-tv .prayer-time {
                font-size: 1.2rem;
            }
            
            .hadeeth-tv {
                max-height: 150px;
                padding: 10px;
            }
            
            .announcements-tv {
                max-height: 200px;
            }
        }
        
        /* Fullscreen TV mode enhancements */
        .fullscreen-mode .tv-layout {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .fullscreen-mode .tv-header {
            padding: 10px 15px;
        }
        
        .fullscreen-mode .prayer-grid {
            height: 280px;
        }
        
        .fullscreen-mode .prayer-time-tv .prayer-name {
            font-size: 1.3rem;
        }
        
        .fullscreen-mode .prayer-time-tv .prayer-time {
            font-size: 1.5rem;
        }
        
        .fullscreen-mode .countdown-tv {
            font-size: 2rem;
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
