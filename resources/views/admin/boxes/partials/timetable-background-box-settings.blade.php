<!-- Timetable Background Box Specific Settings -->
<div class="mb-4">
    <h6>Timetable Background Settings</h6>
    <div class="row">
        <div class="col-md-6">
            <label for="background_color" class="form-label">Background Color</label>
            <input type="color" class="form-control form-control-color" 
                   id="background_color" name="styling_settings[background_color]"
                   value="{{ old('styling_settings.background_color', $box->getBackgroundColorHex()) }}">
        </div>
    </div>
    
    <div class="mt-3">
        <div class="alert alert-info">
            <small>
                <strong>Note:</strong> This controls the overall background color of the entire timetable page.
            </small>
        </div>
    </div>
</div>
