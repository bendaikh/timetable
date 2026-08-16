@extends('layouts.admin')

@section('title', 'Add New Announcement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Add New Announcement</h1>
                <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Announcements
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <!-- Layout: Form on left, Preview on right -->
                    <div class="row">
                        <div class="col-md-6">
                    
                    <form action="{{ route('admin.announcements.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title <span class="text-muted">(optional)</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title') }}" maxlength="255" placeholder="Leave blank for body-only announcement">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="title_font_size" class="form-label">Title Font Size (rem) <span class="text-danger">*</span></label>
                                    @php
                                        $titleFontSizeValue = old('title_font_size', 2.25);
                                        $titleFontSizeValue = is_numeric($titleFontSizeValue) ? (float) $titleFontSizeValue : 2.25;
                                    @endphp
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="range" class="form-range flex-grow-1" 
                                               id="title_font_size_range" min="0.75" max="10" step="0.05" value="{{ $titleFontSizeValue }}">
                                        <input type="number" class="form-control" style="width: 90px;" 
                                               id="title_font_size" name="title_font_size" value="{{ $titleFontSizeValue }}" 
                                               min="0.75" max="10" step="0.05" required>
                                        <span class="input-group-text" id="title-font-size-label">rem</span>
                                    </div>
                                    <small class="form-text d-block mt-2">Recommended: 1.5 - 4 rem (controls announcement title size on the display)</small>
                                    @error('title_font_size')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('content') is-invalid @enderror" 
                                              id="content" name="content" rows="6" required>{{ old('content') }}</textarea>
                                    <div class="form-text d-flex justify-content-between align-items-center mt-2">
                                        <span>
                                            Press <kbd>Enter</kbd> for a new line on the mosque screen.
                                            Characters: <strong id="char-count">0</strong>
                                        </span>
                                    </div>
                                    @error('content')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="display_duration" class="form-label">Display Duration (seconds) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('display_duration') is-invalid @enderror" 
                                           id="display_duration" name="display_duration" value="{{ old('display_duration', 30) }}" 
                                           min="1" max="120" required>
                                    <div class="form-text">How long this announcement stays on screen before the next one (1–120 seconds). Long text scrolls to the end within this time.</div>
                                    @error('display_duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="display_order" class="form-label">Display Order <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('display_order') is-invalid @enderror"
                                           id="display_order" name="display_order"
                                           value="{{ old('display_order', $nextDisplayOrder ?? 1) }}"
                                           min="1" max="9999" required>
                                    <div class="form-text">Lower numbers appear first (1, 2, 3…), same as posters</div>
                                    @error('display_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="font_size" class="form-label">Body Font Size (rem) <span class="text-danger">*</span></label>
                                    @php
                                        $bodyFontSizeValue = old('font_size', 1.5);
                                        $bodyFontSizeValue = is_numeric($bodyFontSizeValue) ? (float) $bodyFontSizeValue : 1.5;
                                    @endphp
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="range" class="form-range flex-grow-1" 
                                               id="font_size_range" min="0.75" max="10" step="0.05" value="{{ $bodyFontSizeValue }}">
                                        <input type="number" class="form-control" style="width: 90px;" 
                                               id="font_size" name="font_size" value="{{ $bodyFontSizeValue }}" 
                                               min="0.75" max="10" step="0.05" required>
                                        <span class="input-group-text" id="font-size-label">rem</span>
                                    </div>
                                    <small class="form-text d-block mt-2">
                                        <span id="font-size-info"></span>
                                        <small id="font-size-warning" style="display: none; color: #ff6b6b;">Large font size - limit text to 150 chars</small>
                                    </small>
                                    @error('font_size')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="scroll_speed" class="form-label">Scroll Speed <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-2">
                                        <input type="range" class="form-range flex-grow-1" 
                                               id="scroll_speed_range" min="1" max="10" value="{{ old('scroll_speed', 3) }}">
                                        <input type="number" class="form-control" style="width: 60px;" 
                                               id="scroll_speed" name="scroll_speed" value="{{ old('scroll_speed', 3) }}" 
                                               min="1" max="10" required>
                                    </div>
                                    <small id="scroll-speed-label" class="form-text d-block mt-2"></small>
                                    @error('scroll_speed')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="text_color" class="form-label">Text Color <span class="text-danger">*</span></label>
                                    <input type="color" class="form-control form-control-color @error('text_color') is-invalid @enderror" 
                                           id="text_color" name="text_color" value="{{ old('text_color', '#000000') }}" required>
                                    @error('text_color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="background_color" class="form-label">Background Color <span class="text-danger">*</span></label>
                                    <input type="color" class="form-control form-control-color @error('background_color') is-invalid @enderror" 
                                           id="background_color" name="background_color" value="{{ old('background_color', '#ffffff') }}" required>
                                    @error('background_color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                @php
                                    $mosqueTimezone = \App\Support\PrayerJamaatTime::appTimezone();
                                    $mosqueNow = \App\Support\PrayerJamaatTime::now();
                                @endphp
                                <div class="alert alert-info py-2 mb-3" role="status">
                                    Schedule times use the <strong>mosque clock</strong>
                                    ({{ $mosqueTimezone }}), same as the display screen —
                                    not your laptop timezone.
                                    Current mosque time:
                                    <strong>{{ $mosqueNow->format('D j M Y, H:i') }}</strong>.
                                    Leave dates empty to show with no time limit.
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Start date &amp; time</label>
                                    <div class="row g-2">
                                        <div class="col-7">
                                            <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                                   id="start_date" name="start_date"
                                                   value="{{ old('start_date') }}"
                                                   aria-label="Start date">
                                            @error('start_date')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-5">
                                            <input type="time" class="form-control @error('start_time') is-invalid @enderror"
                                                   id="start_time" name="start_time" step="60"
                                                   value="{{ old('start_time', '00:00') }}"
                                                   aria-label="Start time (hours and minutes)">
                                            @error('start_time')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-text">Optional. Leave date empty for no start limit. Time uses mosque hours:minutes.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">End date &amp; time</label>
                                    <div class="row g-2">
                                        <div class="col-7">
                                            <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                                   id="end_date" name="end_date"
                                                   value="{{ old('end_date') }}"
                                                   aria-label="End date">
                                            @error('end_date')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-5">
                                            <input type="time" class="form-control @error('end_time') is-invalid @enderror"
                                                   id="end_time" name="end_time" step="60"
                                                   value="{{ old('end_time', '23:59') }}"
                                                   aria-label="End time (hours and minutes)">
                                            @error('end_time')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-text">Optional. Leave date empty for no end limit. Announcement stays visible through the chosen end minute.</div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                        <div class="form-text">Only active announcements will be displayed</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="auto_repeat" name="auto_repeat"
                                               value="1" {{ old('auto_repeat') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="auto_repeat">
                                            Only on specific days
                                        </label>
                                        <div class="form-text">Turn on to show this announcement only on chosen weekdays (mosque timezone)</div>
                                    </div>
                                </div>

                                <div class="mb-3" id="repeat-days-section" style="display: none;">
                                    <label class="form-label">Show only on</label>
                                    <div class="row">
                                        @php
                                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                        @endphp
                                        @foreach($days as $day)
                                            <div class="col-6 col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                           id="repeat_days_{{ $day }}" name="repeat_days[]"
                                                           value="{{ $day }}" {{ in_array($day, old('repeat_days', []), true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="repeat_days_{{ $day }}">
                                                        {{ ucfirst($day) }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="form-text">Example: check Friday only for Jumah announcements</div>
                                    @error('repeat_days')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="bi bi-save"></i> Save Announcement
                            </button>
                        </div>
                    </form>
                        </div>

                        <!-- Preview Column (Right Side) -->
                        <div class="col-md-6">
                            <div class="card sticky-top" style="top: 20px;">
                                <div class="card-header bg-info text-white py-2">
                                    <h6 class="mb-0"><i class="bi bi-eye"></i> Quick preview</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="announcement-preview-frame">
                                        <div id="announcement-preview-box" style="
                                            background-color: #ffffff;
                                            border: 1px solid #adb5bd;
                                            border-radius: 6px;
                                            padding: 12px;
                                            min-height: 120px;
                                            max-height: 220px;
                                            overflow: auto;
                                            display: flex;
                                            flex-direction: column;
                                            justify-content: flex-start;
                                            text-align: left;
                                            font-family: Arial, sans-serif;
                                        ">
                                            <div id="preview-title" style="
                                                font-size: 0.72rem;
                                                font-weight: bold;
                                                margin-bottom: 8px;
                                                line-height: 1.25;
                                                width: 100%;
                                            ">Announcement Title</div>
                                            <div id="preview-text" style="
                                                font-size: 0.48rem;
                                                line-height: 1.35;
                                                word-wrap: break-word;
                                                white-space: pre-line;
                                                width: 100%;
                                                text-align: left;
                                            ">Announcement text will appear here...</div>
                                        </div>
                                    </div>
                                    <div class="form-text mt-2 mb-0">Scaled for this form — the TV uses the full rem sizes below.</div>

                                    <div class="border rounded bg-light p-2 mt-3 small mb-0">
                                        <div class="fw-semibold mb-1">Display info</div>
                                        <div>Body size: <span id="preview-font-size">1.5rem</span></div>
                                        <div>Characters: <span id="preview-char-count">0</span></div>
                                        <div>Est. lines: <span id="preview-lines">1</span></div>
                                        <div id="preview-fit-warning" class="text-danger mt-1" style="display: none;">
                                            <i class="bi bi-exclamation-triangle"></i> Text may need scrolling on the TV.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/announcement-validation.js') }}"></script>
<script>
// ==================== ENHANCED VALIDATION & PREVIEW ====================

// Elements
const contentTextarea = document.getElementById('content');
const fontSizeInput = document.getElementById('font_size');
const fontSizeRange = document.getElementById('font_size_range');
const scrollSpeedInput = document.getElementById('scroll_speed');
const scrollSpeedRange = document.getElementById('scroll_speed_range');
const charCountDisplay = document.getElementById('char-count');
const titleInput = document.getElementById('title');

// Preview elements
const previewBox = document.getElementById('announcement-preview-box');
const previewTitle = document.getElementById('preview-title');
const previewText = document.getElementById('preview-text');
const previewFontSize = document.getElementById('preview-font-size');
const previewCharCount = document.getElementById('preview-char-count');
const previewLines = document.getElementById('preview-lines');
const previewFitWarning = document.getElementById('preview-fit-warning');
const PREVIEW_SCALE = 0.32;

function previewRem(remValue, fallback) {
    const n = parseFloat(remValue);
    const base = Number.isFinite(n) && n > 0 ? n : fallback;
    return Math.max(0.7, base * PREVIEW_SCALE) + 'rem';
}

// Font size info
const fontSizeInfo = document.getElementById('font-size-info');
const fontSizeWarning = document.getElementById('font-size-warning');

// Scroll speed label
const scrollSpeedLabel = document.getElementById('scroll-speed-label');
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

/**
 * Update character counter and validation status
 */
function updateCharCounter() {
    const charCount = contentTextarea.value.length;
    const fontSize = parseFloat(fontSizeInput.value) || 1.5;
    
    // Update display
    charCountDisplay.textContent = charCount;
    
    // Update preview
    previewCharCount.textContent = charCount;
    
    // Check if text fits on TV
    updateTextFitWarning(charCount, fontSize);
    
    // Update preview
    updatePreview();
}

contentTextarea.addEventListener('input', updateCharCounter);

/**
 * Update font size display and preview
 */
function updateFontSize() {
    const fontSize = parseFloat(fontSizeInput.value) || 1.5;
    
    // Sync range and input
    fontSizeRange.value = fontSize;
    fontSizeInput.value = fontSize;
    
    // Update info text (rem scale)
    if (fontSize >= 6) {
        fontSizeInfo.textContent = '🔤 Extra Large';
    } else if (fontSize >= 3.75) {
        fontSizeInfo.textContent = '📢 Large';
    } else if (fontSize >= 2.5) {
        fontSizeInfo.textContent = '📝 Medium';
    } else {
        fontSizeInfo.textContent = '📄 Small';
    }
    
    // Update warning
    if (fontSize >= 3.75) {
        fontSizeWarning.style.display = 'inline';
    } else {
        fontSizeWarning.style.display = 'none';
    }
    
    previewFontSize.textContent = fontSize + 'rem';
    previewText.style.fontSize = previewRem(fontSize, 1.5);
    
    updateCharCounter();
}

fontSizeInput.addEventListener('input', updateFontSize);
fontSizeInput.addEventListener('change', updateFontSize);
fontSizeRange.addEventListener('input', function() {
    fontSizeInput.value = this.value;
    updateFontSize();
});

/**
 * Update title font size display and preview
 */
function updateTitleFontSize() {
    const titleFontSize = parseFloat(document.getElementById('title_font_size').value) || 2.25;
    
    // Sync range and input
    const titleFontSizeRange = document.getElementById('title_font_size_range');
    if (titleFontSizeRange) {
        titleFontSizeRange.value = titleFontSize;
    }
    document.getElementById('title_font_size').value = titleFontSize;
    
    previewTitle.style.fontSize = previewRem(titleFontSize, 2.25);
    updatePreview();
}

const titleFontSizeInput = document.getElementById('title_font_size');
const titleFontSizeRange = document.getElementById('title_font_size_range');
if (titleFontSizeInput) {
    titleFontSizeInput.addEventListener('input', updateTitleFontSize);
    titleFontSizeInput.addEventListener('change', updateTitleFontSize);
}
if (titleFontSizeRange) {
    titleFontSizeRange.addEventListener('input', function() {
        document.getElementById('title_font_size').value = this.value;
        updateTitleFontSize();
    });
}

/**
 * Update scroll speed display
 */
function updateScrollSpeed() {
    const speed = parseInt(scrollSpeedInput.value) || 1;
    
    // Sync range and input
    scrollSpeedRange.value = speed;
    scrollSpeedInput.value = speed;
    
    scrollSpeedLabel.textContent = speedDescriptions[speed] || 'Normal';
}

scrollSpeedInput.addEventListener('change', updateScrollSpeed);
scrollSpeedRange.addEventListener('input', function() {
    scrollSpeedInput.value = this.value;
    updateScrollSpeed();
});

/**
 * Check if text will fit on TV
 */
function updateTextFitWarning(charCount, fontSize) {
    const willFit = (fontSize >= 3.75 ? charCount <= 150 : charCount <= 300);
    
    if (!willFit && charCount > 0) {
        previewFitWarning.style.display = 'block';
    } else {
        previewFitWarning.style.display = 'none';
    }
    
    // Update estimated lines
    const charsPerLine = fontSize >= 3.75 ? 20 : 50;
    const estimatedLines = Math.ceil(charCount / charsPerLine);
    previewLines.textContent = Math.max(1, estimatedLines);
}

/**
 * Update preview in real-time
 */
function updatePreview() {
    if (titleInput) {
        const titleValue = (titleInput.value || '').trim();
        previewTitle.textContent = titleValue || '(No title)';
        previewTitle.style.opacity = titleValue ? '1' : '0.45';
        previewTitle.style.fontStyle = titleValue ? 'normal' : 'italic';
    }
    const body = contentTextarea.value || 'Announcement text will appear here...';
    previewText.innerHTML = String(body)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\r\n|\r|\n/g, '<br>');
    
    // Update background and text colors
    const bgColorInput = document.getElementById('background_color');
    const textColorInput = document.getElementById('text_color');
    
    if (bgColorInput) {
        previewBox.style.backgroundColor = bgColorInput.value;
    }
    if (textColorInput) {
        previewText.style.color = textColorInput.value;
        previewTitle.style.color = textColorInput.value;
    }
}

if (titleInput) titleInput.addEventListener('input', updatePreview);
contentTextarea.addEventListener('input', updatePreview);

document.getElementById('background_color').addEventListener('change', updatePreview);
document.getElementById('text_color').addEventListener('change', updatePreview);

/**
 * Form submission validation
 */
const form = contentTextarea.closest('form');
if (form) {
    form.addEventListener('submit', function(e) {
        const charCount = contentTextarea.value.length;
        
        if (charCount === 0) {
            e.preventDefault();
            alert('Please enter some content for the announcement.');
            contentTextarea.focus();
            return false;
        }

        return true;
    });
}

/**
 * Show/hide weekday restriction; clear day checkboxes when disabled so they are not submitted.
 */
document.getElementById('auto_repeat').addEventListener('change', function() {
    const repeatDaysSection = document.getElementById('repeat-days-section');
    if (this.checked) {
        repeatDaysSection.style.display = 'block';
    } else {
        repeatDaysSection.style.display = 'none';
        repeatDaysSection.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
            cb.checked = false;
        });
    }
});

/**
 * Initialize on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize repeat days section visibility
    const autoRepeatCheckbox = document.getElementById('auto_repeat');
    const repeatDaysSection = document.getElementById('repeat-days-section');
    if (autoRepeatCheckbox && autoRepeatCheckbox.checked) {
        repeatDaysSection.style.display = 'block';
    }
    
    // Initialize all displays
    updateCharCounter();
    updateFontSize();
    updateTitleFontSize();
    updateScrollSpeed();
    updatePreview();
});
</script>
@endsection
