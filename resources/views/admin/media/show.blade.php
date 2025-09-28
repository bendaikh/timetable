@extends('layouts.admin')

@section('title', 'View Media')
@section('page-icon', '<i class="bi bi-eye me-2"></i>')
@section('page-title', 'View Media')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">View Media</h1>
                <div>
                    <a href="{{ route('admin.media.edit', $medium) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('admin.media.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Media
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header card-header-custom">
                            <h5 class="mb-0">Media Preview</h5>
                        </div>
                        <div class="card-body text-center">
                            @if($medium->isImage())
                                <img src="{{ $medium->file_url }}" alt="{{ $medium->title }}" 
                                     class="img-fluid" style="max-height: 500px;">
                            @else
                                <video controls class="w-100" style="max-height: 500px;">
                                    <source src="{{ $medium->file_url }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header card-header-custom">
                            <h5 class="mb-0">Media Details</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Title:</strong></td>
                                    <td>{{ $medium->title }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $medium->type === 'image' ? 'info' : 'warning' }}">
                                            {{ ucfirst($medium->type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Duration:</strong></td>
                                    <td>{{ $medium->display_duration }} seconds</td>
                                </tr>
                                <tr>
                                    <td><strong>Priority:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $medium->priority > 50 ? 'danger' : ($medium->priority > 20 ? 'warning' : 'secondary') }}">
                                            {{ $medium->priority }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $medium->is_active ? 'success' : 'secondary' }}">
                                            {{ $medium->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>File Path:</strong></td>
                                    <td><small class="text-muted">{{ $medium->file_path }}</small></td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $medium->created_at->format('M j, Y g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Updated:</strong></td>
                                    <td>{{ $medium->updated_at->format('M j, Y g:i A') }}</td>
                                </tr>
                            </table>

                            @if($medium->description)
                                <div class="mt-3">
                                    <h6>Description:</h6>
                                    <p class="text-muted">{{ $medium->description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header card-header-custom">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.media.preview', $medium) }}" class="btn btn-success" target="_blank">
                                    <i class="bi bi-play-circle"></i> Preview Media
                                </a>
                                <button type="button" class="btn btn-{{ $medium->is_active ? 'warning' : 'success' }}"
                                        onclick="toggleStatus({{ $medium->id }})">
                                    <i class="bi bi-{{ $medium->is_active ? 'pause' : 'play' }}"></i>
                                    {{ $medium->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <a href="{{ route('admin.media-schedules.create', ['media_id' => $medium->id]) }}" class="btn btn-info">
                                    <i class="bi bi-calendar-plus"></i> Schedule This Media
                                </a>
                                <button type="button" class="btn btn-danger" onclick="deleteMedia({{ $medium->id }})">
                                    <i class="bi bi-trash"></i> Delete Media
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Media Schedules -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header card-header-custom">
                            <h5 class="mb-0">
                                <i class="bi bi-calendar-event me-2"></i>
                                Media Schedules ({{ $medium->schedules->count() }})
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($medium->schedules->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Schedule Type</th>
                                                <th>Details</th>
                                                <th>Priority</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($medium->schedules as $schedule)
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-primary">
                                                            {{ ucwords(str_replace('_', ' ', $schedule->schedule_type)) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($schedule->schedule_type === 'prayer_before' || $schedule->schedule_type === 'prayer_after')
                                                            {{ ucfirst($schedule->prayer_name) }} Prayer
                                                        @elseif($schedule->schedule_type === 'time_range')
                                                            {{ $schedule->start_time }} - {{ $schedule->end_time }}
                                                        @elseif($schedule->schedule_type === 'countdown')
                                                            {{ $schedule->countdown_duration }}s before prayer
                                                        @endif
                                                        @if($schedule->days_of_week)
                                                            <br><small class="text-muted">
                                                                Days: {{ implode(', ', array_map(function($day) {
                                                                    $days = ['', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                                                                    return $days[$day] ?? $day;
                                                                }, $schedule->days_of_week)) }}
                                                            </small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $schedule->priority > 50 ? 'danger' : ($schedule->priority > 20 ? 'warning' : 'secondary') }}">
                                                            {{ $schedule->priority }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $schedule->is_active ? 'success' : 'secondary' }}">
                                                            {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('admin.media-schedules.edit', $schedule->id) }}" class="btn btn-sm btn-outline-warning">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="deleteSchedule({{ $schedule->id }})">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-calendar-event display-4 text-muted"></i>
                                    <p class="text-muted mt-3">No schedules for this media yet.</p>
                                    <a href="{{ route('admin.media-schedules.create', ['media_id' => $medium->id]) }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>
                                        Create Schedule
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
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
                Are you sure you want to delete this media file? This action cannot be undone and will also delete all associated schedules.
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

function deleteSchedule(scheduleId) {
    if (confirm('Are you sure you want to delete this schedule?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/media-schedules/${scheduleId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
