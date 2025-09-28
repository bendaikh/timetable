@extends('layouts.admin')

@section('title', 'Add New Media Schedule')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Add New Media Schedule</h1>
                <a href="{{ route('admin.media-schedules.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Schedules
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.media-schedules.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="media_id" class="form-label">Media <span class="text-danger">*</span></label>
                                    <select class="form-select @error('media_id') is-invalid @enderror" id="media_id" name="media_id" required>
                                        <option value="">Select Media</option>
                                        @foreach($media as $item)
                                            <option value="{{ $item->id }}" {{ old('media_id') == $item->id ? 'selected' : '' }}>
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
                                        <option value="prayer_before" {{ old('schedule_type') === 'prayer_before' ? 'selected' : '' }}>Before Prayer</option>
                                        <option value="prayer_after" {{ old('schedule_type') === 'prayer_after' ? 'selected' : '' }}>After Prayer</option>
                                        <option value="time_range" {{ old('schedule_type') === 'time_range' ? 'selected' : '' }}>Time Range</option>
                                        <option value="countdown" {{ old('schedule_type') === 'countdown' ? 'selected' : '' }}>Countdown Timer</option>
                                    </select>
                                    @error('schedule_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Prayer Selection (for prayer_before, prayer_after, countdown) -->
                                <div class="mb-3" id="prayer_selection" style="display: none;">
                                    <label for="prayer_name" class="form-label">Prayer</label>
                                    <select class="form-select @error('prayer_name') is-invalid @enderror" id="prayer_name" name="prayer_name">
                                        <option value="">Select Prayer</option>
                                        <option value="fajr" {{ old('prayer_name') === 'fajr' ? 'selected' : '' }}>Fajr</option>
                                        <option value="zohar" {{ old('prayer_name') === 'zohar' ? 'selected' : '' }}>Zohar</option>
                                        <option value="asr" {{ old('prayer_name') === 'asr' ? 'selected' : '' }}>Asr</option>
                                        <option value="maghrib" {{ old('prayer_name') === 'maghrib' ? 'selected' : '' }}>Maghrib</option>
                                        <option value="isha" {{ old('prayer_name') === 'isha' ? 'selected' : '' }}>Isha</option>
                                    </select>
                                    @error('prayer_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Time Range Selection (for time_range) -->
                                <div class="row" id="time_range_selection" style="display: none;">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label for="start_time" class="form-label">Start Time</label>
                                            <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                                   id="start_time" name="start_time" value="{{ old('start_time') }}">
                                            @error('start_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label for="end_time" class="form-label">End Time</label>
                                            <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                                   id="end_time" name="end_time" value="{{ old('end_time') }}">
                                            @error('end_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Countdown Duration (for countdown) -->
                                <div class="mb-3" id="countdown_duration_selection" style="display: none;">
                                    <label for="countdown_duration" class="form-label">Countdown Duration (seconds)</label>
                                    <input type="number" class="form-control @error('countdown_duration') is-invalid @enderror" 
                                           id="countdown_duration" name="countdown_duration" value="{{ old('countdown_duration', 30) }}" 
                                           min="10" max="300">
                                    <div class="form-text">How many seconds before the prayer to start countdown</div>
                                    @error('countdown_duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="days_of_week" class="form-label">Days of Week</label>
                                    <div class="form-text mb-2">Select specific days (leave empty for all days)</div>
                                    <div class="row">
                                        @php
                                            $days = [
                                                ['value' => 1, 'label' => 'Monday'],
                                                ['value' => 2, 'label' => 'Tuesday'],
                                                ['value' => 3, 'label' => 'Wednesday'],
                                                ['value' => 4, 'label' => 'Thursday'],
                                                ['value' => 5, 'label' => 'Friday'],
                                                ['value' => 6, 'label' => 'Saturday'],
                                                ['value' => 7, 'label' => 'Sunday']
                                            ];
                                        @endphp
                                        @foreach($days as $day)
                                            <div class="col-6 col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="days_of_week[]" value="{{ $day['value'] }}" 
                                                           id="day_{{ $day['value'] }}"
                                                           {{ in_array($day['value'], old('days_of_week', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="day_{{ $day['value'] }}">
                                                        {{ $day['label'] }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('days_of_week')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('priority') is-invalid @enderror" 
                                           id="priority" name="priority" value="{{ old('priority', 0) }}" 
                                           min="0" max="100" required>
                                    <div class="form-text">Higher priority schedules override lower priority ones</div>
                                    @error('priority')
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
                                        <div class="form-text">Only active schedules will be processed</div>
                                    </div>
                                </div>

                                <!-- Schedule Preview -->
                                <div class="mb-3" id="schedule_preview">
                                    <label class="form-label">Schedule Preview</label>
                                    <div class="border rounded p-3 bg-light">
                                        <div id="preview_content" class="text-muted">
                                            Select schedule type to see preview
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.media-schedules.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Schedule
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
    const prayerSelection = document.getElementById('prayer_selection');
    const timeRangeSelection = document.getElementById('time_range_selection');
    const countdownSelection = document.getElementById('countdown_duration_selection');
    const prayerNameSelect = document.getElementById('prayer_name');
    
    // Hide all conditional fields
    prayerSelection.style.display = 'none';
    timeRangeSelection.style.display = 'none';
    countdownSelection.style.display = 'none';
    
    // Show relevant fields based on schedule type
    switch(scheduleType) {
        case 'prayer_before':
        case 'prayer_after':
            prayerSelection.style.display = 'block';
            prayerNameSelect.required = true;
            break;
        case 'countdown':
            prayerSelection.style.display = 'block';
            countdownSelection.style.display = 'block';
            prayerNameSelect.required = true;
            break;
        case 'time_range':
            timeRangeSelection.style.display = 'block';
            prayerNameSelect.required = false;
            break;
        default:
            prayerNameSelect.required = false;
    }
    
    updatePreview();
});

// Update preview when form changes
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to form fields
    const formFields = ['schedule_type', 'prayer_name', 'start_time', 'end_time', 'countdown_duration', 'days_of_week[]'];
    formFields.forEach(function(fieldName) {
        const elements = document.querySelectorAll(`[name="${fieldName}"]`);
        elements.forEach(function(element) {
            element.addEventListener('change', updatePreview);
        });
    });
    
    // Initial preview update
    updatePreview();
});

function updatePreview() {
    const scheduleType = document.getElementById('schedule_type').value;
    const prayerName = document.getElementById('prayer_name').value;
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    const countdownDuration = document.getElementById('countdown_duration').value;
    const selectedDays = Array.from(document.querySelectorAll('input[name="days_of_week[]"]:checked')).map(cb => cb.value);
    
    let preview = '';
    
    if (!scheduleType) {
        preview = 'Select schedule type to see preview';
    } else {
        switch(scheduleType) {
            case 'prayer_before':
                preview = `Display before ${prayerName || 'selected prayer'} prayer`;
                break;
            case 'prayer_after':
                preview = `Display after ${prayerName || 'selected prayer'} prayer`;
                break;
            case 'time_range':
                preview = `Display from ${startTime || 'start time'} to ${endTime || 'end time'}`;
                break;
            case 'countdown':
                preview = `Show countdown timer ${countdownDuration || 30} seconds before ${prayerName || 'selected prayer'} prayer`;
                break;
        }
        
        if (selectedDays.length > 0 && selectedDays.length < 7) {
            const dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            const dayLabels = selectedDays.map(day => dayNames[day - 1]).join(', ');
            preview += ` on ${dayLabels}`;
        } else if (selectedDays.length === 0) {
            preview += ' every day';
        }
    }
    
    document.getElementById('preview_content').textContent = preview;
}
</script>
@endsection
