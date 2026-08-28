@php
    use App\Support\ScheduleDaysOfWeek;

    $fieldName = $name ?? 'days_of_week';
    $selectedDays = ScheduleDaysOfWeek::normalize($selected ?? []) ?? [];
    $idPrefix = $idPrefix ?? str_replace(['[', ']'], '_', $fieldName);
@endphp
<div class="mb-3">
    <label class="form-label">
        {{ $label ?? 'Active Days' }}
        <span class="text-muted">(leave unchecked for all days)</span>
    </label>
    <div class="row">
        @foreach(ScheduleDaysOfWeek::LABELS as $dayValue => $dayLabel)
            <div class="col-6 col-md-4">
                <div class="form-check form-check-sm">
                    <input class="form-check-input"
                           type="checkbox"
                           name="{{ $fieldName }}[]"
                           value="{{ $dayValue }}"
                           id="{{ $idPrefix }}_day_{{ $dayValue }}"
                           {{ in_array($dayValue, $selectedDays, true) ? 'checked' : '' }}>
                    <label class="form-check-label small" for="{{ $idPrefix }}_day_{{ $dayValue }}">{{ $dayLabel }}</label>
                </div>
            </div>
        @endforeach
    </div>
    @if(!empty($help))
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
