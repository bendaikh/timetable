@extends('layouts.admin')

@section('title', 'Media Schedules')

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
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Media</th>
                                    <th>Schedule Type</th>
                                    <th>Prayer/Time</th>
                                    <th>Days</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schedules as $schedule)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($schedule->media->isImage())
                                                    <img src="{{ $schedule->media->file_url }}" alt="{{ $schedule->media->title }}" 
                                                         class="img-thumbnail me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="bi bi-play-circle"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong>{{ $schedule->media->title }}</strong>
                                                    <br><small class="text-muted">{{ ucfirst($schedule->media->type) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $schedule->schedule_type === 'countdown' ? 'info' : ($schedule->schedule_type === 'time_range' ? 'warning' : 'success') }}">
                                                {{ $schedule->getScheduleTypeLabel() }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($schedule->schedule_type === 'prayer_before' || $schedule->schedule_type === 'prayer_after')
                                                <span class="badge bg-primary">{{ $schedule->getPrayerNameLabel() }}</span>
                                            @elseif($schedule->schedule_type === 'time_range')
                                                <small>
                                                    {{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}
                                                </small>
                                            @elseif($schedule->schedule_type === 'countdown')
                                                <small>{{ $schedule->countdown_duration }}s before {{ $schedule->getPrayerNameLabel() }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($schedule->days_of_week)
                                                @php
                                                    $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                                                    $selectedDays = collect($schedule->days_of_week)->map(function($day) use ($dayNames) {
                                                        return $dayNames[$day - 1] ?? $day;
                                                    })->join(', ');
                                                @endphp
                                                <small>{{ $selectedDays }}</small>
                                            @else
                                                <span class="text-muted">All Days</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $schedule->priority > 50 ? 'danger' : ($schedule->priority > 20 ? 'warning' : 'secondary') }}">
                                                {{ $schedule->priority }}
                                            </span>
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
                                        <td colspan="7" class="text-center text-muted py-4">
                                            No schedules found. <a href="{{ route('admin.media-schedules.create') }}">Create your first schedule</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $schedules->links() }}
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
