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
                                        <option value="minutes_before_prayer" {{ old('schedule_type', $mediaSchedule->schedule_type) === 'minutes_before_prayer' ? 'selected' : '' }}>Minutes Before Prayer</option>
                                        <option value="minutes_after_prayer" {{ old('schedule_type', $mediaSchedule->schedule_type) === 'minutes_after_prayer' ? 'selected' : '' }}>Minutes After Prayer</option>
                                    </select>
                                    @error('schedule_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="prayer_name" class="form-label">Prayer <span class="text-danger">*</span></label>
                                    <select class="form-select @error('prayer_name') is-invalid @enderror" id="prayer_name" name="prayer_name" required>
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

                                <div class="mb-3" id="field_minutes_before">
                                    <label for="minutes_before_prayer" class="form-label">Minutes Before Prayer</label>
                                    <input type="number" class="form-control @error('minutes_before_prayer') is-invalid @enderror" 
                                           id="minutes_before_prayer" name="minutes_before_prayer" 
                                           value="{{ old('minutes_before_prayer', $mediaSchedule->minutes_before_prayer) }}" 
                                           min="5" max="120">
                                    <div class="form-text">How many minutes before prayer to start displaying (5-120 minutes)</div>
                                    @error('minutes_before_prayer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3" id="field_minutes_after" style="display:none;">
                                    <label for="minutes_after_prayer" class="form-label">Minutes After Prayer</label>
                                    <input type="number" class="form-control @error('minutes_after_prayer') is-invalid @enderror" 
                                           id="minutes_after_prayer" name="minutes_after_prayer" 
                                           value="{{ old('minutes_after_prayer', $mediaSchedule->minutes_after_prayer) }}" 
                                           min="1" max="120">
                                    <div class="form-text">How many minutes after prayer to start displaying (1-120 minutes)</div>
                                    @error('minutes_after_prayer')
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
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('priority') is-invalid @enderror" 
                                               id="priority" name="priority" value="{{ old('priority', $mediaSchedule->priority) }}" 
                                               min="1" max="100" required>
                                        <button type="button" class="btn btn-outline-secondary" id="use_suggested_priority" style="display: none;">
                                            Use Suggested
                                        </button>
                                    </div>
                                    <div class="form-text" id="priority_help">Higher priority (lower number) schedules display first. Priority must be unique for overlapping schedules.</div>
                                    
                                    <div class="alert alert-info mt-2 mb-0" id="priorities_info" style="display: none;">
                                        <small>
                                            <strong><i class="bi bi-info-circle"></i> Already Taken Priorities (for this time slot):</strong> 
                                            <span class="badge bg-secondary ms-1" id="used_priorities_display"></span>
                                        </small>
                                    </div>
                                    
                                    @error('priority')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
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

                                <!-- Schedule Preview -->
                                <div class="mb-3" id="schedule_preview">
                                    <label class="form-label">Display Time</label>
                                    <div class="border rounded p-3 bg-light">
                                        <div id="display_time_content" class="text-muted">
                                            <small>Enter prayer and minutes to calculate display time</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Overlapping Schedules Warning -->
                                <div class="mb-3" id="overlap_warning" style="display: none;">
                                    <label class="form-label text-warning">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Overlapping Schedules
                                    </label>
                                    <div class="alert alert-warning">
                                        <p class="mb-2"><strong>The following media are already scheduled during this time:</strong></p>
                                        <div id="overlapping_schedules_list"></div>
                                        <p class="mb-0 mt-2"><small>Make sure to set the correct priority to control which media displays first.</small></p>
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
let checkOverlapTimeout = null;
let suggestedPriority = 1;

function toggleFields() {
    const type = document.getElementById('schedule_type').value;
    const beforeField = document.getElementById('field_minutes_before');
    const afterField = document.getElementById('field_minutes_after');
    const beforeInput = document.getElementById('minutes_before_prayer');
    const afterInput = document.getElementById('minutes_after_prayer');

    if (type === 'minutes_before_prayer') {
        beforeField.style.display = '';
        afterField.style.display = 'none';
        beforeInput.required = true;
        afterInput.required = false;
    } else {
        beforeField.style.display = 'none';
        afterField.style.display = '';
        beforeInput.required = false;
        afterInput.required = true;
    }
    checkOverlap();
}

document.addEventListener('DOMContentLoaded', function() {
    // Add event listener for schedule_type to toggle fields
    document.getElementById('schedule_type').addEventListener('change', toggleFields);
    
    // Add event listeners to form fields
    const formFields = ['prayer_name', 'minutes_before_prayer', 'minutes_after_prayer', 'media_id'];
    formFields.forEach(function(fieldName) {
        const element = document.getElementById(fieldName);
        if (element) {
            element.addEventListener('change', checkOverlap);
            element.addEventListener('input', function() {
                // Debounce the API call
                clearTimeout(checkOverlapTimeout);
                checkOverlapTimeout = setTimeout(checkOverlap, 500);
            });
        }
    });
    
    // Use suggested priority button
    document.getElementById('use_suggested_priority').addEventListener('click', function() {
        document.getElementById('priority').value = suggestedPriority;
    });
    
    // Initial field toggle and check
    toggleFields();
    checkOverlap();
});

function checkOverlap() {
    const prayerName = document.getElementById('prayer_name').value;
    const scheduleType = document.getElementById('schedule_type').value;
    const minutesBeforePrayer = document.getElementById('minutes_before_prayer').value;
    const minutesAfterPrayer = document.getElementById('minutes_after_prayer').value;
    const mediaId = document.getElementById('media_id').value;
    
    const minutes = scheduleType === 'minutes_before_prayer' ? minutesBeforePrayer : minutesAfterPrayer;
    
    if (!prayerName || !minutes) {
        document.getElementById('display_time_content').innerHTML = '<small class="text-muted">Enter prayer and minutes to calculate display time</small>';
        document.getElementById('overlap_warning').style.display = 'none';
        document.getElementById('use_suggested_priority').style.display = 'none';
        return;
    }
    
    // Show loading
    document.getElementById('display_time_content').innerHTML = '<small class="text-muted"><i class="bi bi-hourglass-split"></i> Calculating...</small>';
    
    // Make AJAX call to check overlap
    fetch('{{ route('admin.media-schedules.check-overlap') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            schedule_type: scheduleType,
            prayer_name: prayerName,
            minutes: minutes,
            media_id: mediaId,
            exclude_id: {{ $mediaSchedule->id }} // Exclude current schedule when editing
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update display time
            document.getElementById('display_time_content').innerHTML = `
                <div><strong>Start:</strong> ${data.display_start}</div>
                <div><strong>End:</strong> ${data.display_end}</div>
            `;
            
            // Update suggested priority
            suggestedPriority = data.suggested_priority;
            
            // Update used priorities display
            const prioritiesInfo = document.getElementById('priorities_info');
            const usedPrioritiesEl = document.getElementById('used_priorities_display');
            
            if (data.used_priorities && data.used_priorities.length > 0) {
                usedPrioritiesEl.textContent = data.used_priorities.join(', ');
                prioritiesInfo.style.display = '';
            } else {
                prioritiesInfo.style.display = 'none';
            }
            
            // Show overlapping schedules if any
            if (data.overlapping_schedules.length > 0) {
                let schedulesList = '<ul class="mb-0">';
                data.overlapping_schedules.forEach(schedule => {
                    schedulesList += `
                        <li>
                            <strong>${schedule.media_name}</strong> 
                            (Priority: ${schedule.priority}) - 
                            ${schedule.start_time} to ${schedule.end_time}
                            <br><small class="text-muted">${schedule.schedule_type} - ${schedule.prayer_name}</small>
                        </li>
                    `;
                });
                schedulesList += '</ul>';
                
                document.getElementById('overlapping_schedules_list').innerHTML = schedulesList;
                document.getElementById('overlap_warning').style.display = '';
                
                // Show suggested priority button
                document.getElementById('use_suggested_priority').style.display = '';
                document.getElementById('priority_help').innerHTML = `
                    Suggested priority: <strong>${data.suggested_priority}</strong> 
                    (Next available priority to avoid conflicts)
                `;
            } else {
                document.getElementById('overlap_warning').style.display = 'none';
                document.getElementById('use_suggested_priority').style.display = '';
                document.getElementById('priority_help').innerHTML = `
                    Suggested priority: <strong>${data.suggested_priority}</strong> 
                    (No overlapping schedules found)
                `;
            }
        } else {
            document.getElementById('display_time_content').innerHTML = `<small class="text-danger">${data.message}</small>`;
            document.getElementById('overlap_warning').style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error checking overlap:', error);
        document.getElementById('display_time_content').innerHTML = '<small class="text-danger">Error calculating display time</small>';
    });
}
</script>
@endsection
