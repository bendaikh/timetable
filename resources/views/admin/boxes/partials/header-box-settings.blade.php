<!-- Header Box Specific Settings -->
<div class="mb-4">
    <h6>Header Box Settings</h6>
    
    <div class="row">
        <div class="col-md-6">
            <label for="time_font_size" class="form-label">Time Font Size (rem)</label>
            <input type="text" class="form-control" id="time_font_size" name="styling_settings[time_font_size]"
                   value="{{ old('styling_settings.time_font_size', $box->styling_settings['time_font_size'] ?? '3rem') }}"
                   placeholder="e.g., 3, 4.5, 6 or 6rem"
                   inputmode="decimal"
                   pattern="^[0-9]+(\.[0-9]+)?(rem)?$">
            <div class="form-text">Enter a number or a value ending in <code>rem</code></div>
        </div>
        <div class="col-md-6">
            <label for="date_font_size" class="form-label">Date Font Size (rem)</label>
            <input type="text" class="form-control" id="date_font_size" name="styling_settings[date_font_size]"
                   value="{{ old('styling_settings.date_font_size', $box->styling_settings['date_font_size'] ?? '2.75rem') }}"
                   placeholder="e.g., 1, 1.2, 1.5 or 1.5rem"
                   inputmode="decimal"
                   pattern="^[0-9]+(\.[0-9]+)?(rem)?$">
            <div class="form-text">Enter a number or a value ending in <code>rem</code></div>
        </div>
    </div>
</div>
