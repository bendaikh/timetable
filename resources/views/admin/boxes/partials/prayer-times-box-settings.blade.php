<!-- Prayer Times Box Specific Settings -->
<div class="mb-4">
    <!-- Section 1: Table Header Settings -->
    <div class="card mb-3 border-primary">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="bi bi-table"></i> Prayer Times Table Settings</h6>
        </div>
        <div class="card-body">
            <!-- Column Titles -->
            <div class="mb-3">
                <h6 class="text-muted">Column Titles</h6>
                <div class="row">
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
            </div>
            
            <!-- Header Styling -->
            <hr>
            <div class="mb-0">
                <h6 class="text-muted">Header Row Styling</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label for="header_background_color" class="form-label">Background Color</label>
                        <input type="color" class="form-control form-control-color" 
                               id="header_background_color" name="styling_settings[header_background_color]"
                               value="{{ old('styling_settings.header_background_color', $box->styling_settings['header_background_color'] ?? '#0066cc') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="header_text_color" class="form-label">Text Color</label>
                        <input type="color" class="form-control form-control-color" 
                               id="header_text_color" name="styling_settings[header_text_color]"
                               value="{{ old('styling_settings.header_text_color', $box->styling_settings['header_text_color'] ?? '#ffffff') }}">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <label for="header_font_size" class="form-label">Font Size (rem)</label>
                        <input type="text" class="form-control" id="header_font_size" name="styling_settings[header_font_size]"
                               value="{{ old('styling_settings.header_font_size', $box->styling_settings['header_font_size'] ?? '1.5') }}"
                               placeholder="e.g., 1.2, 1.5, 1.8"
                               pattern="^[0-9]+(\.[0-9]+)?$">
                        <div class="form-text">Recommended: 1.2 - 1.8</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 1.5: Prayer Names and Font Size -->
    <div class="card mb-3 border-danger">
        <div class="card-header bg-danger text-white">
            <h6 class="mb-0"><i class="bi bi-type"></i> Prayer Names Font Size</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">Set the font size for all prayer names (Fajr, Zohar, Asr, Maghrib, Isha)</p>
            
            <div class="row">
                <div class="col-md-6">
                    <label for="prayer_names_font_size" class="form-label">Prayer Names Font Size (rem)</label>
                    <input type="text" class="form-control" id="prayer_names_font_size" name="styling_settings[prayer_names_font_size]"
                           value="{{ old('styling_settings.prayer_names_font_size', $box->styling_settings['prayer_names_font_size'] ?? '4') }}"
                           placeholder="e.g., 3, 3.5, 4, 4.5"
                           pattern="^[0-9]+(\.[0-9]+)?$">
                    <div class="form-text">Recommended: 3.5 - 4.5. Controls the size of prayer names (Fajr, Zohar, Asr, Maghrib, Isha)</div>
                </div>
            </div>
        </div>
    </div>
    
    
    <!-- Section 2: Next Prayer Countdown Settings -->
    <div class="card mb-3 border-success">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="bi bi-hourglass-split"></i> Next Prayer Countdown Settings</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <h6 class="text-muted"><i class="bi bi-type"></i> "Next prayer in:" Text</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label for="next_prayer_font_size" class="form-label">Font Size</label>
                        <input type="text" class="form-control" id="next_prayer_font_size" name="styling_settings[next_prayer_font_size]"
                               value="{{ old('styling_settings.next_prayer_font_size', $box->styling_settings['next_prayer_font_size'] ?? '1.4') }}"
                               placeholder="e.g., 1, 1.2, 1.4"
                               pattern="^[0-9]+(\.[0-9]+)?$">
                        <div class="form-text">Recommended: 0.9 - 1.6</div>
                    </div>
                    <div class="col-md-6">
                        <label for="next_prayer_text_color" class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" 
                               id="next_prayer_text_color" name="styling_settings[next_prayer_text_color]"
                               value="{{ old('styling_settings.next_prayer_text_color', $box->styling_settings['next_prayer_text_color'] ?? '#000000') }}">
                    </div>
                </div>
            </div>
            
            <hr>
            <div class="mb-3">
                <h6 class="text-muted"><i class="bi bi-hourglass"></i> Countdown Timer</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label for="next_prayer_countdown_font_size" class="form-label">Font Size</label>
                        <input type="text" class="form-control" id="next_prayer_countdown_font_size" name="styling_settings[next_prayer_countdown_font_size]"
                               value="{{ old('styling_settings.next_prayer_countdown_font_size', $box->styling_settings['next_prayer_countdown_font_size'] ?? '1.4') }}"
                               placeholder="e.g., 1, 1.2, 1.5"
                               pattern="^[0-9]+(\.[0-9]+)?$">
                        <div class="form-text">Recommended: 1.0 - 2.0</div>
                    </div>
                    <div class="col-md-6">
                        <label for="next_prayer_countdown_color" class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" 
                               id="next_prayer_countdown_color" name="styling_settings[next_prayer_countdown_color]"
                               value="{{ old('styling_settings.next_prayer_countdown_color', $box->styling_settings['next_prayer_countdown_color'] ?? '#000000') }}">
                    </div>
                </div>
            </div>
            
            <hr>
            <div class="mb-3">
                <h6 class="text-muted"><i class="bi bi-star"></i> Prayer Name (e.g., "Fajr")</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label for="next_prayer_name_font_size" class="form-label">Font Size</label>
                        <input type="text" class="form-control" id="next_prayer_name_font_size" name="styling_settings[next_prayer_name_font_size]"
                               value="{{ old('styling_settings.next_prayer_name_font_size', $box->styling_settings['next_prayer_name_font_size'] ?? '0.9') }}"
                               placeholder="e.g., 0.8, 0.9, 1"
                               pattern="^[0-9]+(\.[0-9]+)?$">
                        <div class="form-text">Recommended: 0.8 - 1.2</div>
                    </div>
                    <div class="col-md-6">
                        <label for="next_prayer_name_color" class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" 
                               id="next_prayer_name_color" name="styling_settings[next_prayer_name_color]"
                               value="{{ old('styling_settings.next_prayer_name_color', $box->styling_settings['next_prayer_name_color'] ?? '#666666') }}">
                    </div>
                </div>
            </div>
            
            <hr>
            <div class="mb-0">
                <h6 class="text-muted"><i class="bi bi-layout-sidebar"></i> Display Position</h6>
                <select class="form-select" id="next_prayer_position" name="layout_settings[next_prayer_position]">
                    @php $position = $box->layout_settings['next_prayer_position'] ?? 'below_table'; @endphp
                    <option value="below_table" {{ $position == 'below_table' ? 'selected' : '' }}>Below Prayer Table</option>
                    <option value="above_table" {{ $position == 'above_table' ? 'selected' : '' }}>Above Prayer Table</option>
                    <option value="hidden" {{ $position == 'hidden' ? 'selected' : '' }}>Hidden</option>
                </select>
                <div class="form-text">Choose where to display the next prayer countdown section</div>
            </div>
        </div>
    </div>
    
    <!-- Section 3: Column Widths -->
    <div class="card mb-3 border-info">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0"><i class="bi bi-arrows-expand"></i> Column Widths</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">Set the width percentage for each column in the prayer times table</p>
            <div class="row">
                <div class="col-md-4">
                    <label for="column_width_1" class="form-label">Prayer Name Column</label>
                    <select class="form-select" id="column_width_1" name="layout_settings[column_widths][]">
                        @php $col1 = $box->layout_settings['column_widths'][0] ?? '30%'; @endphp
                        <option value="25%" {{ $col1 == '25%' ? 'selected' : '' }}>25%</option>
                        <option value="28%" {{ $col1 == '28%' ? 'selected' : '' }}>28%</option>
                        <option value="30%" {{ $col1 == '30%' ? 'selected' : '' }}>30%</option>
                        <option value="35%" {{ $col1 == '35%' ? 'selected' : '' }}>35%</option>
                        <option value="40%" {{ $col1 == '40%' ? 'selected' : '' }}>40%</option>
                        <option value="45%" {{ $col1 == '45%' ? 'selected' : '' }}>45%</option>
                        <option value="50%" {{ $col1 == '50%' ? 'selected' : '' }}>50%</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="column_width_2" class="form-label">Beginning Time Column</label>
                    <select class="form-select" id="column_width_2" name="layout_settings[column_widths][]">
                        @php $col2 = $box->layout_settings['column_widths'][1] ?? '35%'; @endphp
                        <option value="25%" {{ $col2 == '25%' ? 'selected' : '' }}>25%</option>
                        <option value="27.5%" {{ $col2 == '27.5%' ? 'selected' : '' }}>27.5%</option>
                        <option value="30%" {{ $col2 == '30%' ? 'selected' : '' }}>30%</option>
                        <option value="35%" {{ $col2 == '35%' ? 'selected' : '' }}>35%</option>
                        <option value="37.5%" {{ $col2 == '37.5%' ? 'selected' : '' }}>37.5%</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="column_width_3" class="form-label">Jamaat Time Column</label>
                    <select class="form-select" id="column_width_3" name="layout_settings[column_widths][]">
                        @php $col3 = $box->layout_settings['column_widths'][2] ?? '35%'; @endphp
                        <option value="25%" {{ $col3 == '25%' ? 'selected' : '' }}>25%</option>
                        <option value="27.5%" {{ $col3 == '27.5%' ? 'selected' : '' }}>27.5%</option>
                        <option value="30%" {{ $col3 == '30%' ? 'selected' : '' }}>30%</option>
                        <option value="35%" {{ $col3 == '35%' ? 'selected' : '' }}>35%</option>
                        <option value="37.5%" {{ $col3 == '37.5%' ? 'selected' : '' }}>37.5%</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 4: Column Font Sizes -->
    <div class="card mb-3 border-warning">
        <div class="card-header bg-warning text-dark">
            <h6 class="mb-0"><i class="bi bi-type"></i> Beginning & Jamaat Time Font Sizes</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">Control the size of the time values in the Beginning and Jamaat Time columns (NOT the column titles)</p>
            <div class="row">
                <div class="col-md-6">
                    <label for="beginning_font_size" class="form-label">Beginning Time Values Font Size</label>
                    <input type="text" class="form-control" id="beginning_font_size" name="styling_settings[beginning_font_size]"
                           value="{{ old('styling_settings.beginning_font_size', $box->styling_settings['beginning_font_size'] ?? '3.5') }}"
                           placeholder="e.g., 3, 3.5, 4"
                           pattern="^[0-9]+(\.[0-9]+)?$">
                    <div class="form-text">This controls the time values like "5:30 AM". Recommended: 3 - 4.5</div>
                </div>
                <div class="col-md-6">
                    <label for="jamaat_font_size" class="form-label">Jamaat Time Values Font Size</label>
                    <input type="text" class="form-control" id="jamaat_font_size" name="styling_settings[jamaat_font_size]"
                           value="{{ old('styling_settings.jamaat_font_size', $box->styling_settings['jamaat_font_size'] ?? '3.5') }}"
                           placeholder="e.g., 3, 3.5, 4"
                           pattern="^[0-9]+(\.[0-9]+)?$">
                    <div class="form-text">This controls the time values like "6:30 AM". Recommended: 3 - 4.5</div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-info alert-sm">
                        <i class="bi bi-lightbulb"></i> 
                        <strong>Tip:</strong> Set different sizes to emphasize one time over another. Example: Beginning 2rem, Jamaat 2.5rem
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 5: Column Spacing -->
    <div class="card mb-3 border-dark">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0"><i class="bi bi-distribute-horizontal"></i> Column Spacing</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">Adjust the gap between columns to improve readability</p>
            <div class="row">
                <div class="col-md-8">
                    <label for="beginning_column_spacing" class="form-label">Space Between Columns</label>
                    <div class="input-group">
                        <input type="range" class="form-range" id="beginning_column_spacing" 
                               name="layout_settings[beginning_column_spacing]"
                               value="{{ old('layout_settings.beginning_column_spacing', $box->layout_settings['beginning_column_spacing'] ?? '0') }}"
                               min="0" max="300" step="10"
                               oninput="document.getElementById('spacing_value').textContent = this.value + 'px'">
                    </div>
                    <div class="mt-2 text-center">
                        <strong id="spacing_display">Current spacing: <span id="spacing_value">{{ $box->layout_settings['beginning_column_spacing'] ?? '0' }}px</span></strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-info alert-sm mt-3">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Range:</strong> 0 - 300px
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
