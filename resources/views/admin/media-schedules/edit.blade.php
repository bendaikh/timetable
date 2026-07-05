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
                    <form action="{{ route('admin.media-schedules.update', $mediaSchedule->id) }}" method="POST" id="scheduleForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Schedule Type -->
                                <div class="mb-3">
                                    <label for="schedule_type" class="form-label">Schedule Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('schedule_type') is-invalid @enderror" id="schedule_type" name="schedule_type" required>
                                        <option value="">Select Schedule Type</option>
                                        <option value="minutes_before_prayer" {{ old('schedule_type', $mediaSchedule->schedule_type) === 'minutes_before_prayer' ? 'selected' : '' }}>Minutes Before Prayer</option>
                                        <option value="minutes_after_prayer" {{ old('schedule_type', $mediaSchedule->schedule_type) === 'minutes_after_prayer' ? 'selected' : '' }}>Minutes After Prayer</option>
                                        <option value="full_time_poster" {{ old('schedule_type', $mediaSchedule->schedule_type) === 'full_time_poster' ? 'selected' : '' }}>Full Time Poster</option>
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

                                <!-- Minutes Before Prayer -->
                                <div class="mb-3" id="field_minutes_before">
                                    <label for="minutes_before_prayer" class="form-label">Minutes Before Prayer</label>
                                    <input type="number" class="form-control @error('minutes_before_prayer') is-invalid @enderror" 
                                           id="minutes_before_prayer" name="minutes_before_prayer" 
                                           value="{{ old('minutes_before_prayer', $mediaSchedule->minutes_before_prayer) }}" 
                                           min="1" max="480">
                                    <div class="form-text">How many minutes before prayer to start displaying (1-480 minutes / up to 8 hours)</div>
                                    @error('minutes_before_prayer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Minutes After Prayer -->
                                <div class="mb-3" id="field_minutes_after" style="display:none;">
                                    <label for="minutes_after_prayer" class="form-label">Minutes After Prayer</label>
                                    <input type="number" class="form-control @error('minutes_after_prayer') is-invalid @enderror" 
                                           id="minutes_after_prayer" name="minutes_after_prayer" 
                                           value="{{ old('minutes_after_prayer', $mediaSchedule->minutes_after_prayer) }}" 
                                           min="1" max="480">
                                    <div class="form-text">How many minutes after prayer to start displaying (1-480 minutes / up to 8 hours)</div>
                                    @error('minutes_after_prayer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                            <div class="col-md-6">
                                <!-- Media Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Select Media <span class="text-danger">*</span></label>
                                    <div class="form-text mb-2">Select one or more media items</div>
                                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                        @php
                                            $existingMediaIds = old('media_ids', $mediaSchedule->mediaItems->pluck('id')->toArray());
                                        @endphp
                                        @foreach($media as $item)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input media-checkbox" type="checkbox" 
                                                       name="media_ids[]" value="{{ $item->id }}" 
                                                       id="media_{{ $item->id }}"
                                                       data-title="{{ $item->title }}"
                                                       {{ in_array($item->id, $existingMediaIds) ? 'checked' : '' }}>
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
                                               value="1" {{ old('is_active', $mediaSchedule->is_active) ? 'checked' : '' }}>
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
let selectedMedia = [];
let checkOverlapTimeout = null;
@php
$existingMediaData = $mediaSchedule->mediaItems->mapWithKeys(function($media) {
    return [
        $media->id => [
            'duration' => $media->pivot->duration,
            'priority' => $media->pivot->priority,
            'start_date' => $media->pivot->start_date,
            'start_time' => $media->pivot->start_time
                ? \Carbon\Carbon::parse($media->pivot->start_time)->format('H:i')
                : null,
            'expiry_date' => $media->pivot->expiry_date,
            'expiry_time' => $media->pivot->expiry_time
                ? \Carbon\Carbon::parse($media->pivot->expiry_time)->format('H:i')
                : null,
            'start_date' => $media->pivot->start_date,
            'start_time' => $media->pivot->start_time,
            'gap_duration' => $media->pivot->gap_duration ?? 0,
            'days_of_week' => $media->pivot->days_of_week,
        ],
    ];
})->toArray();
@endphp
const existingMediaData = @json($existingMediaData);

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
    } else if (type === 'minutes_before_prayer') {
        prayerField.style.display = '';
        beforeField.style.display = '';
        afterField.style.display = 'none';
        prayerInput.required = true;
        beforeInput.required = true;
        afterInput.required = false;
    } else if (type === 'minutes_after_prayer') {
        prayerField.style.display = '';
        beforeField.style.display = 'none';
        afterField.style.display = '';
        prayerInput.required = true;
        beforeInput.required = false;
        afterInput.required = true;
    }
    
    // Check display time when schedule type changes
    checkDisplayTime();
}

function updateMediaConfig() {
    const configContainer = document.getElementById('media_config_list');
    const configSection = document.getElementById('selected_media_config');
    const scheduleType = document.getElementById('schedule_type').value;
    const isFullTimePoster = scheduleType === 'full_time_poster';
    
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
        
        const oldDuration = @json(old('media_durations', []));
        const oldPriority = @json(old('media_priorities', []));
        const oldExpiryDates = @json(old('media_expiry_dates', []));
        const oldExpiryTimes = @json(old('media_expiry_times', []));
        const oldStartDates = @json(old('media_start_dates', []));
        const oldStartTimes = @json(old('media_start_times', []));
        const oldGapDurations = @json(old('media_gap_durations', []));
        const oldDaysOfWeek = @json(old('media_days_of_week', []));
        
        selectedMedia.forEach((media, index) => {
            // Use old values if available, otherwise use existing data from database
            const duration = oldDuration[index] || existingMediaData[media.id]?.duration || 30;
            const priority = oldPriority[index] || existingMediaData[media.id]?.priority || (index + 1);
            const expiryDate = oldExpiryDates[index] || existingMediaData[media.id]?.expiry_date || '';
            const expiryTime = oldExpiryTimes[index] || existingMediaData[media.id]?.expiry_time || '';
            const startDate = oldStartDates[index] || existingMediaData[media.id]?.start_date || '';
            const startTime = oldStartTimes[index] || existingMediaData[media.id]?.start_time || '';
            const gapDuration = oldGapDurations[index] || existingMediaData[media.id]?.gap_duration || 0;
            
            // Handle days of week for this media
            let mediaDaysOfWeek = [];
            if (oldDaysOfWeek[index]) {
                mediaDaysOfWeek = oldDaysOfWeek[index];
            } else if (existingMediaData[media.id]?.days_of_week) {
                const daysData = existingMediaData[media.id].days_of_week;
                mediaDaysOfWeek = typeof daysData === 'string' ? JSON.parse(daysData) : daysData;
            }
            
            html += `
                <div class="card mb-3 media-config-card">
                    <div class="card-body p-3">
                        <h6 class="mb-3"><i class="bi bi-image"></i> ${media.title}</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label small">Duration (minutes)</label>
                                <input type="number" class="form-control form-control-sm" step="0.5"
                                       name="media_durations[]" value="${duration}" 
                                       min="0.5" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small">Priority</label>
                                <input type="number" class="form-control form-control-sm" 
                                       name="media_priorities[]" value="${priority}" 
                                       min="1" max="100" required>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Display Window <span class="text-muted">(optional calendar limits)</span></label>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small">Start Date</label>
                                <input type="date" class="form-control form-control-sm"
                                       name="media_start_dates[]" value="${startDate}">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small">Start Time</label>
                                <input type="time" class="form-control form-control-sm"
                                       name="media_start_times[]" value="${startTime}">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small">End Date</label>
                                <input type="date" class="form-control form-control-sm"
                                       name="media_expiry_dates[]" value="${expiryDate}">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small">End Time</label>
                                <input type="time" class="form-control form-control-sm"
                                       name="media_expiry_times[]" value="${expiryTime}">
                            </div>
                            <div class="col-12">
                                <small class="text-muted">Optional extra limits on top of the prayer schedule window above. The poster only shows when <strong>both</strong> the prayer window and this calendar window match. Leave blank for no calendar limit.</small>
                            </div>
                        </div>

                        ${isFullTimePoster ? `
                        <div class="row mt-2">
                            <div class="col-12 mb-2">
                                <label class="form-label small">Gap Duration (seconds) <span class="text-muted">(time between this and next media)</span></label>
                                <input type="number" class="form-control form-control-sm" 
                                       name="media_gap_durations[]" value="${gapDuration}" 
                                       min="0" max="3600">
                            </div>
                        </div>
                        ` : `<input type="hidden" name="media_gap_durations[]" value="0">`}
                        
                        <div class="row mt-2">
                            <div class="col-12">
                                <label class="form-label small">Days of Week for this Media <span class="text-muted">(leave unchecked for all days)</span></label>
                                <div class="row">
                                    <div class="col-6 col-md-4">
                                        <div class="form-check form-check-sm">
                                            <input class="form-check-input" type="checkbox" name="media_days_of_week[${index}][]" value="1" id="media_${index}_day_1" ${mediaDaysOfWeek.includes(1) ? 'checked' : ''}>
                                            <label class="form-check-label small" for="media_${index}_day_1">Mon</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="form-check form-check-sm">
                                            <input class="form-check-input" type="checkbox" name="media_days_of_week[${index}][]" value="2" id="media_${index}_day_2" ${mediaDaysOfWeek.includes(2) ? 'checked' : ''}>
                                            <label class="form-check-label small" for="media_${index}_day_2">Tue</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="form-check form-check-sm">
                                            <input class="form-check-input" type="checkbox" name="media_days_of_week[${index}][]" value="3" id="media_${index}_day_3" ${mediaDaysOfWeek.includes(3) ? 'checked' : ''}>
                                            <label class="form-check-label small" for="media_${index}_day_3">Wed</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="form-check form-check-sm">
                                            <input class="form-check-input" type="checkbox" name="media_days_of_week[${index}][]" value="4" id="media_${index}_day_4" ${mediaDaysOfWeek.includes(4) ? 'checked' : ''}>
                                            <label class="form-check-label small" for="media_${index}_day_4">Thu</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="form-check form-check-sm">
                                            <input class="form-check-input" type="checkbox" name="media_days_of_week[${index}][]" value="5" id="media_${index}_day_5" ${mediaDaysOfWeek.includes(5) ? 'checked' : ''}>
                                            <label class="form-check-label small" for="media_${index}_day_5">Fri</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="form-check form-check-sm">
                                            <input class="form-check-input" type="checkbox" name="media_days_of_week[${index}][]" value="6" id="media_${index}_day_6" ${mediaDaysOfWeek.includes(6) ? 'checked' : ''}>
                                            <label class="form-check-label small" for="media_${index}_day_6">Sat</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="form-check form-check-sm">
                                            <input class="form-check-input" type="checkbox" name="media_days_of_week[${index}][]" value="7" id="media_${index}_day_7" ${mediaDaysOfWeek.includes(7) ? 'checked' : ''}>
                                            <label class="form-check-label small" for="media_${index}_day_7">Sun</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        configContainer.innerHTML = html;
        configContainer.querySelectorAll('input[name="media_start_dates[]"], input[name="media_start_times[]"], input[name="media_expiry_dates[]"], input[name="media_expiry_times[]"]').forEach((input) => {
            input.addEventListener('change', checkDisplayTime);
            input.addEventListener('input', checkDisplayTime);
        });
    } else {
        configSection.style.display = 'none';
        configContainer.innerHTML = '';
    }
}

function getCalendarWindowSummary() {
    const startDate = document.querySelector('input[name="media_start_dates[]"]')?.value || '';
    const startTime = document.querySelector('input[name="media_start_times[]"]')?.value || '';
    const endDate = document.querySelector('input[name="media_expiry_dates[]"]')?.value || '';
    const endTime = document.querySelector('input[name="media_expiry_times[]"]')?.value || '';

    if (!startDate && !startTime && !endDate && !endTime) {
        return null;
    }

    return { startDate, startTime, endDate, endTime };
}

function buildCalendarOverlapNote(data) {
    const calendar = getCalendarWindowSummary();
    if (!calendar || !calendar.startDate || !calendar.startTime || !calendar.endDate || !calendar.endTime) {
        if (calendar && (calendar.startDate || calendar.startTime || calendar.endDate || calendar.endTime)) {
            return '<div class="text-warning mt-2"><small>Calendar window is incomplete. Set both date and time for start and end.</small></div>';
        }
        return '';
    }

    const prayerStart = new Date(data.display_start_iso);
    const prayerEnd = new Date(data.display_end_iso);
    const calendarStart = new Date(`${calendar.startDate}T${calendar.startTime}`);
    const calendarEnd = new Date(`${calendar.endDate}T${calendar.endTime}`);
    const overlaps = calendarStart < prayerEnd && calendarEnd > prayerStart;

    const calendarLabel = `${calendar.startDate} ${calendar.startTime} → ${calendar.endDate} ${calendar.endTime}`;

    if (!overlaps) {
        return `<div class="alert alert-warning mt-2 mb-0 py-2"><small><strong>Calendar window does not overlap the prayer window.</strong><br>Calendar: ${calendarLabel}<br>The poster will never display with these settings.</small></div>`;
    }

    return `<div class="text-success mt-2"><small>Calendar window overlaps prayer window: ${calendarLabel}</small></div>`;
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
                media_id: null, // We have multiple media now
                exclude_id: {{ $mediaSchedule->id }} // Exclude current schedule when editing
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update display time
                displayContent.innerHTML = `
                    <div><strong>Jamaat:</strong> ${data.jamaat_time}</div>
                    <div><strong>Prayer window start:</strong> ${data.display_start}</div>
                    <div><strong>Prayer window end:</strong> ${data.display_end}</div>
                    ${buildCalendarOverlapNote(data)}
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
    document.getElementById('schedule_type').addEventListener('change', function() {
        toggleFields();
        updateMediaConfig(); // Update media config when schedule type changes
    });
    
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
    
    // Add real-time preview for duration changes
    function addDurationPreviewListeners() {
        document.querySelectorAll('input[name="media_durations[]"]').forEach(input => {
            input.addEventListener('input', function() {
                updateDurationPreview(this);
            });
        });
    }
    
    // Update duration preview
    function updateDurationPreview(input) {
        const card = input.closest('.media-config-card');
        if (!card) return;
        
        const duration = parseFloat(input.value) || 0;
        let previewElement = card.querySelector('.duration-preview');
        
        if (!previewElement) {
            previewElement = document.createElement('div');
            previewElement.className = 'duration-preview mt-2 p-2 bg-light border rounded';
            const durationContainer = input.closest('.col-md-6');
            if (durationContainer) {
                durationContainer.parentElement.insertAdjacentElement('afterend', previewElement);
            }
        }
        
        if (duration > 0) {
            const seconds = Math.round(duration * 60);
            const minutes = Math.floor(seconds / 60);
            const secs = seconds % 60;
            previewElement.innerHTML = `<small><strong>Preview:</strong> ${duration}m = ${seconds}s (${minutes}m ${secs}s)</small>`;
            previewElement.style.display = 'block';
        } else {
            previewElement.style.display = 'none';
        }
    }
    
    // Initial setup
    toggleFields();
    updateMediaConfig();
    checkDisplayTime(); // Check display time on page load
    
    // Add listeners after media config is updated
    setTimeout(() => {
        addDurationPreviewListeners();
    }, 100);
    
    // Re-add listeners when media config updates
    const originalUpdateMediaConfig = window.updateMediaConfig;
    window.updateMediaConfig = function() {
        originalUpdateMediaConfig.call(this);
        setTimeout(() => {
            addDurationPreviewListeners();
        }, 100);
    };
});
</script>
@endsection
