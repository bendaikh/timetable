@extends('layouts.admin')

@section('title', 'View Announcement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">View Announcement</h1>
                <div>
                    <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-primary me-2">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Announcements
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-megaphone me-2"></i>
                        {{ $announcement->title }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <h6>Content:</h6>
                                <div class="p-3 bg-light rounded">
                                    <p class="mb-0">{{ $announcement->content }}</p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6>Preview:</h6>
                                <div class="p-3 rounded" 
                                     style="background-color: {{ $announcement->background_color }}; color: {{ $announcement->text_color }}; 
                                            font-size: {{ $announcement->font_size }}px; min-height: 100px; 
                                            display: flex; align-items: center; justify-content: center;">
                                    <div class="text-center">
                                        <div style="font-size: {{ $announcement->font_size }}px; 
                                                    color: {{ $announcement->text_color }};
                                                    background-color: {{ $announcement->background_color }};
                                                    padding: 10px; border-radius: 5px;">
                                            {{ $announcement->content }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">Announcement Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge {{ $announcement->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $announcement->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Display Duration:</strong></td>
                                            <td>{{ $announcement->display_duration }} seconds</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Font Size:</strong></td>
                                            <td>{{ $announcement->font_size }}px</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Scroll Speed:</strong></td>
                                            <td>{{ $announcement->scroll_speed }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Text Color:</strong></td>
                                            <td>
                                                <span class="d-inline-block me-2" 
                                                      style="width: 20px; height: 20px; background-color: {{ $announcement->text_color }}; border: 1px solid #ccc; border-radius: 3px;"></span>
                                                {{ $announcement->text_color }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Background:</strong></td>
                                            <td>
                                                <span class="d-inline-block me-2" 
                                                      style="width: 20px; height: 20px; background-color: {{ $announcement->background_color }}; border: 1px solid #ccc; border-radius: 3px;"></span>
                                                {{ $announcement->background_color }}
                                            </td>
                                        </tr>
                                        @if($announcement->auto_repeat)
                                        <tr>
                                            <td><strong>Auto Repeat:</strong></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <i class="bi bi-arrow-repeat me-1"></i>
                                                    {{ implode(', ', $announcement->repeat_days ?? []) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endif
                                        @if($announcement->start_date)
                                        <tr>
                                            <td><strong>Start Date:</strong></td>
                                            <td>{{ $announcement->start_date->format('M j, Y g:i A') }}</td>
                                        </tr>
                                        @endif
                                        @if($announcement->end_date)
                                        <tr>
                                            <td><strong>End Date:</strong></td>
                                            <td>{{ $announcement->end_date->format('M j, Y g:i A') }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td><strong>Created:</strong></td>
                                            <td>{{ $announcement->created_at->format('M j, Y g:i A') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Updated:</strong></td>
                                            <td>{{ $announcement->updated_at->format('M j, Y g:i A') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
