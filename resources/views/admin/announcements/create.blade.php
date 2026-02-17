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
                                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title') }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="title_font_size" class="form-label">Title Font Size <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-2">
                                        <input type="range" class="form-range flex-grow-1" 
                                               id="title_font_size_range" min="20" max="60" value="{{ old('title_font_size', 36) }}">
                                        <input type="number" class="form-control" style="width: 80px;" 
                                               id="title_font_size" name="title_font_size" value="{{ old('title_font_size', 36) }}" 
                                               min="20" max="60" required>
                                        <span class="input-group-text" id="title-font-size-label">px</span>
                                    </div>
                                    <small class="form-text d-block mt-2">Recommended: 20 - 60 pixels</small>
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
                                    <div class="form-text">How long this announcement should be displayed (1-120 seconds)</div>
                                    @error('display_duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="font_size" class="form-label">Font Size <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-2">
                                        <input type="range" class="form-range flex-grow-1" 
                                               id="font_size_range" min="12" max="160" value="{{ old('font_size', 24) }}">
                                        <input type="number" class="form-control" style="width: 80px;" 
                                               id="font_size" name="font_size" value="{{ old('font_size', 24) }}" 
                                               min="12" max="160" required>
                                        <span class="input-group-text" id="font-size-label">px</span>
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

                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror" 
                                           id="start_date" name="start_date" value="{{ old('start_date') }}">
                                    <div class="form-text">Optional: When this announcement should start showing</div>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" name="end_date" value="{{ old('end_date') }}">
                                    <div class="form-text">Optional: When this announcement should stop showing</div>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                            Auto Repeat
                                        </label>
                                        <div class="form-text">Enable to repeat this announcement on specific days</div>
                                    </div>
                                </div>

                                <div class="mb-3" id="repeat-days-section" style="display: none;">
                                    <label class="form-label">Repeat Days</label>
                                    <div class="row">
                                        @php
                                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                        @endphp
                                        @foreach($days as $day)
                                            <div class="col-6 col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           id="repeat_days_{{ $day }}" name="repeat_days[]" 
                                                           value="{{ $day }}" {{ in_array($day, old('repeat_days', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="repeat_days_{{ $day }}">
                                                        {{ ucfirst($day) }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
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
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="bi bi-eye"></i> 85" TV Display Preview</h5>
                                </div>
                                <div class="card-body">
                                    <div id="announcement-preview-box" class="p-4" style="
                                        background-color: rgba(253, 247, 230, 0.9);
                                        border: 2px solid #000;
                                        border-radius: 8px;
                                        min-height: 300px;
                                        display: flex;
                                        flex-direction: column;
                                        align-items: center;
                                        justify-content: center;
                                        text-align: center;
                                        overflow: hidden;
                                        font-family: Arial, sans-serif;
                                    ">
                                        <div id="preview-title" style="
                                            font-size: 1.5rem;
                                            font-weight: bold;
                                            margin-bottom: 15px;
                                            width: 100%;
                                        ">Announcement Title</div>
                                        <div id="preview-text" style="
                                            font-size: 24px;
                                            word-wrap: break-word;
                                            width: 100%;
                                            overflow: hidden;
                                            display: -webkit-box;
                                            -webkit-line-clamp: 3;
                                            -webkit-box-orient: vertical;
                                        ">Announcement text will appear here...</div>
                                    </div>

                                    <div class="alert alert-info mt-4 mb-0">
                                        <strong>Display Info:</strong>
                                        <div class="small mt-2">
                                            <p><strong>Font Size:</strong> <span id="preview-font-size">24px</span></p>
                                            <p><strong>Character Count:</strong> <span id="preview-char-count">0</span></p>
                                            <p><strong>Estimated Lines:</strong> <span id="preview-lines">1</span></p>
                                            <p id="preview-fit-warning" style="display: none; color: #ff6b6b; margin-top: 10px;">
                                                <i class="bi bi-exclamation-triangle"></i> Text may be truncated on TV!
                                            </p>
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
    const fontSize = parseInt(fontSizeInput.value) || 12;
    
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
    const fontSize = parseInt(fontSizeInput.value) || 12;
    
    // Sync range and input
    fontSizeRange.value = fontSize;
    fontSizeInput.value = fontSize;
    
    // Update info text
    if (fontSize > 100) {
        fontSizeInfo.textContent = '🔤 Extra Large';
    } else if (fontSize > 60) {
        fontSizeInfo.textContent = '📢 Large';
    } else if (fontSize > 40) {
        fontSizeInfo.textContent = '📝 Medium';
    } else {
        fontSizeInfo.textContent = '📄 Small';
    }
    
    // Update warning
    if (fontSize > 60) {
        fontSizeWarning.style.display = 'inline';
    } else {
        fontSizeWarning.style.display = 'none';
    }
    
    previewFontSize.textContent = fontSize + 'px';
    previewText.style.fontSize = fontSize + 'px';
    
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
    const titleFontSize = parseInt(document.getElementById('title_font_size').value) || 36;
    
    // Sync range and input
    const titleFontSizeRange = document.getElementById('title_font_size_range');
    if (titleFontSizeRange) {
        titleFontSizeRange.value = titleFontSize;
    }
    document.getElementById('title_font_size').value = titleFontSize;
    
    previewTitle.style.fontSize = titleFontSize + 'px';
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
    const willFit = (fontSize > 60 ? charCount <= 150 : charCount <= 300);
    
    if (!willFit && charCount > 0) {
        previewFitWarning.style.display = 'block';
    } else {
        previewFitWarning.style.display = 'none';
    }
    
    // Update estimated lines
    const charsPerLine = fontSize > 60 ? 20 : 50;
    const estimatedLines = Math.ceil(charCount / charsPerLine);
    previewLines.textContent = Math.max(1, estimatedLines);
}

/**
 * Update preview in real-time
 */
function updatePreview() {
    if (titleInput) {
        previewTitle.textContent = titleInput.value || 'Announcement Title';
    }
    previewText.textContent = contentTextarea.value || 'Announcement text will appear here...';
    
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
 * Show/hide repeat days section
 */
document.getElementById('auto_repeat').addEventListener('change', function() {
    const repeatDaysSection = document.getElementById('repeat-days-section');
    if (this.checked) {
        repeatDaysSection.style.display = 'block';
    } else {
        repeatDaysSection.style.display = 'none';
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
    updateScrollSpeed();
    updatePreview();
});
</script>
@endsection
