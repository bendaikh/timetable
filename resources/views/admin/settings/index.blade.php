@extends('layouts.admin')

@section('title', 'Settings')
@section('page-icon', '<i class="bi bi-gear me-2"></i>')
@section('page-title', 'Settings')

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
                @if($settings->count() > 0)
                    <form method="POST" action="{{ route('admin.settings.batch-update') }}">
                        @csrf
                        
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
                                                <form method="POST" action="{{ route('admin.settings.destroy', $setting) }}" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>
                                Update All Settings
                            </button>
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
