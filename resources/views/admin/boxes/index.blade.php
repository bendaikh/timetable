@extends('layouts.admin')

@section('title', 'Boxes Management - Admin Panel')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Boxes Management</h1>
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                    <button class="btn btn-outline-primary" onclick="refreshPreviewFrame()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh Preview
                    </button>
                    <button class="btn btn-primary" onclick="initializeDefaults()">
                        <i class="bi bi-arrow-clockwise"></i> Initialize Defaults
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Live Preview Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-eye"></i> Live Preview
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="preview-container preview-container-frame">
                                <iframe id="previewFrame" src="{{ route('timetable.index') }}" 
                                        style="width: 100%; height: 600px; border: 1px solid #ddd; border-radius: 5px;">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boxes Grid -->
            <div class="row">
                @foreach($boxes as $box)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card box-card" data-box-type="{{ $box->box_type }}">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0">{{ $box->box_name }}</h6>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary btn-sm" onclick="toggleBoxActive('{{ $box->box_type }}')">
                                    <i class="bi bi-power {{ $box->is_active ? 'text-success' : 'text-danger' }}"></i>
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="resetBox('{{ $box->box_type }}')">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="box-preview" id="preview-{{ $box->box_type }}">
                                <!-- Box preview will be loaded here -->
                            </div>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">Status:</small>
                                    <span class="badge {{ $box->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $box->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Type:</small>
                                    <small class="text-muted">{{ ucwords(str_replace('_', ' ', $box->box_type)) }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('admin.boxes.edit', $box->box_type) }}" 
                               class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-pencil"></i> Edit Box
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Box Type Descriptions -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Box Types Reference</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Header Box</h6>
                                    <p class="text-muted">Displays current time, English date, and Islamic date with fullscreen button.</p>
                                    
                                    <h6>Prayer Times Table</h6>
                                    <p class="text-muted">Shows prayer times with beginning and Jamaat times in a structured table.</p>
                                    
                                    <h6>Next Prayer Note</h6>
                                    <p class="text-muted">Displays countdown to next prayer time.</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Announcements</h6>
                                    <p class="text-muted">Community announcements with smart display (one long or two short).</p>
                                    
                                    <h6>Donation Appeal</h6>
                                    <p class="text-muted">Appeal for masjid expansion project donations.</p>
                                    
                                    <h6>Welcome Message</h6>
                                    <p class="text-muted">Welcome message with user name display at the bottom.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Quick Edit -->
<div class="modal fade" id="quickEditModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Edit Box</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="quickEditContent">
                    <!-- Quick edit form will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        loadBoxPreviews();
        refreshPreviewFrame();
    });

    // Load previews for all boxes
    function loadBoxPreviews() {
        @foreach($boxes as $box)
            loadBoxPreview('{{ $box->box_type }}');
        @endforeach
    }

    // Load preview for a specific box
    function loadBoxPreview(boxType) {
        fetch(`/admin/boxes/${boxType}/preview`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const previewElement = document.getElementById(`preview-${boxType}`);
                if (previewElement && data) {
                    previewElement.innerHTML = generateBoxPreview(data, boxType);
                }
            })
            .catch(error => {
                console.error('Error loading box preview:', error);
            });
    }

    // Generate preview HTML for a box
    function generateBoxPreview(boxData, boxType) {
        const styling = boxData.styling_settings || {};
        const content = boxData.content_settings || {};
        
        let previewHTML = '';
        
        switch(boxType) {
            case 'header_box':
                previewHTML = `
                    <div style="background-color: ${styling.background_color || '#1e4d2b'}; color: ${styling.text_color || '#000000'}; padding: 10px; border-radius: 5px;">
                        <div style="font-size: 20px; font-weight: bold;">02:24:13 PM</div>
                        <div style="font-size: 12px;">Wed 15 Oct 2025</div>
                        <div style="font-size: 12px;">18 Safar 1447</div>
                    </div>
                `;
                break;
                
            case 'prayer_times_box':
                previewHTML = `
                    <div style="background-color: ${styling.background_color || '#fdf7e6'}; color: ${styling.text_color || '#000000'}; padding: 10px; border-radius: 5px;">
                        <div style="background-color: ${styling.header_background_color || '#0066cc'}; color: ${styling.header_text_color || '#ffffff'}; padding: 5px; margin-bottom: 5px; text-align: center;">
                            Prayer Times
                        </div>
                        <div style="font-size: 12px;">Fajr: 05:38 | 06:45</div>
                        <div style="font-size: 12px;">Zohar: 12:58 | 01:30</div>
                        <div style="font-size: 12px;">Asr: 04:16 | 05:00</div>
                    </div>
                `;
                break;
                
            case 'note_prayer_box':
                previewHTML = `
                    <div style="background-color: ${styling.background_color || '#fdf7e6'}; color: ${styling.text_color || '#000000'}; padding: 10px; border-radius: 5px; text-align: center;">
                        <div style="font-weight: bold; margin-bottom: 5px;">${content.text || 'Next prayer in:'}</div>
                        <div style="font-size: 18px; font-weight: bold;">02:45:32</div>
                        <div style="font-size: 11px; opacity: 0.8;">Asr</div>
                    </div>
                `;
                break;
                
            
            case 'announcements_box':
                previewHTML = `
                    <div style="background-color: ${styling.background_color || '#fdf7e6'}; color: ${styling.text_color || '#000000'}; padding: 10px; border-radius: 5px;">
                        <div style="font-weight: bold; margin-bottom: 5px;">${content.title || 'Announcements'}</div>
                        <div style="font-size: 12px;">Community Iftar every evening during Ramadan.</div>
                        <div style="font-size: 12px;">All families are welcome to join.</div>
                    </div>
                `;
                break;
                
            case 'welcome_box':
                previewHTML = `
                    <div style="background-color: ${styling.background_color || '#1e4d2b'}; color: ${styling.text_color || '#FFD700'}; padding: 10px; border-radius: 5px; font-weight: bold;">
                        ${content.welcome_text || 'Hello imran Welcome to timetable'}
                    </div>
                `;
                break;
                
            default:
                previewHTML = `
                    <div style="background-color: ${styling.background_color || '#fdf7e6'}; color: ${styling.text_color || '#000000'}; padding: 10px; border-radius: 5px; text-align: center;">
                        <div style="font-size: 12px;">${boxData.box_name || 'Box Preview'}</div>
                    </div>
                `;
        }
        
        return previewHTML;
    }

    // Toggle box active status
    function toggleBoxActive(boxType) {
        fetch(`/admin/boxes/${boxType}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to update status
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to update box status'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update box status');
        });
    }

    // Reset box to defaults
    function resetBox(boxType) {
        if (confirm('Are you sure you want to reset this box to default settings? This will overwrite all current customizations.')) {
            fetch(`/admin/boxes/${boxType}/reset`, {
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

    // Initialize defaults
    function initializeDefaults() {
        if (confirm('WARNING: This will DELETE ALL existing box configurations and reset them to factory defaults. This action cannot be undone. Continue?')) {
            fetch('/admin/boxes/initialize-defaults', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Box configurations reset successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to initialize defaults'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to initialize defaults');
            });
        }
    }

    // Refresh preview frame
    function refreshPreviewFrame() {
        const frame = document.getElementById('previewFrame');
        if (frame) {
            const base = frame.src.split('?')[0];
            frame.src = `${base}?cb=${Date.now()}`;
        }
    }

    // Auto-refresh preview every 30 seconds
    setInterval(refreshPreviewFrame, 5000);
</script>
@endsection
