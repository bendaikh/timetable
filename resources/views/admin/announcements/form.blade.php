@extends('layouts.admin')

@section('title', isset($announcement) ? 'Edit Announcement' : 'Create Announcement')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>{{ isset($announcement) ? 'Edit Announcement' : 'New Announcement' }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ isset($announcement) ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}" 
                          method="POST" id="announcement-form">
                        @csrf
                        @if(isset($announcement))
                            @method('PUT')
                        @endif

                        <div class="form-group mb-3">
                            <label for="title" class="form-label">
                                <strong>Title</strong>
                                <span class="text-muted">(optional)</span>
                            </label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="{{ old('title', $announcement->title ?? '') }}" 
                                   maxlength="255"
                                   placeholder="Leave blank for body-only announcement">
                            @error('title')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="content" class="form-label">
                                <strong>Content (Maximum 250 characters)</strong>
                                <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="content" name="content" 
                                      rows="6" required maxlength="250"
                                      data-char-counter
                                      placeholder="Enter announcement content (max 250 characters)">{{ old('content', $announcement->content ?? '') }}</textarea>
                            <div class="mt-2 d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <span data-char-display>0/250</span> characters
                                </small>
                                <div data-char-warning style="display: none;" class="badge bg-warning text-dark"></div>
                            </div>
                            @error('content')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="display_order" class="form-label">
                                <strong>Display Order</strong>
                            </label>
                            <input type="number" class="form-control" id="display_order" name="display_order"
                                   value="{{ old('display_order', $announcement->display_order ?? 1) }}"
                                   min="1" max="9999" required>
                            <small class="text-muted d-block mt-1">
                                Lower numbers appear first (1, 2, 3…), same as posters.
                            </small>
                        </div>

                        <div class="form-group mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                       {{ old('is_active', $announcement->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active (Display on TV screen)
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                {{ isset($announcement) ? 'Update Announcement' : 'Create Announcement' }}
                            </button>
                            <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Live Preview Column -->
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h3>📺 TV Preview (Live)</h3>
                </div>
                <div class="card-body" style="
                    background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
                    color: #fff;
                    border-radius: 8px;
                    min-height: 400px;
                    display: flex;
                    flex-direction: column;
                    gap: 1rem;
                ">
                    <!-- TV Frame Simulation -->
                    <div style="
                        background: #000;
                        border: 3px solid #444;
                        border-radius: 8px;
                        padding: 1rem;
                        flex: 1;
                        display: flex;
                        flex-direction: column;
                        font-family: Arial, sans-serif;
                        overflow: hidden;
                    ">
                        <!-- Announcement Preview -->
                        <div style="
                            font-size: 1.2rem;
                            line-height: 1.4;
                            flex: 1;
                        ">
                            <div style="font-weight: bold; margin-bottom: 0.5rem; color: #4a9eff;">
                                <span id="preview-title">Announcement Title</span>
                            </div>
                            <div style="
                                color: #e0e0e0;
                                word-wrap: break-word;
                                overflow: hidden;
                                display: -webkit-box;
                                -webkit-line-clamp: 3;
                                -webkit-box-orient: vertical;
                                max-height: 4.2em;
                            " id="preview-content">
                                Enter your announcement content here...
                            </div>
                        </div>

                        <!-- Character Count Info -->
                        <div style="
                            border-top: 1px solid #666;
                            padding-top: 0.5rem;
                            margin-top: 0.5rem;
                            font-size: 0.9rem;
                            opacity: 0.7;
                        ">
                            <div id="preview-char-count">0/250 characters</div>
                        </div>
                    </div>

                    <!-- Scale Info -->
                    <div style="text-align: center; font-size: 0.9rem; opacity: 0.7;">
                        Scale: <strong id="preview-scale-info">100%</strong> • 
                        Font Size Demo: <span id="preview-font-size" style="font-size: 0.9rem;">Normal</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const contentInput = document.getElementById('content');
    const previewTitle = document.getElementById('preview-title');
    const previewContent = document.getElementById('preview-content');
    const previewCharCount = document.getElementById('preview-char-count');
    const previewFontSize = document.getElementById('preview-font-size');

    // Get current CSS scale multiplier
    function getCurrentScale() {
        const scale = getComputedStyle(document.documentElement).getPropertyValue('--scale-multiplier') || '1';
        return parseFloat(scale);
    }

    // Update preview
    function updatePreview() {
        // Update title
        previewTitle.textContent = titleInput.value || 'Announcement Title';

        // Update content
        const content = contentInput.value || 'Enter your announcement content here...';
        previewContent.textContent = content;

        // Update character count
        const charCount = contentInput.value.length;
        previewCharCount.textContent = `${charCount}/250 characters`;

        // Update font size info with new range
        const scale = getCurrentScale();
        document.getElementById('preview-scale-info').textContent = `${Math.round(scale * 100)}%`;
        
        // Show warning if near limit
        if (charCount > 225) {
            previewCharCount.style.color = '#ff6b6b';
        } else if (charCount > 200) {
            previewCharCount.style.color = '#ffa500';
        } else {
            previewCharCount.style.color = '#e0e0e0';
        }
    }

    // Event listeners
    titleInput.addEventListener('input', updatePreview);
    contentInput.addEventListener('input', updatePreview);

    // Watch for CSS variable changes
    setInterval(updatePreview, 500);

    // Initial update
    updatePreview();
});
</script>

<style>
    .form-control {
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }

    .form-control:focus {
        border-color: #0066cc;
        box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.25);
    }

    .badge {
        font-size: 0.85rem;
        padding: 0.35em 0.65em;
    }

    .card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 1rem;
    }

    .text-danger {
        color: #dc3545;
    }

    /* Ensure announcement content doesn't exceed 250 chars */
    textarea[data-char-counter] {
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
    }
</style>
@endsection
