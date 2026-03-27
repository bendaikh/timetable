@extends('layouts.admin')

@section('title', 'Edit ' . $box->box_name . ' - Boxes Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Edit {{ $box->box_name }}</h1>
                <div>
                    <a href="{{ route('admin.boxes.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Boxes
                    </a>
                    <button class="btn btn-warning" onclick="resetToDefaults()">
                        <i class="bi bi-arrow-clockwise"></i> Reset to Defaults
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Edit Form -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Box Configuration</h5>
                        </div>
                        <div class="card-body">
                            <form id="boxEditForm" method="POST" action="{{ route('admin.boxes.update', $box->box_type) }}">
                                @csrf
                                @method('PUT')
                                
                                <!-- Basic Settings -->
                                <div class="mb-4">
                                    <h6>Basic Settings</h6>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <label for="box_name" class="form-label">Box Name</label>
                                            <input type="text" class="form-control" id="box_name" name="box_name" 
                                                   value="{{ old('box_name', $box->box_name) }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Status</label>
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="is_active" value="0">
                                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                                       {{ old('is_active', $box->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">
                                                    Active
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Content Settings -->
                                @if($box->box_type !== 'header_box' && $box->box_type !== 'sliding_text_box' && $box->box_type !== 'prayer_times_box' && $box->box_type !== 'special_times_box' && $box->box_type !== 'announcements_box' && $box->box_type !== 'timetable_background_box')
                                <div class="mb-4">
                                    <h6>Content Settings</h6>
                                    <div id="contentSettings">
                                        @foreach($box->content_settings ?? [] as $key => $value)
                                            @include('admin.boxes.partials.content-field', [
                                                'key' => $key, 
                                                'value' => $value, 
                                                'boxType' => $box->box_type
                                            ])
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <!-- Styling Settings -->
                                @if($box->box_type !== 'timetable_background_box')
                                <div class="mb-4">
                                    <h6>Styling Settings</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="background_color" class="form-label">Background Color</label>
                                            <input type="color" class="form-control form-control-color" 
                                                   id="background_color" name="styling_settings[background_color]"
                                                   value="{{ old('styling_settings.background_color', $box->getBackgroundColorHex()) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="text_color" class="form-label">Text Color</label>
                                            <input type="color" class="form-control form-control-color" 
                                                   id="text_color" name="styling_settings[text_color]"
                                                   value="{{ old('styling_settings.text_color', $box->styling_settings['text_color'] ?? '#000000') }}">
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <label for="font_family" class="form-label">Font Family</label>
                                            <select class="form-select" id="font_family" name="styling_settings[font_family]">
                                                <option value="Arial, sans-serif" {{ ($box->styling_settings['font_family'] ?? '') == 'Arial, sans-serif' ? 'selected' : '' }}>Arial</option>
                                                <option value="Times New Roman, serif" {{ ($box->styling_settings['font_family'] ?? '') == 'Times New Roman, serif' ? 'selected' : '' }}>Times New Roman</option>
                                                <option value="Courier New, monospace" {{ ($box->styling_settings['font_family'] ?? '') == 'Courier New, monospace' ? 'selected' : '' }}>Courier New</option>
                                                <option value="Georgia, serif" {{ ($box->styling_settings['font_family'] ?? '') == 'Georgia, serif' ? 'selected' : '' }}>Georgia</option>
                                                <option value="Verdana, sans-serif" {{ ($box->styling_settings['font_family'] ?? '') == 'Verdana, sans-serif' ? 'selected' : '' }}>Verdana</option>
                                                <option value="Amiri, serif" {{ ($box->styling_settings['font_family'] ?? '') == 'Amiri, serif' ? 'selected' : '' }}>Amiri (Arabic)</option>
                                            </select>
                                        </div>
                                        @if($box->box_type !== 'header_box' && $box->box_type !== 'sliding_text_box' && $box->box_type !== 'prayer_times_box' && $box->box_type !== 'announcements_box')
                                        <div class="col-md-6">
                                            <label for="font_size" class="form-label">Font Size</label>
                                            <input type="text" class="form-control" id="font_size" name="styling_settings[font_size]"
                                                   value="{{ old('styling_settings.font_size', $box->styling_settings['font_size'] ?? '16px') }}"
                                                   placeholder="e.g., 16px, 1.2em">
                                        </div>
                                        @endif
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <label for="border_color" class="form-label">Border Color</label>
                                            <input type="color" class="form-control form-control-color" 
                                                   id="border_color" name="styling_settings[border_color]"
                                                   value="{{ old('styling_settings.border_color', $box->styling_settings['border_color'] ?? '#0066cc') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="border_width" class="form-label">Border Width</label>
                                            <input type="text" class="form-control" id="border_width" name="styling_settings[border_width]"
                                                   value="{{ old('styling_settings.border_width', $box->styling_settings['border_width'] ?? '1px') }}"
                                                   placeholder="e.g., 1px, 2px">
                                        </div>
                                        @if($box->box_type !== 'header_box' && $box->box_type !== 'sliding_text_box' && $box->box_type !== 'prayer_times_box' && $box->box_type !== 'special_times_box' && $box->box_type !== 'announcements_box')
                                        <div class="col-md-4">
                                            <label for="border_radius" class="form-label">Border Radius</label>
                                            <input type="text" class="form-control" id="border_radius" name="styling_settings[border_radius]"
                                                   value="{{ old('styling_settings.border_radius', $box->styling_settings['border_radius'] ?? '0px') }}"
                                                   placeholder="e.g., 0px, 5px">
                                        </div>
                                        @endif
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <label for="padding" class="form-label">Padding</label>
                                            <input type="text" class="form-control" id="padding" name="styling_settings[padding]"
                                                   value="{{ old('styling_settings.padding', $box->styling_settings['padding'] ?? '15px') }}"
                                                   placeholder="e.g., 15px, 10px 20px">
                                        </div>
                                        @if($box->box_type !== 'header_box' && $box->box_type !== 'prayer_times_box' && $box->box_type !== 'special_times_box' && $box->box_type !== 'announcements_box')
                                        <div class="col-md-6">
                                            <label for="text_alignment" class="form-label">Text Alignment</label>
                                            <select class="form-select" id="text_alignment" name="layout_settings[text_alignment]">
                                                <option value="left" {{ ($box->layout_settings['text_alignment'] ?? '') == 'left' ? 'selected' : '' }}>Left</option>
                                                <option value="center" {{ ($box->layout_settings['text_alignment'] ?? '') == 'center' ? 'selected' : '' }}>Center</option>
                                                <option value="right" {{ ($box->layout_settings['text_alignment'] ?? '') == 'right' ? 'selected' : '' }}>Right</option>
                                                <option value="justify" {{ ($box->layout_settings['text_alignment'] ?? '') == 'justify' ? 'selected' : '' }}>Justify</option>
                                            </select>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                <!-- Box-specific settings -->
                                @if($box->box_type === 'header_box')
                                    @include('admin.boxes.partials.header-box-settings', ['box' => $box])
                                @elseif($box->box_type === 'prayer_times_box')
                                    @include('admin.boxes.partials.prayer-times-box-settings', ['box' => $box])
                                @elseif($box->box_type === 'note_prayer_box')
                                    @include('admin.boxes.partials.note-prayer-box-settings', ['box' => $box])
                                @elseif($box->box_type === 'special_times_box')
                                    @include('admin.boxes.partials.special-times-box-settings', ['box' => $box])
                                @elseif($box->box_type === 'announcements_box')
                                    @include('admin.boxes.partials.announcements-box-settings', ['box' => $box])
                                @elseif($box->box_type === 'welcome_box')
                                    @include('admin.boxes.partials.welcome-box-settings', ['box' => $box])
                                @elseif($box->box_type === 'timetable_background_box')
                                    @include('admin.boxes.partials.timetable-background-box-settings', ['box' => $box])
                                @endif

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check"></i> Save Changes
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="previewChanges()">
                                        <i class="bi bi-eye"></i> Preview Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Live Preview -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Live Preview</h5>
                        </div>
                        <div class="card-body">
                            <div id="livePreview" class="preview-container">
                                <!-- Preview will be updated here -->
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshPreview()">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openFullPreview()">
                                    <i class="bi bi-arrows-fullscreen"></i> Full Preview
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Box Information -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Box Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <strong>Type:</strong><br>
                                    <small class="text-muted">{{ ucwords(str_replace('_', ' ', $box->box_type)) }}</small>
                                </div>
                                <div class="col-6">
                                    <strong>Status:</strong><br>
                                    <span class="badge {{ $box->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $box->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6">
                                    <strong>Created:</strong><br>
                                    <small class="text-muted">{{ $box->created_at->format('M j, Y') }}</small>
                                </div>
                                <div class="col-6">
                                    <strong>Updated:</strong><br>
                                    <small class="text-muted">{{ $box->updated_at->format('M j, Y') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Full Preview Modal -->
<div class="modal fade" id="fullPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Full Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <iframe id="fullPreviewFrame" src="{{ route('timetable.index') }}" 
                        style="width: 100%; height: 600px; border: 1px solid #ddd; border-radius: 5px;">
                </iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        refreshPreview();
        
        // Add real-time preview updates
        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.addEventListener('input', debounce(updatePreview, 500));
            element.addEventListener('change', updatePreview);
        });

        ['time_font_size', 'date_font_size'].forEach((fieldId) => {
            const field = document.getElementById(fieldId);

            if (!field) {
                return;
            }

            field.addEventListener('blur', function() {
                const normalized = normalizeHeaderFontInput(this.value);

                if (normalized && normalized !== this.value) {
                    this.value = normalized;
                    updatePreview();
                }
            });
        });
    });

    // Debounce function to limit API calls
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function normalizeCssValue(value, fallbackUnit = '') {
        if (value === null || typeof value === 'undefined' || value === '') {
            return '';
        }

        if (typeof value === 'number') {
            return `${value}${fallbackUnit}`;
        }

        const trimmed = String(value).trim();
        if (!trimmed) {
            return '';
        }

        if (!fallbackUnit) {
            return trimmed;
        }

        if (/^-?\d+(\.\d+)?$/.test(trimmed)) {
            return `${trimmed}${fallbackUnit}`;
        }

        return trimmed;
    }

    function normalizeHeaderFontInput(value) {
        const trimmed = String(value ?? '').trim();

        if (!trimmed) {
            return '';
        }

        if (/^-?\d+(\.\d+)?$/.test(trimmed)) {
            return `${trimmed}rem`;
        }

        return trimmed;
    }

    // Update live preview
    function tokenizeFormKey(key) {
        const tokens = [];
        key.replace(/([^[\]]+)|\[(.*?)\]/g, (_, plain, bracket) => {
            tokens.push(typeof plain !== 'undefined' ? plain : bracket);
        });
        return tokens;
    }

    function assignFormValue(target, key, value) {
        const tokens = tokenizeFormKey(key);
        let current = target;

        tokens.forEach((token, index) => {
            const isLast = index === tokens.length - 1;
            const nextToken = tokens[index + 1];

            if (isLast) {
                if (token === '') {
                    current.push(value);
                } else {
                    current[token] = value;
                }
                return;
            }

            if (token === '') {
                const newEntry = nextToken === '' ? [] : {};
                current.push(newEntry);
                current = newEntry;
                return;
            }

            if (!Object.prototype.hasOwnProperty.call(current, token)) {
                current[token] = nextToken === '' ? [] : {};
            }

            current = current[token];
        });
    }

    function serializeBoxForm(form) {
        const parsedData = {};

        for (const [key, value] of new FormData(form).entries()) {
            if (key === '_token' || key === '_method') {
                continue;
            }

            assignFormValue(parsedData, key, value);
        }

        if (parsedData.styling_settings) {
            ['time_font_size', 'date_font_size'].forEach((field) => {
                if (typeof parsedData.styling_settings[field] !== 'undefined') {
                    parsedData.styling_settings[field] = normalizeHeaderFontInput(parsedData.styling_settings[field]);
                }
            });
        }

        return parsedData;
    }

    function updatePreview(options = {}) {
        const parsedData = serializeBoxForm(document.getElementById('boxEditForm'));

        fetch(`{{ route('admin.boxes.update-ajax', $box->box_type) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(parsedData)
        })
        .then(response => {
            if (!response.ok) {
                return response.json()
                    .catch(() => ({}))
                    .then((payload) => {
                        const message = payload.error || payload.message || JSON.stringify(payload.errors || {}) || `HTTP error! status: ${response.status}`;
                        throw new Error(message);
                    });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                refreshPreview();
                if (options.openModal) {
                    toggleFullPreview();
                } else {
                    refreshFullPreview();
                }
            } else {
                console.error('Update failed:', data.error || data.message);
                alert(data.error || data.message || 'Preview could not be updated.');
            }
        })
        .catch(error => {
            console.error('Error updating preview:', error);
            alert(error.message || 'Preview could not be updated. Please try again.');
        });
    }

    // Refresh preview
    function refreshPreview() {
        const previewElement = document.getElementById('livePreview');
        const boxType = '{{ $box->box_type }}';
        
        fetch(`{{ route('admin.boxes.preview', $box->box_type) }}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data) {
                    previewElement.innerHTML = generatePreviewHTML(data, boxType);
                }
            })
            .catch(error => {
                console.error('Error loading preview:', error);
            });
    }

    function toPreviewRem(value, fallbackRem, maxRem) {
        const numericValue = Number.parseFloat(String(value ?? '').replace(/rem$/i, '').trim());
        const safeValue = Number.isFinite(numericValue) ? numericValue : fallbackRem;
        return `${Math.min(safeValue, maxRem)}rem`;
    }

    function getPreviewHeaderFontSizes(styling) {
        const rawTimeRem = Number.parseFloat(String(styling.time_font_size ?? '').replace(/rem$/i, '').trim());
        const rawDateRem = Number.parseFloat(String(styling.date_font_size ?? '').replace(/rem$/i, '').trim());

        const safeTimeRem = Number.isFinite(rawTimeRem) ? rawTimeRem : 3;
        const safeDateRem = Number.isFinite(rawDateRem) ? rawDateRem : 1.6;

        const scale = Math.min(1, 3.4 / safeTimeRem, 2.1 / safeDateRem);

        return {
            time: `${(safeTimeRem * scale).toFixed(2)}rem`,
            date: `${(safeDateRem * scale).toFixed(2)}rem`,
        };
    }

    // Generate preview HTML
    function generatePreviewHTML(boxData, boxType) {
        const styling = boxData.styling_settings || {};
        const content = boxData.content_settings || {};
        
        let styleString = `
            background-color: ${styling.background_color || '#fdf7e6'};
            color: ${styling.text_color || '#000000'};
            font-family: ${styling.font_family || 'Arial, sans-serif'};
            font-size: ${styling.font_size || '16px'};
            border: ${styling.border_width || '1px'} solid ${styling.border_color || '#0066cc'};
            border-radius: ${styling.border_radius || '0px'};
            padding: ${styling.padding || '15px'};
            text-align: ${boxData.layout_settings?.text_alignment || 'left'};
        `;
        
        switch(boxType) {
            case 'header_box':
                const previewFontSizes = getPreviewHeaderFontSizes(styling);
                const previewPadding = normalizeCssValue(styling.padding, 'px') || '15px';
                return `
                    <div style="${styleString}; width: 100%; overflow: hidden; padding: ${previewPadding};">
                        <div style="display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr) minmax(0, 1fr); align-items: center; gap: 10px; min-height: 110px; position: relative; padding-right: 44px;">
                            <div style="text-align: center; min-width: 0; overflow: hidden;">
                                <div style="font-size: ${previewFontSizes.time}; font-weight: bold; line-height: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">02:24:13 PM</div>
                            </div>
                            <div style="text-align: center; min-width: 0; overflow: hidden;">
                                <div style="font-size: ${previewFontSizes.date}; line-height: 1.15; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Wed 15 Oct 2025</div>
                            </div>
                            <div style="text-align: center; min-width: 0; overflow: hidden;">
                                <div style="font-size: ${previewFontSizes.date}; line-height: 1.15; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">18 Safar 1447</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-light" onclick="openFullPreview()" style="position: absolute; right: 0; bottom: 0;" title="Open full preview">⛶</button>
                        </div>
                    </div>
                `;
                
            case 'prayer_times_box':
                const col1Width = boxData.layout_settings?.column_widths?.[0] || '45%';
                const col2Width = boxData.layout_settings?.column_widths?.[1] || '25%';
                const col3Width = boxData.layout_settings?.column_widths?.[2] || '25%';
                const header1 = content.table_headers?.[0] || '';
                const header2 = content.table_headers?.[1] || 'Beginning';
                const header3 = content.table_headers?.[2] || 'Jamaat Time';
                
                return `
                    <div style="${styleString}">
                        <div style="background-color: ${styling.header_background_color || 'transparent'}; color: ${styling.header_text_color || '#000000'}; padding: 8px; margin: -15px -15px 10px -15px; text-align: center; font-weight: bold; display: grid; grid-template-columns: ${col1Width} ${col2Width} ${col3Width}; gap: 10px;">
                            <div style="text-align: left;">${header1}</div>
                            <div style="text-align: center;">${header2}</div>
                            <div style="text-align: center;">${header3}</div>
                        </div>
                        <div style="display: grid; grid-template-columns: ${col1Width} ${col2Width} ${col3Width}; gap: 10px; font-size: 14px; margin-bottom: 8px;">
                            <div style="text-align: left;">Fajr</div>
                            <div style="text-align: center;">05:38</div>
                            <div style="text-align: center;">06:45</div>
                        </div>
                        <div style="display: grid; grid-template-columns: ${col1Width} ${col2Width} ${col3Width}; gap: 10px; font-size: 14px; margin-bottom: 8px;">
                            <div style="text-align: left;">Zohar</div>
                            <div style="text-align: center;">12:58</div>
                            <div style="text-align: center;">01:30</div>
                        </div>
                        <div style="display: grid; grid-template-columns: ${col1Width} ${col2Width} ${col3Width}; gap: 10px; font-size: 14px;">
                            <div style="text-align: left;">Asr</div>
                            <div style="text-align: center;">04:16</div>
                            <div style="text-align: center;">05:00</div>
                        </div>
                    </div>
                `;
                
            case 'note_prayer_box':
                return `
                    <div style="${styleString}">
                        <div style="font-weight: bold; margin-bottom: 8px; text-align: center;">
                            ${content.text || 'Next prayer in:'}
                        </div>
                        <div style="font-size: 1.4rem; font-weight: bold; text-align: center;">
                            02:45:32
                        </div>
                        <div style="font-size: 0.9rem; margin-top: 5px; opacity: 0.8; text-align: center;">
                            Asr
                        </div>
                    </div>
                `;
                
            case 'special_times_box':
                return `
                    <div style="${styleString}">
                        <div style="background-color: ${styling.header_background_color || '#0066cc'}; color: ${styling.header_text_color || '#ffffff'}; padding: 6px; margin: -15px -15px 8px -15px; text-align: center; font-weight: bold; font-size: 12px;">
                            Special Times
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                            <span>Sehri Ends</span>
                            <span>Sun Rise</span>
                            <span>Noon</span>
                            <span>Jumu'ah 1</span>
                            <span>Jumu'ah 2</span>
                            <span>Eid 1</span>
                            <span>Eid 2</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 11px;">
                            <span>05:38</span>
                            <span>07:35</span>
                            <span>12:58</span>
                            <span>--:--</span>
                            <span>--:--</span>
                            <span>--:--</span>
                            <span>--:--</span>
                        </div>
                    </div>
                `;
                
            case 'hadeeth_box':
                return `
                    <div style="${styleString}">
                        <div style="font-weight: bold; margin-bottom: 10px; text-align: center;">
                            ${content.title || 'Hadeeth Of The Day'}
                        </div>
                        <div style="font-family: serif; font-size: 16px; text-align: center; margin-bottom: 10px;">
                            قَالَ رَسُولُ اللَّهِ صَلَّى اللهُ عَلَيْهِ وَسَلَّمَ
                        </div>
                        <div style="font-size: 14px; text-align: center; margin-bottom: 5px;">
                            "Actions are but by intention"
                        </div>
                        <div style="font-size: 12px; text-align: center; color: #666;">
                            Sahih Bukhari 1
                        </div>
                    </div>
                `;
                
            case 'announcements_box':
                const titleFontSize = normalizeCssValue(styling.title_font_size, 'px') || '28px';
                const titleColor = styling.title_color || '#000000';
                const titleText = content.title || 'Announcements';
                return `
                    <div style="${styleString}">
                        <div style="font-weight: bold; margin-bottom: 10px; font-size: ${titleFontSize}; color: ${titleColor};">
                            ${titleText}
                        </div>
                        <div style="margin-bottom: 8px;">
                            <strong>Community Iftar</strong><br>
                            <small>Community Iftar every evening during Ramadan. All families are welcome to join.</small>
                        </div>
                        <div>
                            <strong>Donation Appeal</strong><br>
                            <small>Help support our masjid expansion project. Donations are greatly appreciated.</small>
                        </div>
                    </div>
                `;
                
            case 'welcome_box':
                return `
                    <div style="${styleString}">
                        ${content.welcome_text || 'Hello imran Welcome to timetable - Manage your prayer times, announcement'}
                    </div>
                `;
                
            default:
                return `
                    <div style="${styleString}">
                        <div style="text-align: center;">${boxData.box_name || 'Box Preview'}</div>
                    </div>
                `;
        }
    }

    // Preview changes
    function previewChanges() {
        updatePreview({ openModal: true });
    }

    function openFullPreview() {
        updatePreview({ openModal: true });
    }

    // Refresh full preview
    function refreshFullPreview() {
        const frame = document.getElementById('fullPreviewFrame');
        if (frame) {
            const base = frame.src.split('?')[0];
            frame.src = `${base}?cb=${Date.now()}`;
        }
    }

    // Toggle full preview modal
    function toggleFullPreview() {
        const modal = new bootstrap.Modal(document.getElementById('fullPreviewModal'));
        modal.show();
        setTimeout(refreshFullPreview, 500);
    }

    // Reset to defaults
    function resetToDefaults() {
        if (confirm('Are you sure you want to reset this box to default settings? This will overwrite all current customizations.')) {
            fetch('{{ route("admin.boxes.reset", $box->box_type) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to reset box'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while resetting the box.');
            });
        }
    }

    // Sync font size range and input fields
    document.addEventListener('DOMContentLoaded', function() {
        // Title font size sync for announcements box
        const titleFontSizeRange = document.getElementById('title_font_size_range');
        const titleFontSizeInput = document.getElementById('title_font_size');
        
        if (titleFontSizeRange && titleFontSizeInput) {
            titleFontSizeRange.addEventListener('input', function() {
                titleFontSizeInput.value = this.value;
                updatePreview();
            });
            titleFontSizeInput.addEventListener('input', function() {
                titleFontSizeRange.value = this.value;
                updatePreview();
            });
        }

        // Announcement text font size sync for announcements box
        const fontSizeRange = document.getElementById('font_size_range');
        const fontSizeInput = document.getElementById('font_size');
        
        if (fontSizeRange && fontSizeInput) {
            fontSizeRange.addEventListener('input', function() {
                fontSizeInput.value = this.value;
                updatePreview();
            });
            fontSizeInput.addEventListener('input', function() {
                fontSizeRange.value = this.value;
                updatePreview();
            });
        }
    });
</script>
@endsection
