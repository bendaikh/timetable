@extends('layouts.admin')

@section('title', 'View Hadeeth')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">View Hadeeth</h1>
                <div>
                    <a href="{{ route('admin.hadeeths.edit', $hadeeth) }}" class="btn btn-primary me-2">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('admin.hadeeths.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Hadeeths
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-book me-2"></i>
                        Hadeeth Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Arabic Text -->
                            <div class="mb-4">
                                <h6>Arabic Text:</h6>
                                <div class="p-4 bg-light rounded border" 
                                     style="font-family: 'Amiri', 'Times New Roman', serif; font-size: 20px; 
                                            text-align: right; direction: rtl; line-height: 1.8; min-height: 150px;">
                                    {{ $hadeeth->arabic_text }}
                                </div>
                            </div>

                            <!-- English Translation -->
                            <div class="mb-4">
                                <h6>English Translation:</h6>
                                <div class="p-4 bg-white rounded border" 
                                     style="font-size: 16px; line-height: 1.6; min-height: 150px;">
                                    {{ $hadeeth->english_translation }}
                                </div>
                            </div>

                            <!-- Reference -->
                            <div class="mb-4">
                                <h6>Reference:</h6>
                                <div class="p-3 bg-info bg-opacity-10 rounded border-start border-info border-4">
                                    <i class="bi bi-bookmark-fill me-2 text-info"></i>
                                    <strong>{{ $hadeeth->reference }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">Hadeeth Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge {{ $hadeeth->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $hadeeth->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Display Order:</strong></td>
                                            <td>
                                                <span class="badge bg-primary">{{ $hadeeth->display_order }}</span>
                                            </td>
                                        </tr>
                                        @if($hadeeth->display_date)
                                        <tr>
                                            <td><strong>Display Date:</strong></td>
                                            <td>{{ $hadeeth->display_date->format('M j, Y') }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td><strong>Created:</strong></td>
                                            <td>{{ $hadeeth->created_at->format('M j, Y g:i A') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Updated:</strong></td>
                                            <td>{{ $hadeeth->updated_at->format('M j, Y g:i A') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Display Preview -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Display Preview</h6>
                                </div>
                                <div class="card-body">
                                    <div class="border rounded p-3 bg-white" style="min-height: 200px;">
                                        <div class="text-center">
                                            <div style="font-family: 'Amiri', 'Times New Roman', serif; font-size: 18px; 
                                                        text-align: right; direction: rtl; margin-bottom: 15px; 
                                                        padding: 10px; background-color: #f8f9fa; border-radius: 5px;">
                                                {{ Str::limit($hadeeth->arabic_text, 100) }}
                                            </div>
                                            <div style="font-size: 14px; margin-bottom: 10px; 
                                                        padding: 8px; background-color: #e9ecef; border-radius: 5px;">
                                                {{ Str::limit($hadeeth->english_translation, 150) }}
                                            </div>
                                            <small class="text-muted">{{ $hadeeth->reference }}</small>
                                        </div>
                                    </div>
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
