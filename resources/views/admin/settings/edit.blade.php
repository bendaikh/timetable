@extends('layouts.admin')

@section('title', 'Edit Setting')
@section('page-icon', '<i class="bi bi-pencil me-2"></i>')
@section('page-title', 'Edit Setting')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-pencil me-2"></i>
                    Edit Setting: {{ $setting->key }}
                </h5>
                <a href="{{ route('admin.settings.index') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left me-2"></i>
                    Back to Settings
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.update', $setting) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="key" class="form-label">Setting Key <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('key') is-invalid @enderror" 
                                       id="key" 
                                       name="key" 
                                       value="{{ old('key', $setting->key) }}" 
                                       required>
                                @error('key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                    <option value="string" {{ old('type', $setting->type) == 'string' ? 'selected' : '' }}>String</option>
                                    <option value="integer" {{ old('type', $setting->type) == 'integer' ? 'selected' : '' }}>Integer</option>
                                    <option value="boolean" {{ old('type', $setting->type) == 'boolean' ? 'selected' : '' }}>Boolean</option>
                                    <option value="json" {{ old('type', $setting->type) == 'json' ? 'selected' : '' }}>JSON</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="value" class="form-label">Value</label>
                        @if($setting->type === 'boolean')
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="value" 
                                       name="value" 
                                       value="1"
                                       {{ old('value', $setting->value) ? 'checked' : '' }}>
                                <label class="form-check-label" for="value">
                                    {{ $setting->value ? 'Enabled' : 'Disabled' }}
                                </label>
                            </div>
                        @elseif($setting->type === 'json')
                            <textarea class="form-control @error('value') is-invalid @enderror" 
                                      id="value" 
                                      name="value" 
                                      rows="5" 
                                      placeholder="Enter JSON data">{{ old('value', $setting->value) }}</textarea>
                        @else
                            <input type="text" 
                                   class="form-control @error('value') is-invalid @enderror" 
                                   id="value" 
                                   name="value" 
                                   value="{{ old('value', $setting->value) }}">
                        @endif
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
                                  placeholder="Describe what this setting does">{{ old('description', $setting->description) }}</textarea>
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
                            Update Setting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
