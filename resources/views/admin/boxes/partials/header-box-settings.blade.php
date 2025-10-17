<!-- Header Box Specific Settings -->
<div class="mb-4">
    <h6>Header Box Settings</h6>
    <div class="row">
        <div class="col-md-6">
            <label for="time_format" class="form-label">Time Format</label>
            <select class="form-select" id="time_format" name="content_settings[time_format]">
                <option value="h:i:s A" {{ ($box->content_settings['time_format'] ?? '') == 'h:i:s A' ? 'selected' : '' }}>12:34:56 PM</option>
                <option value="H:i:s" {{ ($box->content_settings['time_format'] ?? '') == 'H:i:s' ? 'selected' : '' }}>14:34:56</option>
                <option value="h:i A" {{ ($box->content_settings['time_format'] ?? '') == 'h:i A' ? 'selected' : '' }}>12:34 PM</option>
                <option value="H:i" {{ ($box->content_settings['time_format'] ?? '') == 'H:i' ? 'selected' : '' }}>14:34</option>
            </select>
        </div>
        <div class="col-md-6">
            <label for="timezone" class="form-label">Timezone</label>
            <select class="form-select" id="timezone" name="content_settings[timezone]">
                <option value="Europe/London" {{ ($box->content_settings['timezone'] ?? '') == 'Europe/London' ? 'selected' : '' }}>London (GMT)</option>
                <option value="Europe/Dublin" {{ ($box->content_settings['timezone'] ?? '') == 'Europe/Dublin' ? 'selected' : '' }}>Dublin (GMT)</option>
                <option value="Europe/Paris" {{ ($box->content_settings['timezone'] ?? '') == 'Europe/Paris' ? 'selected' : '' }}>Paris (CET)</option>
                <option value="Asia/Dubai" {{ ($box->content_settings['timezone'] ?? '') == 'Asia/Dubai' ? 'selected' : '' }}>Dubai (GST)</option>
                <option value="Asia/Riyadh" {{ ($box->content_settings['timezone'] ?? '') == 'Asia/Riyadh' ? 'selected' : '' }}>Riyadh (AST)</option>
            </select>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <label for="english_date_format" class="form-label">English Date Format</label>
            <select class="form-select" id="english_date_format" name="content_settings[english_date_format]">
                <option value="D j M Y" {{ ($box->content_settings['english_date_format'] ?? '') == 'D j M Y' ? 'selected' : '' }}>Wed 15 Oct 2025</option>
                <option value="l jS F Y" {{ ($box->content_settings['english_date_format'] ?? '') == 'l jS F Y' ? 'selected' : '' }}>Wednesday 15th October 2025</option>
                <option value="j/m/Y" {{ ($box->content_settings['english_date_format'] ?? '') == 'j/m/Y' ? 'selected' : '' }}>15/10/2025</option>
                <option value="Y-m-d" {{ ($box->content_settings['english_date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' }}>2025-10-15</option>
            </select>
        </div>
        <div class="col-md-6">
            <label for="islamic_date_format" class="form-label">Islamic Date Format</label>
            <select class="form-select" id="islamic_date_format" name="content_settings[islamic_date_format]">
                <option value="d F Y" {{ ($box->content_settings['islamic_date_format'] ?? '') == 'd F Y' ? 'selected' : '' }}>18 Safar 1447</option>
                <option value="j F Y" {{ ($box->content_settings['islamic_date_format'] ?? '') == 'j F Y' ? 'selected' : '' }}>18th Safar 1447</option>
                <option value="d/m/Y" {{ ($box->content_settings['islamic_date_format'] ?? '') == 'd/m/Y' ? 'selected' : '' }}>18/02/1447</option>
            </select>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <label for="time_font_size" class="form-label">Time Font Size</label>
            <input type="text" class="form-control" id="time_font_size" name="styling_settings[time_font_size]"
                   value="{{ old('styling_settings.time_font_size', $box->styling_settings['time_font_size'] ?? '48px') }}"
                   placeholder="e.g., 48px, 3rem">
        </div>
        <div class="col-md-6">
            <label for="date_font_size" class="form-label">Date Font Size</label>
            <input type="text" class="form-control" id="date_font_size" name="styling_settings[date_font_size]"
                   value="{{ old('styling_settings.date_font_size', $box->styling_settings['date_font_size'] ?? '18px') }}"
                   placeholder="e.g., 18px, 1.2rem">
        </div>
    </div>
    
    <div class="mt-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="show_fullscreen_button" name="content_settings[show_fullscreen_button]" value="1"
                   {{ ($box->content_settings['show_fullscreen_button'] ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="show_fullscreen_button">
                Show Fullscreen Button
            </label>
        </div>
    </div>
</div>
