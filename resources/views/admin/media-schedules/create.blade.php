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
                    <form action="{{ route('admin.media-schedules.store') }}" method="POST" id="scheduleForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Schedule Type -->
                                <div class="mb-3">
                                    <label for="schedule_type" class="form-label">Schedule Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('schedule_type') is-invalid @enderror" id="schedule_type" name="schedule_type" required>
                                        <option value="">Select Schedule Type</option>
                                        <option value="minutes_before_prayer" {{ old('schedule_type', 'minutes_before_prayer') === 'minutes_before_prayer' ? 'selected' : '' }}>Minutes Before Prayer</option>
                                        <option value="minutes_after_prayer" {{ old('schedule_type') === 'minutes_after_prayer' ? 'selected' : '' }}>Minutes After Prayer</option>
                                        <option value="full_time_poster" {{ old('schedule_type') === 'full_time_poster' ? 'selected' : '' }}>Full Time Poster</option>
                                    </select>
                                    @error('schedule_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Prayer Selection -->
                                <div class="mb-3" id="field_prayer">
                                    <label for="prayer_name" class="form-label">Prayer (Jamaat Time) <span class="text-danger">*</span></label>
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

                                <!-- Minutes Before Prayer -->
                                <div class="mb-3" id="field_minutes_before">
                                    <label for="minutes_before_prayer" class="form-label">Minutes Before Prayer</label>
                                    <input type="number" class="form-control @error('minutes_before_prayer') is-invalid @enderror" 
                                           id="minutes_before_prayer" name="minutes_before_prayer" value="{{ old('minutes_before_prayer') }}" 
                                           min="5" max="120">
                                    <div class="form-text">How many minutes before prayer to start displaying (5-120 minutes)</div>
                                    @error('minutes_before_prayer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Minutes After Prayer -->
                                <div class="mb-3" id="field_minutes_after" style="display:none;">
                                    <label for="minutes_after_prayer" class="form-label">Minutes After Prayer</label>
                                    <input type="number" class="form-control @error('minutes_after_prayer') is-invalid @enderror" 
                                           id="minutes_after_prayer" name="minutes_after_prayer" value="{{ old('minutes_after_prayer') }}" 
                                           min="1" max="480">
                                    <div class="form-text">How many minutes after prayer to start displaying (1-480 minutes / up to 8 hours)</div>
                                    @error('minutes_after_prayer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Days of Week -->
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
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Media Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Select Media <span class="text-danger">*</span></label>
                                    <div class="form-text mb-2">Select one or more media items</div>
                                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                        @foreach($media as $item)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input media-checkbox" type="checkbox" 
                                                       name="media_ids[]" value="{{ $item->id }}" 
                                                       id="media_{{ $item->id }}"
                                                       data-title="{{ $item->title }}"
                                                       {{ in_array($item->id, old('media_ids', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label d-flex align-items-center" for="media_{{ $item->id }}">
                                                    @if($item->isImage())
                                                        <img src="{{ $item->file_url }}" alt="{{ $item->title }}" 
                                                             class="img-thumbnail me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center me-2" 
                                                             style="width: 30px; height: 30px; font-size: 12px;">
                                                            <i class="bi bi-play-circle"></i>
                                                        </div>
                                                    @endif
                                                    <span>{{ $item->title }} ({{ ucfirst($item->type) }})</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('media_ids')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Selected Media Configuration -->
                                <div class="mb-3" id="selected_media_config" style="display: none;">
                                    <label class="form-label">Media Configuration</label>
                                    <div class="alert alert-info">
                                        <small><i class="bi bi-info-circle"></i> Set duration and priority for each selected media. Priority determines display order (1 = first, 2 = second, etc.)</small>
                                    </div>
                                    <div id="media_config_list"></div>
                                </div>

                                <!-- Active Status -->
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
                                    <label class="form-label">Display Time Preview</label>
                                    <div class="border rounded p-3 bg-light">
                                        <div id="display_time_content" class="text-muted">
                                            <small>Select schedule type and prayer to calculate display time</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Overlapping Schedules Warning -->
                                <div class="mb-3" id="overlap_warning" style="display: none;">
                                    <label class="form-label text-warning">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Overlapping Schedules
                                    </label>
                                    <div class="alert alert-warning">
                                        <p class="mb-2"><strong>The following schedules are already active during this time:</strong></p>
                                        <div id="overlapping_schedules_list"></div>
                                        <p class="mb-0 mt-2"><small>Multiple schedules can run at the same time. Make sure this is intended.</small></p>
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
let selectedMedia = [];
let checkOverlapTimeout = null;

function toggleFields() {
    const type = document.getElementById('schedule_type').value;
    const prayerField = document.getElementById('field_prayer');
    const beforeField = document.getElementById('field_minutes_before');
    const afterField = document.getElementById('field_minutes_after');
    const beforeInput = document.getElementById('minutes_before_prayer');
    const afterInput = document.getElementById('minutes_after_prayer');
    const prayerInput = document.getElementById('prayer_name');

    if (type === 'full_time_poster') {
        // Full time poster - hide prayer and minutes fields
        prayerField.style.display = 'none';
        beforeField.style.display = 'none';
        afterField.style.display = 'none';
        prayerInput.required = false;
        beforeInput.required = false;
        afterInput.required = false;
        prayerInput.value = '';
        beforeInput.value = '';
        afterInput.value = '';
    } else if (type === 'minutes_before_prayer') {
        prayerField.style.display = '';
        beforeField.style.display = '';
        afterField.style.display = 'none';
        prayerInput.required = true;
        beforeInput.required = true;
        afterInput.required = false;
        afterInput.value = '';
    } else if (type === 'minutes_after_prayer') {
        prayerField.style.display = '';
        beforeField.style.display = 'none';
        afterField.style.display = '';
        prayerInput.required = true;
        beforeInput.required = false;
        afterInput.required = true;
        beforeInput.value = '';
    }
    
    // Check display time when schedule type changes
    checkDisplayTime();
}

function updateMediaConfig() {
    const configContainer = document.getElementById('media_config_list');
    const configSection = document.getElementById('selected_media_config');
    
    selectedMedia = [];
    document.querySelectorAll('.media-checkbox:checked').forEach(checkbox => {
        selectedMedia.push({
            id: checkbox.value,
            title: checkbox.getAttribute('data-title')
        });
    });
    
    if (selectedMedia.length > 0) {
        configSection.style.display = 'block';
        let html = '';
        
        selectedMedia.forEach((media, index) => {
            const oldDuration = {{ json_encode(old('media_durations', [])) }};
            const oldPriority = {{ json_encode(old('media_priorities', [])) }};
            
            html += `
                <div class="card mb-2">
                    <div class="card-body p-3">
                        <h6 class="mb-2">${media.title}</h6>
                        <div class="row">
                            <div class="col-6">
                                <label class="form-label small">Duration (seconds)</label>
                                <input type="number" class="form-control form-control-sm" 
                                       name="media_durations[]" value="${oldDuration[index] || 30}" 
                                       min="1" max="300" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Priority</label>
                                <input type="number" class="form-control form-control-sm" 
                                       name="media_priorities[]" value="${oldPriority[index] || (index + 1)}" 
                                       min="1" max="100" required>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        configContainer.innerHTML = html;
    } else {
        configSection.style.display = 'none';
        configContainer.innerHTML = '';
    }
}

function checkDisplayTime() {
    const scheduleType = document.getElementById('schedule_type').value;
    const prayerName = document.getElementById('prayer_name').value;
    const minutesBeforePrayer = document.getElementById('minutes_before_prayer').value;
    const minutesAfterPrayer = document.getElementById('minutes_after_prayer').value;
    
    const displayContent = document.getElementById('display_time_content');
    
    // For full time poster
    if (scheduleType === 'full_time_poster') {
        displayContent.innerHTML = '<div><strong>All Day</strong><br><small class="text-muted">Media will cycle continuously throughout the day</small></div>';
        document.getElementById('overlap_warning').style.display = 'none';
        return;
    }
    
    // For prayer-based schedules
    const minutes = scheduleType === 'minutes_before_prayer' ? minutesBeforePrayer : minutesAfterPrayer;
    
    if (!prayerName || !minutes) {
        displayContent.innerHTML = '<small class="text-muted">Select schedule type, prayer, and minutes to calculate display time</small>';
        document.getElementById('overlap_warning').style.display = 'none';
        return;
    }
    
    // Show loading
    displayContent.innerHTML = '<small class="text-muted"><i class="bi bi-hourglass-split"></i> Calculating...</small>';
    
    // Debounce the API call
    clearTimeout(checkOverlapTimeout);
    checkOverlapTimeout = setTimeout(() => {
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
                media_id: null // We have multiple media now, so pass null
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update display time
                displayContent.innerHTML = `
                    <div><strong>Start:</strong> ${data.display_start}</div>
                    <div><strong>End:</strong> ${data.display_end}</div>
                `;
                
                // Show overlapping schedules if any
                if (data.overlapping_schedules && data.overlapping_schedules.length > 0) {
                    let schedulesList = '<ul class="mb-0">';
                    data.overlapping_schedules.forEach(schedule => {
                        schedulesList += `
                            <li>
                                <strong>${schedule.media_name || 'Schedule #' + schedule.id}</strong> - 
                                ${schedule.start_time} to ${schedule.end_time}
                                <br><small class="text-muted">${schedule.schedule_type} - ${schedule.prayer_name}</small>
                            </li>
                        `;
                    });
                    schedulesList += '</ul>';
                    
                    document.getElementById('overlapping_schedules_list').innerHTML = schedulesList;
                    document.getElementById('overlap_warning').style.display = '';
                } else {
                    document.getElementById('overlap_warning').style.display = 'none';
                }
            } else {
                displayContent.innerHTML = `<small class="text-danger">${data.message || 'Error calculating display time'}</small>`;
                document.getElementById('overlap_warning').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error checking overlap:', error);
            displayContent.innerHTML = '<small class="text-danger">Error calculating display time</small>';
        });
    }, 500);
}

document.addEventListener('DOMContentLoaded', function() {
    // Schedule type change
    document.getElementById('schedule_type').addEventListener('change', toggleFields);
    
    // Add event listeners for time calculation
    ['prayer_name', 'minutes_before_prayer', 'minutes_after_prayer'].forEach(fieldName => {
        const element = document.getElementById(fieldName);
        if (element) {
            element.addEventListener('change', checkDisplayTime);
            element.addEventListener('input', checkDisplayTime);
        }
    });
    
    // Media checkbox changes
    document.querySelectorAll('.media-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateMediaConfig);
    });
    
    // Initial setup
    toggleFields();
    updateMediaConfig();
});
</script>
@endsection
