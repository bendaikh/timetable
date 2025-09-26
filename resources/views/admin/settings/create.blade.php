@extends('layouts.admin')

@section('title', 'Create Setting')
@section('page-icon', '<i class="bi bi-plus-circle me-2"></i>')
@section('page-title', 'Create Setting')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-plus-circle me-2"></i>
                    Create New Setting
                </h5>
                <a href="{{ route('admin.settings.index') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left me-2"></i>
                    Back to Settings
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="key" class="form-label">Setting Key <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('key') is-invalid @enderror" 
                                       id="key" 
                                       name="key" 
                                       value="{{ old('key') }}" 
                                       placeholder="e.g., masjid_name"
                                       required>
                                @error('key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Use snake_case format (e.g., masjid_name, display_font_size)</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Data Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" 
                                        name="type" 
                                        required>
                                    <option value="">Select Type</option>
                                    <option value="string" {{ old('type') == 'string' ? 'selected' : '' }}>String</option>
                                    <option value="integer" {{ old('type') == 'integer' ? 'selected' : '' }}>Integer</option>
                                    <option value="boolean" {{ old('type') == 'boolean' ? 'selected' : '' }}>Boolean</option>
                                    <option value="json" {{ old('type') == 'json' ? 'selected' : '' }}>JSON</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="value" class="form-label">Value</label>
                        <input type="text" 
                               class="form-control @error('value') is-invalid @enderror" 
                               id="value" 
                               name="value" 
                               value="{{ old('value') }}" 
                               placeholder="Enter the setting value">
                        @error('value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="3" 
                                  placeholder="Describe what this setting does">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Create Setting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Common Settings Reference -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Common Settings Reference
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Display Settings:</h6>
                        <ul class="list-unstyled">
                            <li><code>masjid_name</code> - Name of the masjid</li>
                            <li><code>location</code> - Location of the masjid</li>
                            <li><code>display_font_family</code> - Font family for display</li>
                            <li><code>display_background_color</code> - Background color (hex)</li>
                            <li><code>display_text_color</code> - Text color (hex)</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Prayer Time Settings:</h6>
                        <ul class="list-unstyled">
                            <li><code>fajr_jamaat_offset</code> - Minutes to add to Fajr</li>
                            <li><code>zohar_jamaat_offset</code> - Minutes to add to Zohar</li>
                            <li><code>asr_jamaat_offset</code> - Minutes to add to Asr</li>
                            <li><code>maghrib_jamaat_offset</code> - Minutes to add to Maghrib</li>
                            <li><code>isha_jamaat_offset</code> - Minutes to add to Isha</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
