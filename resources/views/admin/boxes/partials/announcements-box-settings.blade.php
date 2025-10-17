<!-- Announcements Box Specific Settings -->
<div class="mb-4">
    <h6>Announcements Box Settings</h6>
    <div class="row">
        <div class="col-md-6">
            <label for="title" class="form-label">Box Title</label>
            <input type="text" class="form-control" id="title" name="content_settings[title]"
                   value="{{ old('content_settings.title', $box->content_settings['title'] ?? 'Announcements') }}"
                   placeholder="e.g., Announcements">
        </div>
        <div class="col-md-6">
            <label for="rotation_duration" class="form-label">Rotation Duration (seconds)</label>
            <input type="number" class="form-control" id="rotation_duration" name="content_settings[rotation_duration]"
                   value="{{ old('content_settings.rotation_duration', $box->content_settings['rotation_duration'] ?? 15) }}"
                   min="5" max="120" step="5">
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <label for="title_font_size" class="form-label">Title Font Size</label>
            <input type="text" class="form-control" id="title_font_size" name="styling_settings[title_font_size]"
                   value="{{ old('styling_settings.title_font_size', $box->styling_settings['title_font_size'] ?? '18px') }}"
                   placeholder="e.g., 18px, 1.2rem">
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
            <label for="max_visible_announcements" class="form-label">Max Visible Announcements</label>
            <select class="form-select" id="max_visible_announcements" name="content_settings[max_visible_announcements]">
                <option value="1" {{ ($box->content_settings['max_visible_announcements'] ?? '') == '1' ? 'selected' : '' }}>1 Announcement</option>
                <option value="2" {{ ($box->content_settings['max_visible_announcements'] ?? '') == '2' ? 'selected' : '' }}>2 Announcements</option>
                <option value="3" {{ ($box->content_settings['max_visible_announcements'] ?? '') == '3' ? 'selected' : '' }}>3 Announcements</option>
                <option value="4" {{ ($box->content_settings['max_visible_announcements'] ?? '') == '4' ? 'selected' : '' }}>4 Announcements</option>
            </select>
        </div>
        <div class="col-md-6">
            <label for="character_limit" class="form-label">Character Limit per Announcement</label>
            <input type="number" class="form-control" id="character_limit" name="content_settings[character_limit]"
                   value="{{ old('content_settings.character_limit', $box->content_settings['character_limit'] ?? 200) }}"
                   min="50" max="500" step="10">
        </div>
    </div>
    
    <div class="mt-3">
        <h6>Display Options</h6>
        <div class="row">
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_character_count" name="content_settings[show_character_count]" value="1"
                           {{ ($box->content_settings['show_character_count'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_character_count">
                        Show Character Count
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="enable_scroll" name="content_settings[enable_scroll]" value="1"
                           {{ ($box->content_settings['enable_scroll'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="enable_scroll">
                        Enable Scroll for Overflow
                    </label>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <h6>Smart Display Logic</h6>
        <div class="alert alert-info">
            <small>
                <strong>Smart Display:</strong> The system automatically chooses between displaying one long announcement or two short announcements based on content length and the character limit setting.
            </small>
        </div>
    </div>
</div>
