@extends('layouts.admin')

@section('title', 'Media Schedules')

@section('styles')
<style>
    /* Fix pagination size and layout issues */
    .pagination {
        display: flex;
        flex-wrap: wrap;
        list-style: none;
        padding: 0;
        margin: 1rem 0;
        gap: 0.25rem;
        justify-content: center;
    }

    .pagination .page-item {
        display: inline-block;
    }

    .pagination .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1;
        text-decoration: none;
        background-color: #fff;
        border: 1px solid #dee2e6;
        color: #0d6efd;
        min-width: 2.5rem;
        height: 2.5rem;
        transition: all 0.15s ease-in-out;
    }

    .pagination .page-link:hover {
        color: #0b5ed7;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }

    .pagination .page-item.active .page-link {
        z-index: 3;
        color: #fff;
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }

    /* Ensure table doesn't overflow */
    .table-responsive {
        max-width: 100%;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    /* Ensure buttons don't overflow */
    .btn-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }

    .btn-sm {
        padding: 0.375rem 0.625rem;
        font-size: 0.825rem;
        white-space: nowrap;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Media Schedules</h1>
                <a href="{{ route('admin.media-schedules.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add New Schedule
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
                    <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
                        <table class="table table-striped" style="min-width: 100%; margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th>Media Items</th>
                                    <th>Schedule Type</th>
                                    <th>Prayer/Time</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schedules as $schedule)
                                    <tr>
                                        <td>
                                            @if($schedule->mediaItems->count() > 0)
                                                <div class="d-flex flex-column">
                                                    @foreach($schedule->mediaItems->take(2) as $media)
                                                        <div class="d-flex align-items-center mb-1">
                                                            @if($media->isImage())
                                                                <img src="{{ $media->file_url }}" alt="{{ $media->title }}" 
                                                                     class="img-thumbnail me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                                            @else
                                                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center me-2" 
                                                                     style="width: 30px; height: 30px; font-size: 10px;">
                                                                    <i class="bi bi-play-circle"></i>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <small><strong>{{ $media->title }}</strong></small>
                                                                <br><small class="text-muted">Priority: {{ $media->pivot->priority }}, Duration: {{ \App\Support\MediaScheduleDuration::secondsFromStored($media->pivot->duration) }}s</small>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    @if($schedule->mediaItems->count() > 2)
                                                        <small class="text-muted">+ {{ $schedule->mediaItems->count() - 2 }} more</small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">No media</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $schedule->schedule_type === 'full_time_poster' ? 'primary' : 'success' }}">
                                                {{ $schedule->getScheduleTypeLabel() }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($schedule->schedule_type === 'minutes_before_prayer')
                                                <small>{{ $schedule->minutes_before_prayer }} min before {{ $schedule->getPrayerNameLabel() }}</small>
                                            @elseif($schedule->schedule_type === 'minutes_after_prayer')
                                                <small>{{ $schedule->minutes_after_prayer }} min after {{ $schedule->getPrayerNameLabel() }}</small>
                                            @elseif($schedule->schedule_type === 'full_time_poster')
                                                <small class="text-muted">All day</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($schedule->days_of_week)
                                                <small>{{ \App\Support\ScheduleDaysOfWeek::formatLabels($schedule->days_of_week) }}</small>
                                            @else
                                                <span class="text-muted">All Days</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-{{ $schedule->is_active ? 'success' : 'secondary' }}"
                                                    onclick="toggleStatus({{ $schedule->id }})">
                                                {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                                            </button>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.media-schedules.show', $schedule->id) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
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
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No schedules found. <a href="{{ route('admin.media-schedules.create') }}">Create your first schedule</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <nav class="mt-4" role="navigation" aria-label="pagination">
                        {{ $schedules->links('pagination::bootstrap-4') }}
                    </nav>
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
                Are you sure you want to delete this schedule? This action cannot be undone.
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
function toggleStatus(scheduleId) {
    fetch(`/admin/media-schedules/${scheduleId}/toggle-status`, {
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

function deleteSchedule(scheduleId) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    form.action = `/admin/media-schedules/${scheduleId}`;
    modal.show();
}
</script>
@endsection
