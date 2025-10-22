<!-- Hadeeth Box Specific Settings -->
<div class="mb-4">
    <h6>Hadeeth Box Settings</h6>
    <div class="row">
        <div class="col-md-6">
            <label for="title_font_size" class="form-label">Title Font Size</label>
            <input type="text" class="form-control" id="title_font_size" name="styling_settings[title_font_size]"
                   value="{{ old('styling_settings.title_font_size', $box->styling_settings['title_font_size'] ?? '1.6rem') }}"
                   placeholder="e.g., 1.6rem, 20px">
        </div>
        <div class="col-md-6">
            <label for="title_color" class="form-label">Title Color</label>
            <input type="color" class="form-control form-control-color" 
                   id="title_color" name="styling_settings[title_color]"
                   value="{{ old('styling_settings.title_color', $box->styling_settings['title_color'] ?? '#000000') }}">
        </div>
    </div>
</div>
