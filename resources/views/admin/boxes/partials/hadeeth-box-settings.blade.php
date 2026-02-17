<!-- Hadeeth Box Specific Settings -->
<div class="mb-4">
    <h6>Hadeeth Box Settings</h6>
    
    <!-- Title Settings -->
    <div class="row mb-3">
        <div class="col-md-12">
            <label for="title" class="form-label">Hadeeth Title</label>
            <input type="text" class="form-control" id="title" name="content_settings[title]"
                   value="{{ old('content_settings.title', $box->content_settings['title'] ?? 'Hadeeth Of The Day') }}"
                   placeholder="e.g., Hadeeth Of The Day, Daily Hadeeth, etc.">
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <label for="title_font_size" class="form-label">Title Font Size (rem)</label>
            <input type="text" class="form-control" id="title_font_size" name="styling_settings[title_font_size]"
                   value="{{ old('styling_settings.title_font_size', $box->styling_settings['title_font_size'] ?? '1.6rem') }}"
                   placeholder="e.g., 1.2, 1.6, 2"
                   pattern="^[0-9]+(\.[0-9]+)?$">
            <div class="form-text">Enter font size in rem units</div>
        </div>
        <div class="col-md-6">
            <label for="title_color" class="form-label">Title Color</label>
            <input type="color" class="form-control form-control-color" 
                   id="title_color" name="styling_settings[title_color]"
                   value="{{ old('styling_settings.title_color', $box->styling_settings['title_color'] ?? '#000000') }}">
        </div>
    </div>
</div>
