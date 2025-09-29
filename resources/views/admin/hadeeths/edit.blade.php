@extends('layouts.admin')

@section('title', 'Edit Hadeeth')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Edit Hadeeth</h1>
                <a href="{{ route('admin.hadeeths.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Hadeeths
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.hadeeths.update', $hadeeth) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="arabic_text" class="form-label">Arabic Text <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('arabic_text') is-invalid @enderror" 
                                              id="arabic_text" name="arabic_text" rows="6" required 
                                              dir="rtl" style="font-family: 'Amiri', 'Times New Roman', serif; font-size: 18px;">{{ old('arabic_text', $hadeeth->arabic_text) }}</textarea>
                                    <div class="form-text">Enter the Arabic text of the Hadeeth</div>
                                    @error('arabic_text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="english_translation" class="form-label">English Translation <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('english_translation') is-invalid @enderror" 
                                              id="english_translation" name="english_translation" rows="6" required>{{ old('english_translation', $hadeeth->english_translation) }}</textarea>
                                    <div class="form-text">Enter the English translation of the Hadeeth</div>
                                    @error('english_translation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reference" class="form-label">Reference <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('reference') is-invalid @enderror" 
                                           id="reference" name="reference" value="{{ old('reference', $hadeeth->reference) }}" required>
                                    <div class="form-text">e.g., Sahih Bukhari, Book 1, Hadith 1</div>
                                    @error('reference')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="display_order" class="form-label">Display Order <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('display_order') is-invalid @enderror" 
                                           id="display_order" name="display_order" value="{{ old('display_order', $hadeeth->display_order) }}" 
                                           min="0" required>
                                    <div class="form-text">Lower numbers appear first (0 = highest priority)</div>
                                    @error('display_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="display_date" class="form-label">Display Date</label>
                                    <input type="date" class="form-control @error('display_date') is-invalid @enderror" 
                                           id="display_date" name="display_date" value="{{ old('display_date', $hadeeth->display_date ? $hadeeth->display_date->format('Y-m-d') : '') }}">
                                    <div class="form-text">Optional: Specific date to show this Hadeeth</div>
                                    @error('display_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               value="1" {{ old('is_active', $hadeeth->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                        <div class="form-text">Only active Hadeeths will be displayed</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Preview</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Arabic Text:</h6>
                                                <div id="arabic-preview" class="p-3 bg-white rounded border" 
                                                     style="font-family: 'Amiri', 'Times New Roman', serif; font-size: 18px; min-height: 100px; text-align: right; direction: rtl;">
                                                    {{ $hadeeth->arabic_text }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>English Translation:</h6>
                                                <div id="english-preview" class="p-3 bg-white rounded border" 
                                                     style="min-height: 100px;">
                                                    {{ $hadeeth->english_translation }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted" id="reference-preview">{{ $hadeeth->reference }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.hadeeths.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Hadeeth
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
document.getElementById('arabic_text').addEventListener('input', function() {
    document.getElementById('arabic-preview').textContent = this.value || 'Enter Arabic text above to see preview';
});

document.getElementById('english_translation').addEventListener('input', function() {
    document.getElementById('english-preview').textContent = this.value || 'Enter English translation above to see preview';
});

document.getElementById('reference').addEventListener('input', function() {
    document.getElementById('reference-preview').textContent = this.value || 'Reference will appear here';
});
</script>
@endsection
