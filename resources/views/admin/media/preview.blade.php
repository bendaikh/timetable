<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Preview - {{ $medium->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            font-family: 'Arial', sans-serif;
            overflow: hidden;
            height: 100vh;
        }

        .preview-container {
            position: relative;
            width: 100vw;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .media-content {
            position: relative;
            max-width: 90vw;
            max-height: 90vh;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .media-content img {
            width: 100%;
            height: auto;
            display: block;
            max-height: 90vh;
            object-fit: contain;
        }

        .media-content video {
            width: 100%;
            height: auto;
            display: block;
            max-height: 90vh;
        }

        .preview-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(180deg, rgba(0,0,0,0.7) 0%, transparent 30%);
            padding: 20px;
            z-index: 10;
        }

        .preview-info {
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .media-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .media-details {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .duration-indicator {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
        }

        .preview-controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 15px;
            z-index: 10;
        }

        .control-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            font-size: 0.9rem;
            cursor: pointer;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .control-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }

        .progress-bar-container {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
        }

        .progress-bar {
            height: 100%;
            background: #00ff88;
            width: 0%;
            animation: progress-animation linear;
        }

        @keyframes progress-animation {
            from { width: 0%; }
            to { width: 100%; }
        }

        .preview-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: rgba(255, 255, 255, 0.1);
            font-size: 3rem;
            font-weight: bold;
            pointer-events: none;
            z-index: 5;
        }

        /* Fullscreen styles */
        .fullscreen-mode {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 9999;
        }

        /* Loading animation */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 20;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid #00ff88;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="preview-container" id="preview-container">
        <!-- Loading overlay -->
        <div class="loading-overlay" id="loading-overlay">
            <div class="loading-spinner"></div>
        </div>

        <!-- Preview watermark -->
        <div class="preview-watermark">PREVIEW</div>

        <!-- Media content -->
        <div class="media-content" id="media-content">
            <div class="preview-overlay">
                <div class="preview-info">
                    <div>
                        <div class="media-title">{{ $medium->title }}</div>
                        <div class="media-details">
                            {{ ucfirst($medium->type) }} • Priority: {{ $medium->priority }}
                            @if($medium->description)
                                <br>{{ $medium->description }}
                            @endif
                        </div>
                    </div>
                    <div class="duration-indicator">
                        <i class="bi bi-clock"></i> {{ $medium->display_duration }}s
                    </div>
                </div>
            </div>

            @if($medium->isImage())
                <img src="{{ $medium->file_url }}" alt="{{ $medium->title }}" id="media-element">
            @else
                <video id="media-element" muted>
                    <source src="{{ $medium->file_url }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            @endif

            <!-- Progress bar -->
            <div class="progress-bar-container">
                <div class="progress-bar" id="progress-bar"></div>
            </div>
        </div>

        <!-- Controls -->
        <div class="preview-controls">
            <button class="control-btn" onclick="startPreview()" id="play-btn">
                <i class="bi bi-play-fill"></i> Start Preview
            </button>
            <button class="control-btn" onclick="toggleFullscreen()" id="fullscreen-btn">
                <i class="bi bi-arrows-fullscreen"></i> Fullscreen
            </button>
            <a href="{{ route('admin.media.show', $medium) }}" class="control-btn">
                <i class="bi bi-arrow-left"></i> Back to Media
            </a>
            <a href="{{ route('admin.media.edit', $medium) }}" class="control-btn">
                <i class="bi bi-pencil"></i> Edit Media
            </a>
        </div>
    </div>

    <script>
        let previewActive = false;
        let previewTimer = null;
        const duration = {{ $medium->display_duration * 1000 }}; // Convert to milliseconds
        
        document.addEventListener('DOMContentLoaded', function() {
            // Hide loading overlay
            setTimeout(() => {
                document.getElementById('loading-overlay').style.display = 'none';
            }, 500);
        });

        function startPreview() {
            if (previewActive) {
                stopPreview();
                return;
            }

            previewActive = true;
            const playBtn = document.getElementById('play-btn');
            const progressBar = document.getElementById('progress-bar');
            const mediaElement = document.getElementById('media-element');

            // Update button
            playBtn.innerHTML = '<i class="bi bi-stop-fill"></i> Stop Preview';

            // Start progress animation
            progressBar.style.animationDuration = `${duration}ms`;
            progressBar.style.width = '100%';

            // If it's a video, play it
            if (mediaElement.tagName === 'VIDEO') {
                mediaElement.currentTime = 0;
                mediaElement.play();
                
                // Loop the video if it's shorter than display duration
                mediaElement.addEventListener('ended', function() {
                    if (previewActive) {
                        mediaElement.currentTime = 0;
                        mediaElement.play();
                    }
                });
            }

            // Set timer to stop preview
            previewTimer = setTimeout(() => {
                stopPreview();
            }, duration);
        }

        function stopPreview() {
            previewActive = false;
            const playBtn = document.getElementById('play-btn');
            const progressBar = document.getElementById('progress-bar');
            const mediaElement = document.getElementById('media-element');

            // Update button
            playBtn.innerHTML = '<i class="bi bi-play-fill"></i> Start Preview';

            // Reset progress bar
            progressBar.style.width = '0%';
            progressBar.style.animationDuration = '0s';

            // Stop video if playing
            if (mediaElement.tagName === 'VIDEO') {
                mediaElement.pause();
                mediaElement.currentTime = 0;
            }

            // Clear timer
            if (previewTimer) {
                clearTimeout(previewTimer);
                previewTimer = null;
            }
        }

        function toggleFullscreen() {
            const container = document.getElementById('preview-container');
            const fullscreenBtn = document.getElementById('fullscreen-btn');

            if (!document.fullscreenElement) {
                container.requestFullscreen().then(() => {
                    container.classList.add('fullscreen-mode');
                    fullscreenBtn.innerHTML = '<i class="bi bi-fullscreen-exit"></i> Exit Fullscreen';
                });
            } else {
                document.exitFullscreen().then(() => {
                    container.classList.remove('fullscreen-mode');
                    fullscreenBtn.innerHTML = '<i class="bi bi-arrows-fullscreen"></i> Fullscreen';
                });
            }
        }

        // Handle fullscreen change events
        document.addEventListener('fullscreenchange', function() {
            const container = document.getElementById('preview-container');
            const fullscreenBtn = document.getElementById('fullscreen-btn');
            
            if (!document.fullscreenElement) {
                container.classList.remove('fullscreen-mode');
                fullscreenBtn.innerHTML = '<i class="bi bi-arrows-fullscreen"></i> Fullscreen';
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            switch(e.key) {
                case ' ':
                case 'Enter':
                    e.preventDefault();
                    startPreview();
                    break;
                case 'Escape':
                    if (document.fullscreenElement) {
                        document.exitFullscreen();
                    } else {
                        stopPreview();
                    }
                    break;
                case 'f':
                case 'F':
                    toggleFullscreen();
                    break;
            }
        });

        // Auto-start preview after 2 seconds
        setTimeout(() => {
            if (!previewActive) {
                startPreview();
            }
        }, 2000);
    </script>
</body>
</html>
