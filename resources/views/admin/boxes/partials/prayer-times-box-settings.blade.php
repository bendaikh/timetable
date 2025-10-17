<!-- Prayer Times Box Specific Settings -->
<div class="mb-4">
    <h6>Prayer Times Table Settings</h6>
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
                   value="{{ old('styling_settings.header_font_size', $box->styling_settings['header_font_size'] ?? '16px') }}"
                   placeholder="e.g., 16px, 1.1rem">
        </div>
        <div class="col-md-6">
            <label for="time_format" class="form-label">Time Format</label>
            <select class="form-select" id="time_format" name="content_settings[time_format]">
                <option value="h:i" {{ ($box->content_settings['time_format'] ?? '') == 'h:i' ? 'selected' : '' }}>12:34 PM</option>
                <option value="H:i" {{ ($box->content_settings['time_format'] ?? '') == 'H:i' ? 'selected' : '' }}>14:34</option>
                <option value="h:i A" {{ ($box->content_settings['time_format'] ?? '') == 'h:i A' ? 'selected' : '' }}>12:34 PM</option>
            </select>
        </div>
    </div>
    
    <div class="mt-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="show_jamaat_times" name="content_settings[show_jamaat_times]" value="1"
                   {{ ($box->content_settings['show_jamaat_times'] ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="show_jamaat_times">
                Show Jamaat Times
            </label>
        </div>
    </div>
    
    <div class="mt-3">
        <h6>Table Headers</h6>
        <div class="row">
            <div class="col-md-4">
                <label for="table_header_1" class="form-label">Column 1 Header</label>
                <input type="text" class="form-control" id="table_header_1" name="content_settings[table_headers][]"
                       value="{{ old('content_settings.table_headers.0', ($box->content_settings['table_headers'][0] ?? '')) }}"
                       placeholder="">
            </div>
            <div class="col-md-4">
                <label for="table_header_2" class="form-label">Column 2 Header</label>
                <input type="text" class="form-control" id="table_header_2" name="content_settings[table_headers][]"
                       value="{{ old('content_settings.table_headers.1', ($box->content_settings['table_headers'][1] ?? 'Beginning')) }}"
                       placeholder="e.g., Beginning">
            </div>
            <div class="col-md-4">
                <label for="table_header_3" class="form-label">Column 3 Header</label>
                <input type="text" class="form-control" id="table_header_3" name="content_settings[table_headers][]"
                       value="{{ old('content_settings.table_headers.2', ($box->content_settings['table_headers'][2] ?? 'Jamaat Time')) }}"
                       placeholder="e.g., Jamaat Time">
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <h6>Column Widths</h6>
        <div class="row">
            <div class="col-md-4">
                <label for="column_width_1" class="form-label">Prayer Name Width</label>
                <select class="form-select" id="column_width_1" name="layout_settings[column_widths][]">
                    @php $col1 = $box->layout_settings['column_widths'][0] ?? '45%'; @endphp
                    <option value="30%" {{ $col1 == '30%' ? 'selected' : '' }}>30%</option>
                    <option value="35%" {{ $col1 == '35%' ? 'selected' : '' }}>35%</option>
                    <option value="40%" {{ $col1 == '40%' ? 'selected' : '' }}>40%</option>
                    <option value="45%" {{ $col1 == '45%' ? 'selected' : '' }}>45%</option>
                    <option value="50%" {{ $col1 == '50%' ? 'selected' : '' }}>50%</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="column_width_2" class="form-label">Beginning Time Width</label>
                <select class="form-select" id="column_width_2" name="layout_settings[column_widths][]">
                    @php $col2 = $box->layout_settings['column_widths'][1] ?? '25%'; @endphp
                    <option value="25%" {{ $col2 == '25%' ? 'selected' : '' }}>25%</option>
                    <option value="27.5%" {{ $col2 == '27.5%' ? 'selected' : '' }}>27.5%</option>
                    <option value="30%" {{ $col2 == '30%' ? 'selected' : '' }}>30%</option>
                    <option value="35%" {{ $col2 == '35%' ? 'selected' : '' }}>35%</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="column_width_3" class="form-label">Jamaat Time Width</label>
                <select class="form-select" id="column_width_3" name="layout_settings[column_widths][]">
                    @php $col3 = $box->layout_settings['column_widths'][2] ?? '25%'; @endphp
                    <option value="25%" {{ $col3 == '25%' ? 'selected' : '' }}>25%</option>
                    <option value="27.5%" {{ $col3 == '27.5%' ? 'selected' : '' }}>27.5%</option>
                    <option value="30%" {{ $col3 == '30%' ? 'selected' : '' }}>30%</option>
                    <option value="35%" {{ $col3 == '35%' ? 'selected' : '' }}>35%</option>
                </select>
            </div>
        </div>
    </div>
</div>
