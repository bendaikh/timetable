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
                            <h5 class="mb-0">Media Preview</h5>
                        </div>
                        <div class="card-body text-center">
                            @if($mediaSchedule->media->isImage())
                                <img src="{{ $mediaSchedule->media->file_url }}" alt="{{ $mediaSchedule->media->title }}" 
                                     class="img-fluid" style="max-height: 400px;">
                            @else
                                <video controls class="w-100" style="max-height: 400px;">
                                    <source src="{{ $mediaSchedule->media->file_url }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
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
                                    <td><strong>Media:</strong></td>
                                    <td>{{ $mediaSchedule->media->title }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $mediaSchedule->media->type === 'image' ? 'info' : 'warning' }}">
                                            {{ ucfirst($mediaSchedule->media->type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Schedule Type:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $mediaSchedule->schedule_type === 'countdown' ? 'info' : ($mediaSchedule->schedule_type === 'time_range' ? 'warning' : 'success') }}">
                                            {{ ucwords(str_replace('_', ' ', $mediaSchedule->schedule_type)) }}
                                        </span>
                                    </td>
                                </tr>
                                @if($mediaSchedule->schedule_type === 'prayer_before' || $mediaSchedule->schedule_type === 'prayer_after')
                                    <tr>
                                        <td><strong>Prayer:</strong></td>
                                        <td>
                                            <span class="badge bg-primary">{{ ucfirst($mediaSchedule->prayer_name) }}</span>
                                        </td>
                                    </tr>
                                @elseif($mediaSchedule->schedule_type === 'time_range')
                                    <tr>
                                        <td><strong>Time Range:</strong></td>
                                        <td>
                                            {{ $mediaSchedule->start_time->format('H:i') }} - {{ $mediaSchedule->end_time->format('H:i') }}
                                        </td>
                                    </tr>
                                @elseif($mediaSchedule->schedule_type === 'countdown')
                                    <tr>
                                        <td><strong>Countdown:</strong></td>
                                        <td>
                                            {{ $mediaSchedule->countdown_duration }}s before {{ ucfirst($mediaSchedule->prayer_name) }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td><strong>Days:</strong></td>
                                    <td>
                                        @if($mediaSchedule->days_of_week)
                                            @php
                                                $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                                $selectedDays = collect($mediaSchedule->days_of_week)->map(function($day) use ($dayNames) {
                                                    return $dayNames[$day - 1] ?? $day;
                                                })->join(', ');
                                            @endphp
                                            {{ $selectedDays }}
                                        @else
                                            <span class="text-muted">All Days</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Priority:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $mediaSchedule->priority > 50 ? 'danger' : ($mediaSchedule->priority > 20 ? 'warning' : 'secondary') }}">
                                            {{ $mediaSchedule->priority }}
                                        </span>
                                    </td>
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
                                <a href="{{ route('admin.media.show', $mediaSchedule->media) }}" class="btn btn-info">
                                    <i class="bi bi-images"></i> View Media
                                </a>
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
                                        @if($mediaSchedule->schedule_type === 'prayer_before')
                                            <li><i class="bi bi-check-circle text-success me-2"></i>Media will be displayed before {{ ucfirst($mediaSchedule->prayer_name) }} prayer</li>
                                        @elseif($mediaSchedule->schedule_type === 'prayer_after')
                                            <li><i class="bi bi-check-circle text-success me-2"></i>Media will be displayed after {{ ucfirst($mediaSchedule->prayer_name) }} prayer</li>
                                        @elseif($mediaSchedule->schedule_type === 'time_range')
                                            <li><i class="bi bi-check-circle text-success me-2"></i>Media will be displayed between {{ $mediaSchedule->start_time->format('H:i') }} and {{ $mediaSchedule->end_time->format('H:i') }}</li>
                                        @elseif($mediaSchedule->schedule_type === 'countdown')
                                            <li><i class="bi bi-check-circle text-success me-2"></i>Countdown timer will be shown {{ $mediaSchedule->countdown_duration }} seconds before {{ ucfirst($mediaSchedule->prayer_name) }} prayer</li>
                                        @endif
                                        
                                        @if($mediaSchedule->days_of_week)
                                            <li><i class="bi bi-check-circle text-success me-2"></i>Only active on selected days</li>
                                        @else
                                            <li><i class="bi bi-check-circle text-success me-2"></i>Active every day</li>
                                        @endif
                                        
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Priority level: {{ $mediaSchedule->priority }} (higher overrides lower)</li>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Media duration: {{ $mediaSchedule->media->display_duration }} seconds</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Next Occurrence:</h6>
                                    <div class="alert alert-info">
                                        <i class="bi bi-clock me-2"></i>
                                        @if($mediaSchedule->is_active)
                                            @if($mediaSchedule->schedule_type === 'time_range')
                                                <strong>Today:</strong> {{ $mediaSchedule->start_time->format('H:i') }} - {{ $mediaSchedule->end_time->format('H:i') }}
                                            @else
                                                <strong>Next {{ ucfirst($mediaSchedule->prayer_name) }} prayer</strong>
                                            @endif
                                        @else
                                            <em>Schedule is currently inactive</em>
                                        @endif
                                    </div>
                                    
                                    <h6>Media Details:</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>Title:</strong> {{ $mediaSchedule->media->title }}</li>
                                        <li><strong>Type:</strong> {{ ucfirst($mediaSchedule->media->type) }}</li>
                                        <li><strong>Duration:</strong> {{ $mediaSchedule->media->display_duration }}s</li>
                                        <li><strong>Status:</strong> 
                                            <span class="badge bg-{{ $mediaSchedule->media->is_active ? 'success' : 'secondary' }}">
                                                {{ $mediaSchedule->media->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </li>
                                    </ul>
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
