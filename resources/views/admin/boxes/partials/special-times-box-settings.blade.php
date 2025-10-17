<!-- Special Times Box Specific Settings -->
<div class="mb-4">
    <h6>Special Times Settings</h6>
    <div class="row">
        <div class="col-md-6">
            <label for="header_background_color" class="form-label">Header Background Color</label>
            <input type="color" class="form-control form-control-color" 
                   id="header_background_color" name="styling_settings[header_background_color]"
                   value="{{ old('styling_settings.header_background_color', $box->styling_settings['header_background_color'] ?? '#0066cc') }}">
        </div>
        <div class="col-md-6">
            <label for="header_text_color" class="form-label">Header Text Color</label>
            <input type="color" class="form-control form-control-color" 
                   id="header_text_color" name="styling_settings[header_text_color]"
                   value="{{ old('styling_settings.header_text_color', $box->styling_settings['header_text_color'] ?? '#ffffff') }}">
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <label for="header_font_size" class="form-label">Header Font Size</label>
            <input type="text" class="form-control" id="header_font_size" name="styling_settings[header_font_size]"
                   value="{{ old('styling_settings.header_font_size', $box->styling_settings['header_font_size'] ?? '14px') }}"
                   placeholder="e.g., 14px, 0.9rem">
        </div>
        <div class="col-md-6">
            <label for="time_format" class="form-label">Time Format</label>
            <select class="form-select" id="time_format" name="content_settings[time_format]">
                <option value="h:i" {{ ($box->content_settings['time_format'] ?? '') == 'h:i' ? 'selected' : '' }}>05:38</option>
                <option value="H:i" {{ ($box->content_settings['time_format'] ?? '') == 'H:i' ? 'selected' : '' }}>05:38</option>
                <option value="h:i A" {{ ($box->content_settings['time_format'] ?? '') == 'h:i A' ? 'selected' : '' }}>05:38 AM</option>
            </select>
        </div>
    </div>
    
    <div class="mt-3">
        <h6>Show/Hide Columns</h6>
        <div class="row">
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_sehri_ends" name="content_settings[show_sehri_ends]" value="1"
                           {{ ($box->content_settings['show_sehri_ends'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_sehri_ends">
                        Show Sehri Ends
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_sun_rise" name="content_settings[show_sun_rise]" value="1"
                           {{ ($box->content_settings['show_sun_rise'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_sun_rise">
                        Show Sun Rise
                    </label>
                </div>
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_noon" name="content_settings[show_noon]" value="1"
                           {{ ($box->content_settings['show_noon'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_noon">
                        Show Noon
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_jumuah_1" name="content_settings[show_jumuah_1]" value="1"
                           {{ ($box->content_settings['show_jumuah_1'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_jumuah_1">
                        Show Jumu'ah 1
                    </label>
                </div>
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_jumuah_2" name="content_settings[show_jumuah_2]" value="1"
                           {{ ($box->content_settings['show_jumuah_2'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_jumuah_2">
                        Show Jumu'ah 2
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_eid_prayer_1" name="content_settings[show_eid_prayer_1]" value="1"
                           {{ ($box->content_settings['show_eid_prayer_1'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_eid_prayer_1">
                        Show Eid Prayer 1
                    </label>
                </div>
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_eid_prayer_2" name="content_settings[show_eid_prayer_2]" value="1"
                           {{ ($box->content_settings['show_eid_prayer_2'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_eid_prayer_2">
                        Show Eid Prayer 2
                    </label>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <h6>Column Headers</h6>
        <div class="row">
            <div class="col-md-6">
                <label for="table_header_1" class="form-label">Column 1 Header</label>
                <input type="text" class="form-control" id="table_header_1" name="content_settings[table_headers][]"
                       value="{{ old('content_settings.table_headers.0', ($box->content_settings['table_headers'][0] ?? 'Sehri Ends')) }}"
                       placeholder="e.g., Sehri Ends">
            </div>
            <div class="col-md-6">
                <label for="table_header_2" class="form-label">Column 2 Header</label>
                <input type="text" class="form-control" id="table_header_2" name="content_settings[table_headers][]"
                       value="{{ old('content_settings.table_headers.1', ($box->content_settings['table_headers'][1] ?? 'Sun Rise')) }}"
                       placeholder="e.g., Sun Rise">
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-md-6">
                <label for="table_header_3" class="form-label">Column 3 Header</label>
                <input type="text" class="form-control" id="table_header_3" name="content_settings[table_headers][]"
                       value="{{ old('content_settings.table_headers.2', ($box->content_settings['table_headers'][2] ?? 'Noon')) }}"
                       placeholder="e.g., Noon">
            </div>
            <div class="col-md-6">
                <label for="table_header_4" class="form-label">Column 4 Header</label>
                <input type="text" class="form-control" id="table_header_4" name="content_settings[table_headers][]"
                       value="{{ old('content_settings.table_headers.3', ($box->content_settings['table_headers'][3] ?? 'Jumu\'ah 1')) }}"
                       placeholder="e.g., Jumu'ah 1">
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-md-6">
                <label for="table_header_5" class="form-label">Column 5 Header</label>
                <input type="text" class="form-control" id="table_header_5" name="content_settings[table_headers][]"
                       value="{{ old('content_settings.table_headers.4', ($box->content_settings['table_headers'][4] ?? 'Jumu\'ah 2')) }}"
                       placeholder="e.g., Jumu'ah 2">
            </div>
            <div class="col-md-6">
                <label for="table_header_6" class="form-label">Column 6 Header</label>
                <input type="text" class="form-control" id="table_header_6" name="content_settings[table_headers][]"
                       value="{{ old('content_settings.table_headers.5', ($box->content_settings['table_headers'][5] ?? 'Eid Prayer 1')) }}"
                       placeholder="e.g., Eid Prayer 1">
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-md-6">
                <label for="table_header_7" class="form-label">Column 7 Header</label>
                <input type="text" class="form-control" id="table_header_7" name="content_settings[table_headers][]"
                       value="{{ old('content_settings.table_headers.6', ($box->content_settings['table_headers'][6] ?? 'Eid Prayer 2')) }}"
                       placeholder="e.g., Eid Prayer 2">
            </div>
        </div>
    </div>
</div>
