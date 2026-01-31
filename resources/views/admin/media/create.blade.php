@extends('layouts.admin')

@section('title', 'Add New Media')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Add New Media</h1>
                <a href="{{ route('admin.media.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Media
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title') }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="type" class="form-label">Media Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="image" {{ old('type') === 'image' ? 'selected' : '' }}>Image</option>
                                        <option value="video" {{ old('type') === 'video' ? 'selected' : '' }}>Video</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="file" class="form-label">File <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                           id="file" name="file" accept=".jpg,.jpeg,.png,.gif,.mp4,.avi,.mov,image/*,video/*" required>
                                    <?php
                                        $maxUpload = min(
                                            (int)str_replace('M', '', ini_get('upload_max_filesize')),
                                            (int)str_replace('M', '', ini_get('post_max_size'))
                                        );
                                    ?>
                                    <div class="form-text">
                                        Supported formats: JPG, PNG, GIF, MP4, AVI, MOV. Max size: {{ $maxUpload }}MB
                                    </div>
                                    @if($errors->has('file'))
                                        <div class="alert alert-danger mt-2 mb-0">
                                            <strong>File Upload Error:</strong> {{ $errors->first('file') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="4">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                        <div class="form-text">Only active media will be displayed</div>
                                    </div>
                                </div>

                                <!-- File Preview -->
                                <div class="mb-3" id="file-preview" style="display: none;">
                                    <label class="form-label">Preview</label>
                                    <div class="border rounded p-3 text-center">
                                        <div id="preview-content"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.media.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-save"></i> Save Media
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
// Form submission validation
document.querySelector('form').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('file');
    const titleInput = document.getElementById('title');
    const typeSelect = document.getElementById('type');
    
    // Check if title is empty
    if (!titleInput.value.trim()) {
        e.preventDefault();
        alert('Please enter a title');
        titleInput.focus();
        return false;
    }
    
    // Check if type is selected
    if (!typeSelect.value) {
        e.preventDefault();
        alert('Please select a media type');
        typeSelect.focus();
        return false;
    }
    
    // Check if file is selected
    if (!fileInput.files || fileInput.files.length === 0) {
        e.preventDefault();
        alert('Please select a file to upload');
        fileInput.focus();
        return false;
    }
    
    const file = fileInput.files[0];
    const maxSizeMB = 2; // PHP limit
    const maxSizeBytes = maxSizeMB * 1024 * 1024;
    
    // Check file size
    if (file.size > maxSizeBytes) {
        e.preventDefault();
        alert(`File size exceeds maximum limit of ${maxSizeMB}MB. Selected file is ${(file.size / 1024 / 1024).toFixed(2)}MB`);
        return false;
    }
    
    // Check file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/x-msvideo', 'video/quicktime'];
    if (!allowedTypes.includes(file.type)) {
        e.preventDefault();
        alert(`File type not allowed. Selected type: ${file.type}\nAllowed types: JPG, PNG, GIF, MP4, AVI, MOV`);
        return false;
    }
});

document.getElementById('file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const type = document.getElementById('type').value;
    const preview = document.getElementById('file-preview');
    const previewContent = document.getElementById('preview-content');
    
    if (file) {
        // Show file info
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        previewContent.innerHTML = `<p class="text-muted mb-2">File: ${file.name}<br>Size: ${sizeMB}MB</p>`;
        
        preview.style.display = 'block';
        
        if (type === 'image' && file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.maxWidth = '100%';
            img.style.maxHeight = '200px';
            img.onload = function() {
                previewContent.innerHTML = '';
                previewContent.appendChild(img);
            };
        } else if (type === 'video' && file.type.startsWith('video/')) {
            const video = document.createElement('video');
            video.src = URL.createObjectURL(file);
            video.controls = true;
            video.style.maxWidth = '100%';
            video.style.maxHeight = '200px';
            previewContent.innerHTML = '';
            previewContent.appendChild(video);
        } else {
            previewContent.innerHTML = `<p class="text-muted">File: ${file.name}<br>Size: ${sizeMB}MB<br><small>(Preview not available for this file type)</small></p>`;
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
