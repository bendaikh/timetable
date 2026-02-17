@extends('layouts.admin')

@section('title', 'Settings')
@section('page-icon')
{!! '<i class="bi bi-gear me-2"></i>' !!}
@endsection
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
                
                
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-shield-lock me-2"></i>
                    Developer-only settings are hidden from the admin interface.
                </div>

                <form method="POST" action="{{ route('admin.settings.batch-update') }}" enctype="multipart/form-data" id="settings-form">
                    @csrf

                    <div class="text-center mb-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle me-2"></i>
                            Update Logo
                        </button>
                    </div>

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
                                                <div class="d-flex justify-content-center align-items-center mb-2">
                                                    <p class="mb-0"><strong>Current Logo:</strong></p>
                                                    @if(Setting::get('logo_path'))
                                                        <button type="button" 
                                                                class="btn btn-sm btn-danger ms-2" 
                                                                onclick="deleteLogo()" 
                                                                title="Delete Logo">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                                <div id="logo-preview">
                                                    @if(Setting::get('logo_path'))
                                                        <img src="{{ app()->environment('production') ? url('public/storage/' . Setting::get('logo_path')) : asset('storage/' . Setting::get('logo_path')) }}" 
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
                </form>
            </div>
        </div>
    </div>
</div>

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

    // Delete logo function
    function deleteLogo() {
        if (confirm('Are you sure you want to delete the current logo?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.settings.delete-logo") }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            form.appendChild(csrfToken);
            document.body.appendChild(form);
            form.submit();
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
