<div class="mb-4">
    <h6>Sliding Text Typography</h6>
    <div class="row">
        <div class="col-md-6">
            <label for="font_size" class="form-label">Default Font Size (rem)</label>
            <input type="text" class="form-control" id="font_size" name="styling_settings[font_size]"
                   value="{{ old('styling_settings.font_size', \App\Support\CssUnits::normalizeBoxRem($box->styling_settings['font_size'] ?? null, '5rem')) }}"
                   placeholder="e.g. 5">
            <div class="form-text">Used when a sliding text item has no font size set.</div>
        </div>
        <div class="col-md-6">
            <label for="font_weight" class="form-label">Default Font Weight</label>
            <select class="form-select" id="font_weight" name="styling_settings[font_weight]">
                @foreach(['400' => 'Normal (400)', '500' => 'Medium (500)', '600' => 'Semi-bold (600)', '700' => 'Bold (700)', '800' => 'Extra-bold (800)'] as $weight => $label)
                    <option value="{{ $weight }}" {{ old('styling_settings.font_weight', $box->styling_settings['font_weight'] ?? '700') == $weight ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>
