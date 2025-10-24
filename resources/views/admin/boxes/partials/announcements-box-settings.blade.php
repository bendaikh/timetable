<!-- Announcements Box Specific Settings -->
<div class="mb-4">
    <h6>Announcements Box Settings</h6>
    
    <!-- Title Settings -->
    <div class="row mb-3">
        <div class="col-md-12">
            <label for="title" class="form-label">Announcements Title</label>
            <input type="text" class="form-control" id="title" name="content_settings[title]"
                   value="{{ old('content_settings.title', $box->content_settings['title'] ?? 'Announcements') }}"
                   placeholder="e.g., Announcements, Community News, etc.">
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <label for="title_font_size" class="form-label">Title Font Size</label>
            <input type="text" class="form-control" id="title_font_size" name="styling_settings[title_font_size]"
                   value="{{ old('styling_settings.title_font_size', $box->styling_settings['title_font_size'] ?? '1.4rem') }}"
                   placeholder="e.g., 1.4rem, 18px">
        </div>
        <div class="col-md-6">
            <label for="title_color" class="form-label">Title Color</label>
            <input type="color" class="form-control form-control-color" 
                   id="title_color" name="styling_settings[title_color]"
                   value="{{ old('styling_settings.title_color', $box->styling_settings['title_color'] ?? '#000000') }}">
        </div>
    </div>
</div>
