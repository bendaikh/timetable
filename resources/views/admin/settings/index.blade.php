@extends('layouts.admin')

@section('title', 'Settings')
@section('page-icon', '<i class="bi bi-gear me-2"></i>')
@section('page-title', 'Settings')

@php
use App\Models\Setting;
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-gear me-2"></i>
                    Application Settings
                </h5>
                <a href="{{ route('admin.settings.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-circle me-2"></i>
                    Add Setting
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                
                @if($settings->count() > 0)
                    <form method="POST" action="{{ route('admin.settings.batch-update') }}" enctype="multipart/form-data" id="settings-form">
                        @csrf
                        
                        <!-- Update Button at Top -->
                        <div class="text-center mb-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>
                                Update All Settings
                            </button>
                        </div>
                        
                        <!-- Logo Upload Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">
                                            <i class="bi bi-image me-2"></i>
                                            Logo Upload
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="logo" class="form-label">Upload Logo</label>
                                                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*" onchange="previewLogo(this)">
                                                    <div class="form-text">Upload a logo image (JPG, PNG, GIF, SVG - Max 2MB)</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="text-center">
                                                    <p class="mb-2"><strong>Current Logo:</strong></p>
                                                    <div id="logo-preview">
                                                        @if(Setting::get('logo_path'))
                                                            <img src="{{ asset('storage/' . Setting::get('logo_path')) }}" 
                                                                 alt="Current Logo" 
                                                                 class="img-thumbnail" 
                                                                 style="max-height: 100px; max-width: 200px;">
                                                        @else
                                                            <div class="text-muted">
                                                                <i class="bi bi-image display-4"></i>
                                                                <p class="mt-2">No logo uploaded</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            @foreach($settings as $setting)
                            <div class="col-md-6 mb-4">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">
                                            {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                        </h6>
                                        
                                        @if($setting->description)
                                            <p class="card-text text-muted small">{{ $setting->description }}</p>
                                        @endif
                                        
                                        @if($setting->type === 'boolean')
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="settings[{{ $setting->key }}]" 
                                                       value="1"
                                                       {{ $setting->value ? 'checked' : '' }}>
                                                <label class="form-check-label">
                                                    {{ $setting->value ? 'Enabled' : 'Disabled' }}
                                                </label>
                                            </div>
                                        @elseif($setting->type === 'json')
                                            <textarea class="form-control" 
                                                      name="settings[{{ $setting->key }}]" 
                                                      rows="3"
                                                      placeholder="JSON format">{{ $setting->value }}</textarea>
                                        @else
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="settings[{{ $setting->key }}]" 
                                                   value="{{ $setting->value }}"
                                                   placeholder="Enter {{ $setting->type }} value">
                                        @endif
                                        
                                        <div class="mt-2 d-flex justify-content-between">
                                            <small class="text-muted">Type: {{ $setting->type }}</small>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.settings.edit', $setting) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteSetting({{ $setting->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </form>
                    
                    <div class="d-flex justify-content-center mt-4">
                        {{ $settings->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-gear display-4 text-muted"></i>
                        <h4 class="mt-3 text-muted">No Settings Found</h4>
                        <p class="text-muted">Configure your application settings here.</p>
                        <a href="{{ route('admin.settings.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>
                            Add First Setting
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Default Settings Information -->
@if(count($defaultSettings) > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Available Default Settings
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($defaultSettings as $key => $value)
                    <div class="col-md-4 mb-2">
                        <strong>{{ ucwords(str_replace('_', ' ', $key)) }}</strong>
                        <br>
                        <small class="text-muted">{{ $key }}</small>
                        <br>
                        <code>{{ $value }}</code>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    // Logo preview functionality
    function previewLogo(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('logo-preview');
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Logo Preview" class="img-thumbnail" style="max-height: 100px; max-width: 200px;">';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Delete setting function
    function deleteSetting(settingId) {
        if (confirm('Are you sure you want to delete this setting?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.settings.destroy", ":id") }}'.replace(':id', settingId);
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Form submission handling
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('settings-form');
        const submitBtn = form ? form.querySelector('button[type="submit"]') : null;
        
        if (form && submitBtn) {
            form.addEventListener('submit', function(e) {
                // Show loading state
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Updating...';
                submitBtn.disabled = true;
                
                // Add a small delay to show the loading state
                setTimeout(() => {
                    // Let the form submit normally
                }, 100);
            });
        }
    });
</script>
@endsection
