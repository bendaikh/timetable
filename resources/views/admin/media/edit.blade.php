@extends('layouts.admin')

@section('title', 'Edit Media')
@section('page-icon', '<i class="bi bi-pencil me-2"></i>')
@section('page-title', 'Edit Media')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Edit Media</h1>
                <a href="{{ route('admin.media.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Media
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.media.update', $medium) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $medium->title) }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="type" class="form-label">Media Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="image" {{ old('type', $medium->type) === 'image' ? 'selected' : '' }}>Image</option>
                                        <option value="video" {{ old('type', $medium->type) === 'video' ? 'selected' : '' }}>Video</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="file" class="form-label">File</label>
                                    <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                           id="file" name="file" accept="image/*,video/*">
                                    <div class="form-text">
                                        Leave empty to keep current file. Supported formats: JPG, PNG, GIF, MP4, AVI, MOV. Max size: 100MB
                                    </div>
                                    @error('file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="display_duration" class="form-label">Display Duration (seconds) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('display_duration') is-invalid @enderror" 
                                           id="display_duration" name="display_duration" value="{{ old('display_duration', $medium->display_duration) }}" 
                                           min="5" max="300" required>
                                    <div class="form-text">How long this media should be displayed (5-300 seconds)</div>
                                    @error('display_duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('priority') is-invalid @enderror" 
                                           id="priority" name="priority" value="{{ old('priority', $medium->priority) }}" 
                                           min="0" max="100" required>
                                    <div class="form-text">Higher priority media overrides lower priority (0-100)</div>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="4">{{ old('description', $medium->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               value="1" {{ old('is_active', $medium->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                        <div class="form-text">Only active media will be displayed</div>
                                    </div>
                                </div>

                                <!-- Current File Preview -->
                                <div class="mb-3">
                                    <label class="form-label">Current File</label>
                                    <div class="border rounded p-3 text-center">
                                        @if($medium->isImage())
                                            <img src="{{ $medium->file_url }}" alt="{{ $medium->title }}" 
                                                 style="max-width: 100%; max-height: 200px;">
                                        @else
                                            <video controls style="max-width: 100%; max-height: 200px;">
                                                <source src="{{ $medium->file_url }}" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        @endif
                                        <div class="mt-2">
                                            <small class="text-muted">{{ $medium->file_path }}</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- New File Preview -->
                                <div class="mb-3" id="file-preview" style="display: none;">
                                    <label class="form-label">New File Preview</label>
                                    <div class="border rounded p-3 text-center">
                                        <div id="preview-content"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.media.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Media
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const type = document.getElementById('type').value;
    const preview = document.getElementById('file-preview');
    const previewContent = document.getElementById('preview-content');
    
    if (file) {
        preview.style.display = 'block';
        
        if (type === 'image' && file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.maxWidth = '100%';
            img.style.maxHeight = '200px';
            previewContent.innerHTML = '';
            previewContent.appendChild(img);
        } else if (type === 'video' && file.type.startsWith('video/')) {
            const video = document.createElement('video');
            video.src = URL.createObjectURL(file);
            video.controls = true;
            video.style.maxWidth = '100%';
            video.style.maxHeight = '200px';
            previewContent.innerHTML = '';
            previewContent.appendChild(video);
        } else {
            previewContent.innerHTML = '<p class="text-muted">Preview not available for this file type</p>';
        }
    } else {
        preview.style.display = 'none';
    }
});

document.getElementById('type').addEventListener('change', function() {
    const fileInput = document.getElementById('file');
    if (fileInput.files.length > 0) {
        // Trigger file preview update
        fileInput.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
