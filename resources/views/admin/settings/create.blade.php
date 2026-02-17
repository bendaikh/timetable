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
                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-shield-lock me-2"></i>
                    Developer-only settings are hidden from the admin interface.
                </div>
                <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>
                    Back to Settings
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
