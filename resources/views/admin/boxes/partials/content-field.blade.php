@php
    // Skip rendering if value is an array (will be handled separately)
    if (is_array($value)) {
        return;
    }
    
    $fieldType = 'text';
    $fieldLabel = ucwords(str_replace('_', ' ', $key));
    $fieldValue = old("content_settings.{$key}", $value);
    
    // Determine field type based on key
    if (str_contains($key, 'color')) {
        $fieldType = 'color';
    } elseif (str_contains($key, 'font_size') || str_contains($key, 'size')) {
        $fieldType = 'text';
        $fieldLabel .= ' (e.g., 16px, 1.2em)';
    } elseif (str_contains($key, 'format')) {
        $fieldType = 'text';
        $fieldLabel .= ' (e.g., h:i A, D j M Y)';
    } elseif (str_contains($key, 'duration') || str_contains($key, 'limit') || str_contains($key, 'offset')) {
        $fieldType = 'number';
    } elseif (str_contains($key, 'enabled') || str_contains($key, 'show_') || str_contains($key, 'active')) {
        $fieldType = 'checkbox';
    } elseif (str_contains($key, 'text') || str_contains($key, 'message') || str_contains($key, 'title')) {
        $fieldType = 'textarea';
    }
@endphp

<div class="mb-3">
    @if($fieldType === 'checkbox')
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" 
                   id="content_{{ $key }}" name="content_settings[{{ $key }}]" value="1"
                   {{ $fieldValue ? 'checked' : '' }}>
            <label class="form-check-label" for="content_{{ $key }}">
                {{ $fieldLabel }}
            </label>
        </div>
    @elseif($fieldType === 'textarea')
        <label for="content_{{ $key }}" class="form-label">{{ $fieldLabel }}</label>
        <textarea class="form-control" id="content_{{ $key }}" name="content_settings[{{ $key }}]" 
                  rows="3" placeholder="Enter {{ strtolower($fieldLabel) }}...">{{ $fieldValue }}</textarea>
    @elseif($fieldType === 'number')
        <label for="content_{{ $key }}" class="form-label">{{ $fieldLabel }}</label>
        <input type="number" class="form-control" id="content_{{ $key }}" name="content_settings[{{ $key }}]" 
               value="{{ $fieldValue }}" min="0" step="1">
    @else
        <label for="content_{{ $key }}" class="form-label">{{ $fieldLabel }}</label>
        <input type="{{ $fieldType }}" class="form-control {{ $fieldType === 'color' ? 'form-control-color' : '' }}" 
               id="content_{{ $key }}" name="content_settings[{{ $key }}]" 
               value="{{ $fieldValue }}" 
               placeholder="Enter {{ strtolower($fieldLabel) }}...">
    @endif
</div>
