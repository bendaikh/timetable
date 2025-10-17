<!-- Next Prayer Note Box Specific Settings -->
<div class="mb-4">
    <h6>Next Prayer Countdown Settings</h6>
    <div class="row">
        <div class="col-md-12">
            <label for="text" class="form-label">Countdown Label Text</label>
            <input type="text" class="form-control" id="text" name="content_settings[text]"
                   value="{{ old('content_settings.text', $box->content_settings['text'] ?? 'Next prayer in:') }}"
                   placeholder="e.g., Next prayer in:">
            <small class="form-text text-muted">The text displayed above the countdown timer.</small>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="show_countdown" name="content_settings[show_countdown]" value="1"
                       {{ ($box->content_settings['show_countdown'] ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="show_countdown">
                    Show Countdown Timer
                </label>
            </div>
            <small class="form-text text-muted">Display the countdown to the next prayer time.</small>
        </div>
    </div>
    
    <div class="mt-3">
        <h6>Countdown Display</h6>
        <div class="alert alert-info">
            <small>
                <strong>How it works:</strong> The countdown automatically calculates and displays the time remaining until the next prayer (Jamaat time if available). It shows hours, minutes, and seconds in HH:MM:SS format, along with the name of the upcoming prayer.
            </small>
        </div>
    </div>
</div>

