@extends('layouts.admin')

@section('title', 'View Media Schedule')
@section('page-icon', '<i class="bi bi-eye me-2"></i>')
@section('page-title', 'View Media Schedule')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">View Media Schedule</h1>
                <div>
                    <a href="{{ route('admin.media-schedules.edit', $mediaSchedule->id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('admin.media-schedules.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Schedules
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header card-header-custom">
                            <h5 class="mb-0">Media Items ({{ $mediaSchedule->mediaItems->count() }})</h5>
                        </div>
                        <div class="card-body">
                            @if($mediaSchedule->mediaItems->count() > 0)
                                <div class="row">
                                    @foreach($mediaSchedule->mediaItems as $media)
                                        <div class="col-md-6 mb-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="mb-0">{{ $media->title }}</h6>
                                                        <div>
                                                            <span class="badge bg-primary">Priority: {{ $media->pivot->priority }}</span>
                                                            <span class="badge bg-info">{{ \App\Support\MediaScheduleDuration::secondsFromStored($media->pivot->duration) }}s</span>
                                                        </div>
                                                    </div>
                                                    <div class="text-center">
                                                        @if($media->isImage())
                                                            <img src="{{ $media->file_url }}" alt="{{ $media->title }}" 
                                                                 class="img-fluid" style="max-height: 200px; object-fit: contain;">
                                                        @else
                                                            <video controls class="w-100" style="max-height: 200px;">
                                                                <source src="{{ $media->file_url }}" type="video/mp4">
                                                                Your browser does not support the video tag.
                                                            </video>
                                                        @endif
                                                    </div>
                                                    <div class="mt-2">
                                                        <small class="text-muted">{{ ucfirst($media->type) }}</small>
                                                        @if($media->pivot->start_date && $media->pivot->start_time)
                                                            <div><small class="text-muted">Starts: {{ \Carbon\Carbon::parse($media->pivot->start_date . ' ' . $media->pivot->start_time)->format('M j, Y g:i A') }}</small></div>
                                                        @endif
                                                        @if($media->pivot->expiry_date && $media->pivot->expiry_time)
                                                            <div><small class="text-muted">Ends: {{ \Carbon\Carbon::parse($media->pivot->expiry_date . ' ' . $media->pivot->expiry_time)->format('M j, Y g:i A') }}</small></div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted text-center">No media items in this schedule</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header card-header-custom">
                            <h5 class="mb-0">Schedule Details</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Schedule Type:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $mediaSchedule->schedule_type === 'full_time_poster' ? 'primary' : 'success' }}">
                                            {{ $mediaSchedule->getScheduleTypeLabel() }}
                                        </span>
                                    </td>
                                </tr>
                                @if($mediaSchedule->schedule_type !== 'full_time_poster')
                                    <tr>
                                        <td><strong>Prayer:</strong></td>
                                        <td>
                                            <span class="badge bg-primary">{{ $mediaSchedule->getPrayerNameLabel() }}</span>
                                        </td>
                                    </tr>
                                    @if($mediaSchedule->schedule_type === 'minutes_before_prayer')
                                        <tr>
                                            <td><strong>Minutes Before:</strong></td>
                                            <td>{{ $mediaSchedule->minutes_before_prayer }} minutes</td>
                                        </tr>
                                    @elseif($mediaSchedule->schedule_type === 'minutes_after_prayer')
                                        <tr>
                                            <td><strong>Minutes After:</strong></td>
                                            <td>{{ $mediaSchedule->minutes_after_prayer }} minutes</td>
                                        </tr>
                                    @endif
                                @endif
                                <tr>
                                    <td><strong>Days:</strong></td>
                                    <td>
                                        @if($mediaSchedule->days_of_week)
                                            {{ \App\Support\ScheduleDaysOfWeek::formatLabels($mediaSchedule->days_of_week) }}
                                        @else
                                            <span class="text-muted">All Days</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Media Count:</strong></td>
                                    <td>{{ $mediaSchedule->mediaItems->count() }} item(s)</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Duration:</strong></td>
                                    <td>{{ $mediaSchedule->mediaItems->sum(fn ($media) => \App\Support\MediaScheduleDuration::secondsFromStored($media->pivot->duration)) }} seconds</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $mediaSchedule->is_active ? 'success' : 'secondary' }}">
                                            {{ $mediaSchedule->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $mediaSchedule->created_at->format('M j, Y g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Updated:</strong></td>
                                    <td>{{ $mediaSchedule->updated_at->format('M j, Y g:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header card-header-custom">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-{{ $mediaSchedule->is_active ? 'warning' : 'success' }}"
                                        onclick="toggleStatus({{ $mediaSchedule->id }})">
                                    <i class="bi bi-{{ $mediaSchedule->is_active ? 'pause' : 'play' }}"></i>
                                    {{ $mediaSchedule->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button type="button" class="btn btn-danger" onclick="deleteSchedule({{ $mediaSchedule->id }})">
                                    <i class="bi bi-trash"></i> Delete Schedule
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule Information -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header card-header-custom">
                            <h5 class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Schedule Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>How This Schedule Works:</h6>
                                    <ul class="list-unstyled">
                                        @if($mediaSchedule->schedule_type === 'minutes_before_prayer')
                                            <li><i class="bi bi-check-circle text-success me-2"></i>Starts {{ $mediaSchedule->minutes_before_prayer }} minutes before <strong>Jamaat</strong> (congregation time), not Adhan</li>
                                            <li><i class="bi bi-info-circle text-info me-2"></i>Runs until Jamaat (countdown windows briefly override near iqamah)</li>
                                        @elseif($mediaSchedule->schedule_type === 'minutes_after_prayer')
                                            <li><i class="bi bi-check-circle text-success me-2"></i>Starts {{ $mediaSchedule->minutes_after_prayer }} minutes after <strong>Jamaat</strong></li>
                                            <li><i class="bi bi-info-circle text-info me-2"></i>Stays active for {{ \App\Support\PrayerJamaatTime::AFTER_POSTER_WINDOW_MINUTES }} minutes from that start</li>
                                        @elseif($mediaSchedule->schedule_type === 'full_time_poster')
                                            <li><i class="bi bi-check-circle text-success me-2"></i>Media will cycle continuously throughout the day</li>
                                        @endif
                                        <li><i class="bi bi-info-circle text-info me-2"></i>Optional start/end date and time can limit each media item</li>
                                        
                                        @if($mediaSchedule->days_of_week)
                                            <li><i class="bi bi-check-circle text-success me-2"></i>Only active on selected days</li>
                                        @else
                                            <li><i class="bi bi-check-circle text-success me-2"></i>Active every day</li>
                                        @endif
                                        
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Media display in priority order (1, 2, 3...)</li>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Each media has its own duration</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Display Sequence:</h6>
                                    <div class="alert alert-info">
                                        @if($mediaSchedule->mediaItems->count() > 0)
                                            <p class="mb-2"><strong>Media will display in this order:</strong></p>
                                            <ol class="mb-0">
                                                @foreach($mediaSchedule->mediaItems->sortBy('pivot.priority') as $media)
                                                    <li>{{ $media->title }} ({{ \App\Support\MediaScheduleDuration::secondsFromStored($media->pivot->duration) }}s)</li>
                                                @endforeach
                                            </ol>
                                            <hr>
                                            <small class="text-muted">
                                                Total cycle duration: {{ $mediaSchedule->mediaItems->sum(fn ($media) => \App\Support\MediaScheduleDuration::secondsFromStored($media->pivot->duration)) }} seconds
                                                @if($mediaSchedule->schedule_type === 'full_time_poster')
                                                    <br>This cycle repeats continuously
                                                @endif
                                            </small>
                                        @else
                                            <em>No media items in this schedule</em>
                                        @endif
                                    </div>
                                </div>
                            </div>
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
                Are you sure you want to delete this media schedule? This action cannot be undone.
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
