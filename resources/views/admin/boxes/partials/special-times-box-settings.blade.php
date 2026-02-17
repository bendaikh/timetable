<!-- Special Times Box Specific Settings -->
<div class="mb-4">
    <h6>Special Times Settings</h6>
    
    <!-- Title Settings -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="sehri_ends_title" class="form-label">Sehri Ends Title</label>
            <input type="text" class="form-control" id="sehri_ends_title" name="content_settings[sehri_ends_title]"
                   value="{{ old('content_settings.sehri_ends_title', $box->content_settings['sehri_ends_title'] ?? 'Sehri Ends') }}"
                   placeholder="e.g., Sehri Ends, Suhoor Ends, etc.">
        </div>
        <div class="col-md-6">
            <label for="sun_rise_title" class="form-label">Sun Rise Title</label>
            <input type="text" class="form-control" id="sun_rise_title" name="content_settings[sun_rise_title]"
                   value="{{ old('content_settings.sun_rise_title', $box->content_settings['sun_rise_title'] ?? 'Sun Rise') }}"
                   placeholder="e.g., Sun Rise, Sunrise, etc.">
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="noon_title" class="form-label">Noon Title</label>
            <input type="text" class="form-control" id="noon_title" name="content_settings[noon_title]"
                   value="{{ old('content_settings.noon_title', $box->content_settings['noon_title'] ?? 'Noon') }}"
                   placeholder="e.g., Noon, Midday, etc.">
        </div>
        <div class="col-md-6">
            <label for="jumah_1_title" class="form-label">Jumu'ah 1 Title</label>
            <input type="text" class="form-control" id="jumah_1_title" name="content_settings[jumah_1_title]"
                   value="{{ old('content_settings.jumah_1_title', $box->content_settings['jumah_1_title'] ?? 'Jumu\'ah 1') }}"
                   placeholder="e.g., Jumu'ah 1, Friday Prayer 1, etc.">
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="jumah_2_title" class="form-label">Jumu'ah 2 Title</label>
            <input type="text" class="form-control" id="jumah_2_title" name="content_settings[jumah_2_title]"
                   value="{{ old('content_settings.jumah_2_title', $box->content_settings['jumah_2_title'] ?? 'Jumu\'ah 2') }}"
                   placeholder="e.g., Jumu'ah 2, Friday Prayer 2, etc.">
        </div>
        <div class="col-md-6">
            <label for="eid_prayer_1_title" class="form-label">Eid Prayer 1 Title</label>
            <input type="text" class="form-control" id="eid_prayer_1_title" name="content_settings[eid_prayer_1_title]"
                   value="{{ old('content_settings.eid_prayer_1_title', $box->content_settings['eid_prayer_1_title'] ?? 'Eid Prayer 1') }}"
                   placeholder="e.g., Eid Prayer 1, Eid al-Fitr Prayer, etc.">
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="eid_prayer_2_title" class="form-label">Eid Prayer 2 Title</label>
            <input type="text" class="form-control" id="eid_prayer_2_title" name="content_settings[eid_prayer_2_title]"
                   value="{{ old('content_settings.eid_prayer_2_title', $box->content_settings['eid_prayer_2_title'] ?? 'Eid Prayer 2') }}"
                   placeholder="e.g., Eid Prayer 2, Eid al-Adha Prayer, etc.">
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <label for="header_text_color" class="form-label">Header Text Color</label>
            <input type="color" class="form-control form-control-color" 
                   id="header_text_color" name="styling_settings[header_text_color]"
                   value="{{ old('styling_settings.header_text_color', $box->styling_settings['header_text_color'] ?? '#000000') }}">
        </div>
        <div class="col-md-6">
            <label for="header_font_size" class="form-label">Header Font Size (rem)</label>
            <input type="text" class="form-control" id="header_font_size" name="styling_settings[header_font_size]"
                   value="{{ old('styling_settings.header_font_size', $box->styling_settings['header_font_size'] ?? '1rem') }}"
                   placeholder="e.g., 0.8, 1, 1.2"
                   pattern="^[0-9]+(\.[0-9]+)?$">
            <div class="form-text">Enter font size in rem units. Example: 1rem = 16px</div>
        </div>
    </div>
</div>
