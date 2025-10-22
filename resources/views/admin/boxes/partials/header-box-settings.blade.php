<!-- Header Box Specific Settings -->
<div class="mb-4">
    <h6>Header Box Settings</h6>
    
    <div class="row">
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
</div>
