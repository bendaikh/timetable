@extends('layouts.admin')

@section('title', 'Announcements')
@section('page-icon')
{!! '<i class="bi bi-megaphone me-2"></i>' !!}
@endsection
@section('page-title', 'Announcements')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="bi bi-gear me-2"></i>
                    Display Settings
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label"><strong>Announcement Display Mode</strong></label>
                        <div class="btn-group" role="group" style="width: 100%;">
                            <input type="radio" class="btn-check" name="displayMode" id="modeRotation" value="rotation" checked>
                            <label class="btn btn-outline-primary" for="modeRotation" onclick="updateAnnouncementMode('rotation')">
                                <i class="bi bi-arrow-repeat me-2"></i> Rotation Mode
                            </label>

                            <input type="radio" class="btn-check" name="displayMode" id="modeShowAll" value="show-all">
                            <label class="btn btn-outline-primary" for="modeShowAll" onclick="updateAnnouncementMode('show-all')">
                                <i class="bi bi-list me-2"></i> Show All
                            </label>
                        </div>
                        <small class="form-text text-muted d-block mt-2">
                            <strong>Rotation:</strong> Shows one announcement at a time, rotates based on duration<br>
                            <strong>Show All:</strong> Displays all announcements stacked vertically
                        </small>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-info mb-0">
                            <strong><i class="bi bi-info-circle me-2"></i>Current Display:</strong>
                            <span id="currentModeDisplay">Rotation Mode</span>
                            <br>
                            <small>Changes apply to the TV display in real-time</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-megaphone me-2"></i>
                    Announcements Management
                </h5>
                <a href="{{ route('admin.announcements.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-circle me-2"></i>
                    Add Announcement
                </a>
            </div>
            <div class="card-body">
                @if($announcements->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Content</th>
                                    <th>Font Size</th>
                                    <th>Status</th>
                                    <th>Auto Repeat</th>
                                    <th>Duration</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($announcements as $announcement)
                                <tr>
                                    <td>
                                        <strong>{{ $announcement->title }}</strong>
                                    </td>
                                    <td>
                                        <div style="max-width: 300px;">
                                            {{ Str::limit($announcement->content, 80) }}
                                            @if(strlen($announcement->content) > 300 && $announcement->font_size > 60)
                                                <div class="alert alert-warning alert-sm py-1 px-2 mt-2 mb-0" style="font-size: 12px;">
                                                    <i class="bi bi-exclamation-triangle"></i> Long text with large font may be hidden
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $announcement->font_size }}px</span>
                                        @if($announcement->font_size > 80)
                                            <div style="font-size: 11px; color: #ff6b6b; margin-top: 2px;">
                                                <i class="bi bi-exclamation-circle"></i> Large font
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $announcement->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $announcement->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($announcement->auto_repeat)
                                            <span class="badge bg-info">
                                                <i class="bi bi-arrow-repeat me-1"></i>
                                                {{ implode(', ', $announcement->repeat_days ?? []) }}
                                            </span>
                                        @else
                                            <span class="text-muted">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ ceil($announcement->display_duration / 60) }}m</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $announcement->created_at->format('M j, Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.announcements.show', $announcement) }}" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this announcement?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center">
                        {{ $announcements->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-megaphone display-4 text-muted"></i>
                        <h4 class="mt-3 text-muted">No Announcements Found</h4>
                        <p class="text-muted">Start by creating your first announcement for the masjid.</p>
                        <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>
                            Create First Announcement
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Store current display mode in localStorage
    const DISPLAY_MODE_KEY = 'announcementDisplayMode';
    
    function updateAnnouncementMode(mode) {
        // Save to localStorage
        localStorage.setItem(DISPLAY_MODE_KEY, mode);
        
        // Update the display text
        const displayText = mode === 'rotation' ? 'Rotation Mode' : 'Show All Mode';
        document.getElementById('currentModeDisplay').textContent = displayText;
        
        // Send update to all open windows/tabs via localStorage event
        // (The TV display window will listen for this change)
        window.dispatchEvent(new CustomEvent('announcementModeChanged', { 
            detail: { mode: mode } 
        }));
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        const savedMode = localStorage.getItem(DISPLAY_MODE_KEY) || 'rotation';
        document.getElementById(savedMode === 'rotation' ? 'modeRotation' : 'modeShowAll').checked = true;
        const displayText = savedMode === 'rotation' ? 'Rotation Mode' : 'Show All Mode';
        document.getElementById('currentModeDisplay').textContent = displayText;
    });
</script>
@endsection
