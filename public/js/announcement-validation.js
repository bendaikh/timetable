/**
 * Announcement Form Validation & Character Limiting
 * Mosque Prayer Time Web App
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize character counter if on announcement form
    const contentInput = document.getElementById('content');
    const charCountDisplay = document.getElementById('char-count');
    const charWarning = document.getElementById('char-warning');
    const warningText = document.getElementById('warning-text');
    const saveButton = document.querySelector('form button[type="submit"]');

    if (contentInput) {
        /**
         * Update character counter and validation status
         */
        function updateCharCounter() {
            const charCount = contentInput.value.length;
            
            // Update display
            if (charCountDisplay) {
                charCountDisplay.textContent = charCount;
            }
        }
        contentInput.addEventListener('input', updateCharCounter);

        /**
         * Validate on form submit
         */
        const form = contentInput.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const charCount = contentInput.value.length;
                
                if (charCount === 0) {
                    e.preventDefault();
                    alert('Please enter some content for the announcement.');
                    contentInput.focus();
                    return false;
                }

                return true;
            });
        }

        // Initial count on page load
        updateCharCounter();
    }

    /**
     * Font Size Preview - Shows how announcement will look on display
     */
    const fontSizeInput = document.getElementById('font_size');
    if (fontSizeInput) {
        const previewContainer = document.getElementById('announcement-preview');
        
        function updateFontSizePreview() {
            const fontSize = fontSizeInput.value;
            
            // Show warning if font is very large and text is also long
            if (contentInput && fontSize > 60 && contentInput.value.length > 150) {
                if (charWarning) {
                    warningText.textContent = `⚠️ Large font (${fontSize}px) with this text length may overflow on 85" TV. Consider reducing font size or text length.`;
                    charWarning.style.display = 'block';
                }
            }
        }

        fontSizeInput.addEventListener('change', updateFontSizePreview);
        if (contentInput) {
            contentInput.addEventListener('input', updateFontSizePreview);
        }
    }

    /**
     * Live Preview for Announcement
     */
    if (contentInput) {
        const previewTitle = document.getElementById('preview-title');
        const previewText = document.getElementById('preview-text');
        const previewBox = document.getElementById('announcement-preview-box');

        function updatePreview() {
            const titleInput = document.getElementById('title');
            if (titleInput && previewTitle) {
                previewTitle.textContent = titleInput.value || 'Announcement Title';
            }
            if (previewText) {
                previewText.textContent = contentInput.value || 'Announcement text will appear here...';
            }
        }

        const titleInput = document.getElementById('title');
        if (titleInput) {
            titleInput.addEventListener('input', updatePreview);
        }
        contentInput.addEventListener('input', updatePreview);

        if (fontSizeInput) {
            fontSizeInput.addEventListener('change', function() {
                if (previewText) {
                    previewText.style.fontSize = fontSizeInput.value + 'px';
                }
            });
        }

        updatePreview();
    }

    /**
     * Scroll Speed Indicator
     */
    const scrollSpeedInput = document.getElementById('scroll_speed');
    const scrollSpeedLabel = document.getElementById('scroll-speed-label');
    
    if (scrollSpeedInput && scrollSpeedLabel) {
        const speedDescriptions = {
            1: '🐌 Very Slow',
            2: '🐢 Slow',
            3: '⏱️ Normal',
            4: '🏃 Fast',
            5: '⚡ Very Fast',
            6: '🚀 Super Fast',
            7: '💨 Extreme',
            8: '🔥 Ultra',
            9: '⚙️ Insane',
            10: '💥 Maximum'
        };

        function updateSpeedLabel() {
            const speed = scrollSpeedInput.value;
            scrollSpeedLabel.textContent = speedDescriptions[speed] || 'Normal';
        }

        scrollSpeedInput.addEventListener('change', updateSpeedLabel);
        updateSpeedLabel();
    }

    /**
     * Display Duration Indicator
     */
    const durationInput = document.getElementById('display_duration');
    const durationLabel = document.getElementById('duration-label');
    
    if (durationInput && durationLabel) {
        function updateDurationLabel() {
            const duration = durationInput.value;
            if (duration < 5) {
                durationLabel.textContent = '⚡ Very Brief';
            } else if (duration < 15) {
                durationLabel.textContent = '📢 Short';
            } else if (duration < 30) {
                durationLabel.textContent = '⏱️ Medium';
            } else if (duration < 60) {
                durationLabel.textContent = '📖 Long';
            } else {
                durationLabel.textContent = '📚 Very Long';
            }
        }

        durationInput.addEventListener('change', updateDurationLabel);
        updateDurationLabel();
    }
});

/**
 * Utility function to safely truncate text for TV display
 * @param {string} text - Text to truncate
 * @param {number} maxChars - Maximum characters
 * @returns {string} Truncated text with ellipsis
 */
function truncateForDisplay(text, maxChars = 300) {
    if (text.length <= maxChars) {
        return text;
    }
    return text.substring(0, maxChars - 3) + '...';
}

/**
 * Utility function to estimate text fit on TV
 * @param {number} charCount - Number of characters
 * @param {number} fontSize - Font size in pixels
 * @returns {boolean} True if text should fit safely
 */
function willTextFitOnTV(charCount, fontSize) {
    // Rule: Large fonts (>60px) can fit ~150 chars
    // Normal fonts (12-60px) can fit 300+ chars
    if (fontSize > 60) {
        return charCount <= 150;
    }
    return charCount <= 300;
}

/**
 * Estimate display lines
 * @param {string} text - Text to display
 * @param {number} fontSize - Font size in pixels
 * @returns {number} Estimated number of lines
 */
function estimateDisplayLines(text, fontSize) {
    const charsPerLine = fontSize > 60 ? 20 : 50;
    const lines = Math.ceil(text.length / charsPerLine);
    return lines;
}
