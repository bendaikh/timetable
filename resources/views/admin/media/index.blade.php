@extends('layouts.admin')

@section('title', 'Media Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Media Management</h1>
                <a href="{{ route('admin.media.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add New Media
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Preview</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Duration</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Schedules</th>
                                    <th width="180">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($media as $item)
                                    <tr>
                                        <td>
                                            @if($item->isImage())
                                                <img src="{{ $item->file_url }}" alt="{{ $item->title }}" 
                                                     class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px;">
                                                    <i class="bi bi-play-circle fs-4"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $item->title }}</strong>
                                            @if($item->description)
                                                <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $item->type === 'image' ? 'info' : 'warning' }}">
                                                {{ ucfirst($item->type) }}
                                            </span>
                                        </td>
                                        <td>{{ $item->display_duration }}s</td>
                                        <td>
                                            <span class="badge bg-{{ $item->priority > 50 ? 'danger' : ($item->priority > 20 ? 'warning' : 'secondary') }}">
                                                {{ $item->priority }}
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-{{ $item->is_active ? 'success' : 'secondary' }}"
                                                    onclick="toggleStatus({{ $item->id }})">
                                                {{ $item->is_active ? 'Active' : 'Inactive' }}
                                            </button>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $item->schedules->count() }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.media.preview', $item) }}" class="btn btn-sm btn-outline-success" 
                                                   title="Preview Media" target="_blank">
                                                    <i class="bi bi-play-circle"></i>
                                                </a>
                                                <a href="{{ route('admin.media.show', $item) }}" class="btn btn-sm btn-outline-info"
                                                   title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.media.edit', $item) }}" class="btn btn-sm btn-outline-warning"
                                                   title="Edit Media">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteMedia({{ $item->id }})" title="Delete Media">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            No media files found. <a href="{{ route('admin.media.create') }}">Add your first media file</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $media->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this media file? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleStatus(mediaId) {
    fetch(`/admin/media/${mediaId}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the status.');
    });
}

function deleteMedia(mediaId) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    form.action = `/admin/media/${mediaId}`;
    modal.show();
}
</script>
@endsection
