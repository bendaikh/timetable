@extends('layouts.admin')

@section('title', 'Edit Sliding Text')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Edit Sliding Text</h1>
                <a href="{{ route('admin.sliding-texts.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Sliding Texts
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.sliding-texts.update', $slidingText) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="text" class="form-label">Sliding Text <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('text') is-invalid @enderror" 
                                              id="text" name="text" rows="3" required>{{ old('text', $slidingText->text) }}</textarea>
                                    <div class="form-text">Enter the text that will slide across the sidebar (max 500 characters)</div>
                                    @error('text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="animation_speed" class="form-label">Animation Speed (seconds) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('animation_speed') is-invalid @enderror" 
                                                   id="animation_speed" name="animation_speed" value="{{ old('animation_speed', $slidingText->animation_speed) }}" 
                                                   min="5" max="60" required>
                                            <div class="form-text">How long the animation takes (5-60 seconds)</div>
                                            @error('animation_speed')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="display_order" class="form-label">Display Order</label>
                                            <input type="number" class="form-control @error('display_order') is-invalid @enderror" 
                                                   id="display_order" name="display_order" value="{{ old('display_order', $slidingText->display_order) }}" 
                                                   min="0">
                                            <div class="form-text">Lower numbers appear first</div>
                                            @error('display_order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="font_size" class="form-label">Font Size (rem) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('font_size') is-invalid @enderror" 
                                           id="font_size" name="font_size" value="{{ old('font_size', $slidingText->font_size) }}" 
                                           placeholder="e.g., 3, 5.5, 10" pattern="^[0-9]+(\.[0-9]+)?$" required>
                                    <div class="form-text">Enter the font size in rem units (e.g., 3rem, 10rem)</div>
                                    @error('font_size')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="font_weight" class="form-label">Font Weight <span class="text-danger">*</span></label>
                                    <select class="form-select @error('font_weight') is-invalid @enderror" 
                                            id="font_weight" name="font_weight" required>
                                        <option value="400" {{ old('font_weight', $slidingText->font_weight) == '400' ? 'selected' : '' }}>Normal (400)</option>
                                        <option value="500" {{ old('font_weight', $slidingText->font_weight) == '500' ? 'selected' : '' }}>Medium (500)</option>
                                        <option value="600" {{ old('font_weight', $slidingText->font_weight) == '600' ? 'selected' : '' }}>Semi-Bold (600)</option>
                                        <option value="700" {{ old('font_weight', $slidingText->font_weight) == '700' ? 'selected' : '' }}>Bold (700)</option>
                                        <option value="800" {{ old('font_weight', $slidingText->font_weight) == '800' ? 'selected' : '' }}>Extra-Bold (800)</option>
                                        <option value="900" {{ old('font_weight', $slidingText->font_weight) == '900' ? 'selected' : '' }}>Black (900)</option>
                                    </select>
                                    @error('font_weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="text_color" class="form-label">Text Color <span class="text-danger">*</span></label>
                                    <input type="color" class="form-control form-control-color @error('text_color') is-invalid @enderror" 
                                           id="text_color" name="text_color" value="{{ old('text_color', $slidingText->text_color) }}" required>
                                    @error('text_color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="background_color" class="form-label">Background Color <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('background_color') is-invalid @enderror" 
                                           id="background_color" name="background_color" value="{{ old('background_color', $slidingText->background_color) }}" required>
                                    <div class="form-text">Use rgba or hex format</div>
                                    @error('background_color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               value="1" {{ old('is_active', $slidingText->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <strong>Active</strong>
                                        </label>
                                        <div class="form-text">Only active texts will be displayed</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.sliding-texts.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Sliding Text
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
    document.querySelector('form')?.addEventListener('submit', function() {
        try {
            localStorage.setItem('timetable-display-sync', String(Date.now()));
            const channel = new BroadcastChannel('timetable-display');
            channel.postMessage({ type: 'sync', at: Date.now() });
            channel.close();
        } catch (error) {
            // Ignore unsupported browsers.
        }
    });
</script>
@endsection
