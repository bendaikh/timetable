<!-- Special Times Box Specific Settings -->
<div class="mb-4">
    <h6>Special Times Settings</h6>
    <div class="row">
        <div class="col-md-6">
            <label for="header_text_color" class="form-label">Header Text Color</label>
            <input type="color" class="form-control form-control-color" 
                   id="header_text_color" name="styling_settings[header_text_color]"
                   value="{{ old('styling_settings.header_text_color', $box->styling_settings['header_text_color'] ?? '#000000') }}">
        </div>
        <div class="col-md-6">
            <label for="header_font_size" class="form-label">Header Font Size</label>
            <input type="text" class="form-control" id="header_font_size" name="styling_settings[header_font_size]"
                   value="{{ old('styling_settings.header_font_size', $box->styling_settings['header_font_size'] ?? '1rem') }}"
                   placeholder="e.g., 1rem, 16px">
        </div>
    </div>
</div>
