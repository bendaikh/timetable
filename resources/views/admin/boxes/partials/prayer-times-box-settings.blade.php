<!-- Prayer Times Box Specific Settings -->
<div class="mb-4">
    <h6>Prayer Times Table Settings</h6>
    
    <!-- Title Settings -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="beginning_title" class="form-label">Beginning Column Title</label>
            <input type="text" class="form-control" id="beginning_title" name="content_settings[beginning_title]"
                   value="{{ old('content_settings.beginning_title', $box->content_settings['beginning_title'] ?? 'Beginning') }}"
                   placeholder="e.g., Beginning, Start Time, etc.">
        </div>
        <div class="col-md-6">
            <label for="jamaat_time_title" class="form-label">Jamaat Time Column Title</label>
            <input type="text" class="form-control" id="jamaat_time_title" name="content_settings[jamaat_time_title]"
                   value="{{ old('content_settings.jamaat_time_title', $box->content_settings['jamaat_time_title'] ?? 'Jamaat Time') }}"
                   placeholder="e.g., Jamaat Time, Congregation Time, etc.">
        </div>
    </div>
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
    </div>
    
    <div class="mt-4">
        <h6>Next Prayer Countdown Settings</h6>
        <div class="row">
            <div class="col-md-6">
                <label for="next_prayer_font_size" class="form-label">Next Prayer Text Font Size</label>
                <input type="text" class="form-control" id="next_prayer_font_size" name="styling_settings[next_prayer_font_size]"
                       value="{{ old('styling_settings.next_prayer_font_size', $box->styling_settings['next_prayer_font_size'] ?? '1.4rem') }}"
                       placeholder="e.g., 1.4rem, 16px">
            </div>
            <div class="col-md-6">
                <label for="next_prayer_text_color" class="form-label">Next Prayer Text Color</label>
                <input type="color" class="form-control form-control-color" 
                       id="next_prayer_text_color" name="styling_settings[next_prayer_text_color]"
                       value="{{ old('styling_settings.next_prayer_text_color', $box->styling_settings['next_prayer_text_color'] ?? '#000000') }}">
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <label for="next_prayer_countdown_font_size" class="form-label">Countdown Timer Font Size</label>
                <input type="text" class="form-control" id="next_prayer_countdown_font_size" name="styling_settings[next_prayer_countdown_font_size]"
                       value="{{ old('styling_settings.next_prayer_countdown_font_size', $box->styling_settings['next_prayer_countdown_font_size'] ?? '1.4rem') }}"
                       placeholder="e.g., 1.4rem, 16px">
            </div>
            <div class="col-md-6">
                <label for="next_prayer_countdown_color" class="form-label">Countdown Timer Color</label>
                <input type="color" class="form-control form-control-color" 
                       id="next_prayer_countdown_color" name="styling_settings[next_prayer_countdown_color]"
                       value="{{ old('styling_settings.next_prayer_countdown_color', $box->styling_settings['next_prayer_countdown_color'] ?? '#000000') }}">
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <label for="next_prayer_name_font_size" class="form-label">Prayer Name Font Size</label>
                <input type="text" class="form-control" id="next_prayer_name_font_size" name="styling_settings[next_prayer_name_font_size]"
                       value="{{ old('styling_settings.next_prayer_name_font_size', $box->styling_settings['next_prayer_name_font_size'] ?? '0.9rem') }}"
                       placeholder="e.g., 0.9rem, 12px">
            </div>
            <div class="col-md-6">
                <label for="next_prayer_name_color" class="form-label">Prayer Name Color</label>
                <input type="color" class="form-control form-control-color" 
                       id="next_prayer_name_color" name="styling_settings[next_prayer_name_color]"
                       value="{{ old('styling_settings.next_prayer_name_color', $box->styling_settings['next_prayer_name_color'] ?? '#666666') }}">
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <label for="next_prayer_position" class="form-label">Next Prayer Position</label>
                <select class="form-select" id="next_prayer_position" name="layout_settings[next_prayer_position]">
                    @php $position = $box->layout_settings['next_prayer_position'] ?? 'below_table'; @endphp
                    <option value="below_table" {{ $position == 'below_table' ? 'selected' : '' }}>Below Prayer Table</option>
                    <option value="above_table" {{ $position == 'above_table' ? 'selected' : '' }}>Above Prayer Table</option>
                    <option value="hidden" {{ $position == 'hidden' ? 'selected' : '' }}>Hidden</option>
                </select>
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
