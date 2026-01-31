@extends('layouts.admin')

@section('title', 'Edit Announcement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Edit Announcement</h1>
                <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Announcements
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    @if(strlen($announcement->content) > 300 && $announcement->font_size > 60)
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>⚠️ Warning:</strong> This announcement has {{ strlen($announcement->content) }} characters with {{ $announcement->font_size }}px font. Some text may be hidden!
                            <ul class="mb-0 mt-2">
                                <li>For large fonts (>60px), keep text under 150 characters</li>
                                <li>For normal fonts (12-60px), text up to 300+ characters can fit</li>
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('admin.announcements.update', $announcement) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $announcement->title) }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('content') is-invalid @enderror" 
                                              id="content" name="content" rows="6" required>{{ old('content', $announcement->content) }}</textarea>
                                    <div class="form-text">
                                        Characters: <strong id="char-count">{{ strlen($announcement->content) }}</strong>/300 recommended
                                        <div id="char-warning" style="display: none; margin-top: 8px;" class="alert alert-warning py-2 px-3 mb-0">
                                            <i class="bi bi-exclamation-triangle me-1"></i><span id="warning-text"></span>
                                        </div>
                                    </div>
                                    @error('content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="display_duration" class="form-label">Display Duration (seconds) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('display_duration') is-invalid @enderror" 
                                           id="display_duration" name="display_duration" value="{{ old('display_duration', $announcement->display_duration) }}" 
                                           min="1" max="120" required>
                                    <div class="form-text">How long this announcement should be displayed (1-120 seconds)</div>
                                    @error('display_duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="font_size" class="form-label">Font Size <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('font_size') is-invalid @enderror" 
                                           id="font_size" name="font_size" value="{{ old('font_size', $announcement->font_size) }}" 
                                           min="12" max="160" required>
                                    <div class="form-text">Font size for the announcement text (12-160px / up to 10rem)</div>
                                    @error('font_size')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="scroll_speed" class="form-label">Scroll Speed <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('scroll_speed') is-invalid @enderror" 
                                           id="scroll_speed" name="scroll_speed" value="{{ old('scroll_speed', $announcement->scroll_speed) }}" 
                                           min="1" max="10" required>
                                    <div class="form-text">How fast the text scrolls (1=slow, 10=fast)</div>
                                    @error('scroll_speed')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="text_color" class="form-label">Text Color <span class="text-danger">*</span></label>
                                    <input type="color" class="form-control form-control-color @error('text_color') is-invalid @enderror" 
                                           id="text_color" name="text_color" value="{{ old('text_color', $announcement->text_color) }}" required>
                                    @error('text_color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="background_color" class="form-label">Background Color <span class="text-danger">*</span></label>
                                    <input type="color" class="form-control form-control-color @error('background_color') is-invalid @enderror" 
                                           id="background_color" name="background_color" value="{{ old('background_color', $announcement->background_color) }}" required>
                                    @error('background_color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror" 
                                           id="start_date" name="start_date" value="{{ old('start_date', $announcement->start_date ? $announcement->start_date->format('Y-m-d\TH:i') : '') }}">
                                    <div class="form-text">Optional: When this announcement should start showing</div>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" name="end_date" value="{{ old('end_date', $announcement->end_date ? $announcement->end_date->format('Y-m-d\TH:i') : '') }}">
                                    <div class="form-text">Optional: When this announcement should stop showing</div>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               value="1" {{ old('is_active', $announcement->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                        <div class="form-text">Only active announcements will be displayed</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="auto_repeat" name="auto_repeat" 
                                               value="1" {{ old('auto_repeat', $announcement->auto_repeat) ? 'checked' : '' }}>
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
                                            $selectedDays = old('repeat_days', $announcement->repeat_days ?? []);
                                        @endphp
                                        @foreach($days as $day)
                                            <div class="col-6 col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           id="repeat_days_{{ $day }}" name="repeat_days[]" 
                                                           value="{{ $day }}" {{ in_array($day, $selectedDays) ? 'checked' : '' }}>
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
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Announcement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Character counter and warnings
const contentTextarea = document.getElementById('content');
const fontSizeInput = document.getElementById('font_size');
const charCountDisplay = document.getElementById('char-count');
const charWarningDiv = document.getElementById('char-warning');
const warningText = document.getElementById('warning-text');

function updateCharacterWarning() {
    const contentLength = contentTextarea.value.length;
    const fontSize = parseInt(fontSizeInput.value) || 0;
    
    charCountDisplay.textContent = contentLength;
    
    // Show warning based on content length and font size
    if (fontSize > 60 && contentLength > 150) {
        charWarningDiv.style.display = 'block';
        warningText.textContent = `Large font size (${fontSize}px) with ${contentLength} characters may cause text to be hidden. Recommended: keep text under 150 characters for fonts above 60px.`;
    } else if (contentLength > 300) {
        charWarningDiv.style.display = 'block';
        warningText.textContent = `Your announcement is ${contentLength} characters long. For optimal display, keep it under 300 characters.`;
    } else {
        charWarningDiv.style.display = 'none';
    }
}

contentTextarea.addEventListener('input', updateCharacterWarning);
fontSizeInput.addEventListener('change', updateCharacterWarning);

document.getElementById('auto_repeat').addEventListener('change', function() {
    const repeatDaysSection = document.getElementById('repeat-days-section');
    if (this.checked) {
        repeatDaysSection.style.display = 'block';
    } else {
        repeatDaysSection.style.display = 'none';
    }
});

// Initialize the repeat days section visibility based on current state
document.addEventListener('DOMContentLoaded', function() {
    const autoRepeatCheckbox = document.getElementById('auto_repeat');
    const repeatDaysSection = document.getElementById('repeat-days-section');
    
    if (autoRepeatCheckbox.checked) {
        repeatDaysSection.style.display = 'block';
    }
});
</script>
@endsection
