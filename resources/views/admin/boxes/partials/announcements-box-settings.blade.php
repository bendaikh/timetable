<!-- Announcements Box Specific Settings -->
<div class="mb-4">
    <!-- Section 1: Header & Title Settings -->
    <div class="card mb-3 border-primary">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="bi bi-chat-square-text"></i> Announcements Box Header</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <h6 class="text-muted"><i class="bi bi-pencil"></i> Title Configuration</h6>
                <div class="row">
                    <div class="col-md-12">
                        <label for="title" class="form-label">Section Title</label>
                        <input type="text" class="form-control" id="title" name="content_settings[title]"
                               value="{{ old('content_settings.title', $box->content_settings['title'] ?? 'Announcements') }}"
                               placeholder="e.g., Announcements, Community News">
                        <div class="form-text">Text displayed at the top of the announcements section</div>
                    </div>
                </div>
            </div>

            <hr>
            <div class="mb-3">
                <h6 class="text-muted"><i class="bi bi-palette"></i> Header Colors</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label for="title_background_color" class="form-label">Header Background Color</label>
                        <input type="color" class="form-control form-control-color"
                               id="title_background_color" name="styling_settings[title_background_color]"
                               value="{{ old('styling_settings.title_background_color', $box->styling_settings['title_background_color'] ?? '#1E4D2B') }}">
                        <div class="form-text">Default mosque green: #1E4D2B</div>
                    </div>
                    <div class="col-md-6">
                        <label for="title_color" class="form-label">Header Text Color</label>
                        <input type="color" class="form-control form-control-color"
                               id="title_color" name="styling_settings[title_color]"
                               value="{{ old('styling_settings.title_color', $box->styling_settings['title_color'] ?? '#ffffff') }}">
                    </div>
                </div>
            </div>
            
            <hr>
            <div class="mb-0">
                <h6 class="text-muted"><i class="bi bi-type"></i> Title Font Size</h6>
                <div class="row">
                    <div class="col-md-12">
                        <label for="title_font_size" class="form-label">Font Size (rem)</label>
                        <input type="text" class="form-control" id="title_font_size" name="styling_settings[title_font_size]"
                               value="{{ old('styling_settings.title_font_size', \App\Support\CssUnits::normalizeBoxRem($box->styling_settings['title_font_size'] ?? null, '1.6rem')) }}"
                               placeholder="e.g., 1.2rem">
                        <div class="form-text mt-2">Matches the prayer table header scale. Recommended: 1rem - 3.5rem</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section 2: Box Appearance -->
    <div class="card mb-3 border-info">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0"><i class="bi bi-palette"></i> Box Appearance</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">Control the look and style of the announcements box container</p>
            
            <div class="mb-3">
                <h6 class="text-muted"><i class="bi bi-fill"></i> Colors</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label for="border_color" class="form-label">Border Color</label>
                        <input type="color" class="form-control form-control-color" 
                               id="border_color" name="styling_settings[border_color]"
                               value="{{ old('styling_settings.border_color', $box->styling_settings['border_color'] ?? '#cccccc') }}">
                    </div>
                </div>
            </div>
            
            <hr>
            <div class="mb-3">
                <h6 class="text-muted"><i class="bi bi-border"></i> Border</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label for="border_width" class="form-label">Border Width</label>
                        <select class="form-select" id="border_width" name="styling_settings[border_width]">
                            @php $borderWidth = $box->styling_settings['border_width'] ?? '1px'; @endphp
                            <option value="0px" {{ $borderWidth == '0px' ? 'selected' : '' }}>None</option>
                            <option value="1px" {{ $borderWidth == '1px' ? 'selected' : '' }}>1px (Thin)</option>
                            <option value="2px" {{ $borderWidth == '2px' ? 'selected' : '' }}>2px (Medium)</option>
                            <option value="3px" {{ $borderWidth == '3px' ? 'selected' : '' }}>3px (Thick)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="font_family" class="form-label">Font Family</label>
                        <select class="form-select" id="font_family" name="styling_settings[font_family]">
                            @php $fontFamily = $box->styling_settings['font_family'] ?? 'Arial, sans-serif'; @endphp
                            <option value="Arial, sans-serif" {{ $fontFamily == 'Arial, sans-serif' ? 'selected' : '' }}>Arial</option>
                            <option value="'Courier New', monospace" {{ $fontFamily == "'Courier New', monospace" ? 'selected' : '' }}>Courier New</option>
                            <option value="'Times New Roman', serif" {{ $fontFamily == "'Times New Roman', serif" ? 'selected' : '' }}>Times New Roman</option>
                            <option value="'Georgia', serif" {{ $fontFamily == "'Georgia', serif" ? 'selected' : '' }}>Georgia</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <hr>
            <div class="mb-0">
                <h6 class="text-muted"><i class="bi bi-arrows-expand"></i> Padding</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label for="padding" class="form-label">Internal Spacing (px)</label>
                        <input type="number" class="form-control" id="padding" name="styling_settings[padding]"
                               value="{{ old('styling_settings.padding', $box->styling_settings['padding'] ?? '15') }}"
                               min="5" max="50" step="5">
                        <div class="form-text">Space between border and content</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
