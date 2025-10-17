<!-- Hadeeth Box Specific Settings -->
<div class="mb-4">
    <h6>Hadeeth Box Settings</h6>
    <div class="row">
        <div class="col-md-6">
            <label for="title" class="form-label">Box Title</label>
            <input type="text" class="form-control" id="title" name="content_settings[title]"
                   value="{{ old('content_settings.title', $box->content_settings['title'] ?? 'Hadeeth Of The Day') }}"
                   placeholder="e.g., Hadeeth Of The Day">
        </div>
        <div class="col-md-6">
            <label for="rotation_duration" class="form-label">Rotation Duration (seconds)</label>
            <input type="number" class="form-control" id="rotation_duration" name="content_settings[rotation_duration]"
                   value="{{ old('content_settings.rotation_duration', $box->content_settings['rotation_duration'] ?? 30) }}"
                   min="10" max="300" step="5">
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <label for="title_font_size" class="form-label">Title Font Size</label>
            <input type="text" class="form-control" id="title_font_size" name="styling_settings[title_font_size]"
                   value="{{ old('styling_settings.title_font_size', $box->styling_settings['title_font_size'] ?? '20px') }}"
                   placeholder="e.g., 20px, 1.3rem">
        </div>
        <div class="col-md-6">
            <label for="title_color" class="form-label">Title Color</label>
            <input type="color" class="form-control form-control-color" 
                   id="title_color" name="styling_settings[title_color]"
                   value="{{ old('styling_settings.title_color', $box->styling_settings['title_color'] ?? '#000000') }}">
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <label for="arabic_font_family" class="form-label">Arabic Font Family</label>
            <select class="form-select" id="arabic_font_family" name="styling_settings[arabic_font_family]">
                <option value="Amiri, serif" {{ ($box->styling_settings['arabic_font_family'] ?? '') == 'Amiri, serif' ? 'selected' : '' }}>Amiri (Recommended)</option>
                <option value="Scheherazade, serif" {{ ($box->styling_settings['arabic_font_family'] ?? '') == 'Scheherazade, serif' ? 'selected' : '' }}>Scheherazade</option>
                <option value="Arial, sans-serif" {{ ($box->styling_settings['arabic_font_family'] ?? '') == 'Arial, sans-serif' ? 'selected' : '' }}>Arial</option>
                <option value="Times New Roman, serif" {{ ($box->styling_settings['arabic_font_family'] ?? '') == 'Times New Roman, serif' ? 'selected' : '' }}>Times New Roman</option>
            </select>
        </div>
        <div class="col-md-6">
            <label for="english_font_family" class="form-label">English Font Family</label>
            <select class="form-select" id="english_font_family" name="styling_settings[english_font_family]">
                <option value="Arial, sans-serif" {{ ($box->styling_settings['english_font_family'] ?? '') == 'Arial, sans-serif' ? 'selected' : '' }}>Arial</option>
                <option value="Times New Roman, serif" {{ ($box->styling_settings['english_font_family'] ?? '') == 'Times New Roman, serif' ? 'selected' : '' }}>Times New Roman</option>
                <option value="Georgia, serif" {{ ($box->styling_settings['english_font_family'] ?? '') == 'Georgia, serif' ? 'selected' : '' }}>Georgia</option>
                <option value="Verdana, sans-serif" {{ ($box->styling_settings['english_font_family'] ?? '') == 'Verdana, sans-serif' ? 'selected' : '' }}>Verdana</option>
            </select>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <label for="accent_color" class="form-label">Accent Color (Background Element)</label>
            <input type="color" class="form-control form-control-color" 
                   id="accent_color" name="styling_settings[accent_color]"
                   value="{{ old('styling_settings.accent_color', $box->styling_settings['accent_color'] ?? '#90EE90') }}">
        </div>
    </div>
    
    <div class="mt-3">
        <h6>Display Options</h6>
        <div class="row">
            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_arabic_text" name="content_settings[show_arabic_text]" value="1"
                           {{ ($box->content_settings['show_arabic_text'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_arabic_text">
                        Show Arabic Text
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_english_translation" name="content_settings[show_english_translation]" value="1"
                           {{ ($box->content_settings['show_english_translation'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_english_translation">
                        Show English Translation
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_reference" name="content_settings[show_reference]" value="1"
                           {{ ($box->content_settings['show_reference'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_reference">
                        Show Reference
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
