@extends('layouts.admin')

@section('title', 'Edit Media Schedule')
@section('page-icon', '<i class="bi bi-pencil me-2"></i>')
@section('page-title', 'Edit Media Schedule')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Edit Media Schedule</h1>
                <a href="{{ route('admin.media-schedules.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Schedules
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.media-schedules.update', $mediaSchedule->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="media_id" class="form-label">Media <span class="text-danger">*</span></label>
                                    <select class="form-select @error('media_id') is-invalid @enderror" id="media_id" name="media_id" required>
                                        <option value="">Select Media</option>
                                        @foreach($media as $item)
                                            <option value="{{ $item->id }}" {{ old('media_id', $mediaSchedule->media_id) == $item->id ? 'selected' : '' }}>
                                                {{ $item->title }} ({{ ucfirst($item->type) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('media_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="schedule_type" class="form-label">Schedule Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('schedule_type') is-invalid @enderror" id="schedule_type" name="schedule_type" required>
                                        <option value="">Select Type</option>
                                        <option value="prayer_before" {{ old('schedule_type', $mediaSchedule->schedule_type) === 'prayer_before' ? 'selected' : '' }}>Before Prayer</option>
                                        <option value="prayer_after" {{ old('schedule_type', $mediaSchedule->schedule_type) === 'prayer_after' ? 'selected' : '' }}>After Prayer</option>
                                        <option value="time_range" {{ old('schedule_type', $mediaSchedule->schedule_type) === 'time_range' ? 'selected' : '' }}>Time Range</option>
                                        <option value="countdown" {{ old('schedule_type', $mediaSchedule->schedule_type) === 'countdown' ? 'selected' : '' }}>Countdown Timer</option>
                                    </select>
                                    @error('schedule_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3" id="prayer_name_field" style="display: none;">
                                    <label for="prayer_name" class="form-label">Prayer <span class="text-danger">*</span></label>
                                    <select class="form-select @error('prayer_name') is-invalid @enderror" id="prayer_name" name="prayer_name">
                                        <option value="">Select Prayer</option>
                                        <option value="fajr" {{ old('prayer_name', $mediaSchedule->prayer_name) === 'fajr' ? 'selected' : '' }}>Fajr</option>
                                        <option value="zohar" {{ old('prayer_name', $mediaSchedule->prayer_name) === 'zohar' ? 'selected' : '' }}>Zohar</option>
                                        <option value="asr" {{ old('prayer_name', $mediaSchedule->prayer_name) === 'asr' ? 'selected' : '' }}>Asr</option>
                                        <option value="maghrib" {{ old('prayer_name', $mediaSchedule->prayer_name) === 'maghrib' ? 'selected' : '' }}>Maghrib</option>
                                        <option value="isha" {{ old('prayer_name', $mediaSchedule->prayer_name) === 'isha' ? 'selected' : '' }}>Isha</option>
                                    </select>
                                    @error('prayer_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3" id="exact_start_time_field" style="display: none;">
                                    <label for="exact_start_time" class="form-label">Exact Start Time</label>
                                    <input type="time" class="form-control @error('exact_start_time') is-invalid @enderror" 
                                           id="exact_start_time" name="exact_start_time" 
                                           value="{{ old('exact_start_time', $mediaSchedule->exact_start_time ? $mediaSchedule->exact_start_time->format('H:i') : '') }}">
                                    <div class="form-text">When to start showing for Before/After Prayer.</div>
                                    @error('exact_start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3" id="time_range_fields" style="display: none;">
                                    <div class="row">
                                        <div class="col-6">
                                            <label for="start_time" class="form-label">Start Time</label>
                                            <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                                   id="start_time" name="start_time" value="{{ old('start_time', $mediaSchedule->start_time ? $mediaSchedule->start_time->format('H:i') : '') }}">
                                            @error('start_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-6">
                                            <label for="end_time" class="form-label">End Time</label>
                                            <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                                   id="end_time" name="end_time" value="{{ old('end_time', $mediaSchedule->end_time ? $mediaSchedule->end_time->format('H:i') : '') }}">
                                            @error('end_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3" id="countdown_field" style="display: none;">
                                    <label for="countdown_duration" class="form-label">Countdown Duration (seconds)</label>
                                    <input type="number" class="form-control @error('countdown_duration') is-invalid @enderror" 
                                           id="countdown_duration" name="countdown_duration" 
                                           value="{{ old('countdown_duration', $mediaSchedule->countdown_duration) }}" 
                                           min="10" max="300">
                                    <div class="form-text">How long before prayer to show countdown (10-300 seconds)</div>
                                    @error('countdown_duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="days_of_week" class="form-label">Days of Week</label>
                                    <div class="form-check-group">
                                        @php
                                            $selectedDays = old('days_of_week', $mediaSchedule->days_of_week ?? []);
                                            $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                        @endphp
                                        @for($i = 1; $i <= 7; $i++)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="day_{{ $i }}" 
                                                       name="days_of_week[]" value="{{ $i }}" 
                                                       {{ in_array($i, $selectedDays) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="day_{{ $i }}">
                                                    {{ $dayNames[$i-1] }}
                                                </label>
                                            </div>
                                        @endfor
                                    </div>
                                    <div class="form-text">Leave empty to apply to all days</div>
                                </div>

                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('priority') is-invalid @enderror" 
                                           id="priority" name="priority" value="{{ old('priority', $mediaSchedule->priority) }}" 
                                           min="0" max="100" required>
                                    <div class="form-text">Higher priority schedules override lower priority (0-100)</div>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               value="1" {{ old('is_active', $mediaSchedule->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                        <div class="form-text">Only active schedules will be executed</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.media-schedules.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Schedule
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
document.getElementById('schedule_type').addEventListener('change', function() {
    const scheduleType = this.value;
    const prayerField = document.getElementById('prayer_name_field');
    const timeFields = document.getElementById('time_range_fields');
    const countdownField = document.getElementById('countdown_field');
    const prayerSelect = document.getElementById('prayer_name');
    const startTime = document.getElementById('start_time');
    const endTime = document.getElementById('end_time');
    const countdownDuration = document.getElementById('countdown_duration');
    const exactStartTimeField = document.getElementById('exact_start_time_field');
    const exactStartTimeInput = document.getElementById('exact_start_time');

    // Hide all conditional fields
    prayerField.style.display = 'none';
    timeFields.style.display = 'none';
    countdownField.style.display = 'none';
    exactStartTimeField.style.display = 'none';

    // Clear required attributes
    prayerSelect.removeAttribute('required');
    startTime.removeAttribute('required');
    endTime.removeAttribute('required');
    countdownDuration.removeAttribute('required');
    exactStartTimeInput.removeAttribute('required');

    // Show relevant fields based on schedule type
    if (scheduleType === 'prayer_before' || scheduleType === 'prayer_after') {
        prayerField.style.display = 'block';
        prayerSelect.setAttribute('required', 'required');
        exactStartTimeField.style.display = 'block';
        exactStartTimeInput.setAttribute('required', 'required');
    } else if (scheduleType === 'time_range') {
        timeFields.style.display = 'block';
        startTime.setAttribute('required', 'required');
        endTime.setAttribute('required', 'required');
    } else if (scheduleType === 'countdown') {
        countdownField.style.display = 'block';
        countdownDuration.setAttribute('required', 'required');
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('schedule_type').dispatchEvent(new Event('change'));
});
</script>
@endsection
