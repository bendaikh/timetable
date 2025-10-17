<!-- Welcome Box Specific Settings -->
<div class="mb-4">
    <h6>Welcome Box Settings</h6>
    <div class="row">
        <div class="col-md-12">
            <label for="welcome_text" class="form-label">Welcome Text</label>
            <textarea class="form-control" id="welcome_text" name="content_settings[welcome_text]" 
                      rows="3" placeholder="Enter welcome message...">{{ old('content_settings.welcome_text', $box->content_settings['welcome_text'] ?? 'Hello imran Welcome to timetable - Manage your prayer times, announcement') }}</textarea>
            <div class="form-text">Use {username} to display the current user's name dynamically.</div>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <label for="font_weight" class="form-label">Font Weight</label>
            <select class="form-select" id="font_weight" name="styling_settings[font_weight]">
                <option value="normal" {{ ($box->styling_settings['font_weight'] ?? '') == 'normal' ? 'selected' : '' }}>Normal</option>
                <option value="bold" {{ ($box->styling_settings['font_weight'] ?? '') == 'bold' ? 'selected' : '' }}>Bold</option>
                <option value="bolder" {{ ($box->styling_settings['font_weight'] ?? '') == 'bolder' ? 'selected' : '' }}>Bolder</option>
                <option value="lighter" {{ ($box->styling_settings['font_weight'] ?? '') == 'lighter' ? 'selected' : '' }}>Lighter</option>
            </select>
        </div>
        <div class="col-md-6">
            <label for="font_size" class="form-label">Font Size</label>
            <input type="text" class="form-control" id="font_size" name="styling_settings[font_size]"
                   value="{{ old('styling_settings.font_size', $box->styling_settings['font_size'] ?? '24px') }}"
                   placeholder="e.g., 24px, 1.5rem">
        </div>
    </div>
    
    <div class="mt-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="show_user_name" name="content_settings[show_user_name]" value="1"
                   {{ ($box->content_settings['show_user_name'] ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="show_user_name">
                Show User Name in Welcome Message
            </label>
        </div>
    </div>
    
    <div class="mt-3">
        <div class="alert alert-info">
            <small>
                <strong>Note:</strong> The welcome box appears at the bottom of the display with a distinctive green background and yellow text to match the reference design.
            </small>
        </div>
    </div>
</div>
