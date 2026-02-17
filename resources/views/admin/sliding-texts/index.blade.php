@extends('layouts.admin')

@section('title', 'Sliding Texts')
@section('page-icon')
{!! '<i class="bi bi-text-left me-2"></i>' !!}
@endsection
@section('page-title', 'Sliding Texts')

@section('styles')
<style>
    /* Fix pagination size and layout issues */
    .pagination {
        display: flex;
        flex-wrap: wrap;
        list-style: none;
        padding: 0;
        margin: 1rem 0;
        gap: 0.25rem;
        justify-content: center;
    }

    .pagination .page-item {
        display: inline-block;
    }

    .pagination .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1;
        text-decoration: none;
        background-color: #fff;
        border: 1px solid #dee2e6;
        color: #0d6efd;
        min-width: 2.5rem;
        height: 2.5rem;
        transition: all 0.15s ease-in-out;
    }

    .pagination .page-link:hover {
        color: #0b5ed7;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }

    .pagination .page-item.active .page-link {
        z-index: 3;
        color: #fff;
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }

    /* Ensure table doesn't overflow */
    .table-responsive {
        max-width: 100%;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    /* Ensure buttons don't overflow */
    .btn-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }

    .btn-sm {
        padding: 0.375rem 0.625rem;
        font-size: 0.825rem;
        white-space: nowrap;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-text-left me-2"></i>
                    Sliding Texts Management
                </h5>
                <a href="{{ route('admin.sliding-texts.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-circle me-2"></i>
                    Add Sliding Text
                </a>
            </div>
            <div class="card-body">
                @if($slidingTexts->count() > 0)
                    <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
                        <table class="table table-hover" style="min-width: 100%; margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th>Text</th>
                                    <th>Status</th>
                                    <th>Speed</th>
                                    <th>Font Size</th>
                                    <th>Display Order</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($slidingTexts as $slidingText)
                                <tr>
                                    <td>
                                        <div style="max-width: 400px;">
                                            {{ Str::limit($slidingText->text, 100) }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $slidingText->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $slidingText->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $slidingText->animation_speed }}s</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $slidingText->font_size }}px</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">{{ $slidingText->display_order }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $slidingText->created_at->format('M j, Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.sliding-texts.edit', $slidingText) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.sliding-texts.destroy', $slidingText) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this sliding text?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <nav class="mt-4" role="navigation" aria-label="pagination">
                        {{ $slidingTexts->links('pagination::bootstrap-4') }}
                    </nav>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-text-left display-4 text-muted"></i>
                        <h4 class="mt-3 text-muted">No Sliding Texts Found</h4>
                        <p class="text-muted">Start by creating your first sliding text for the sidebar.</p>
                        <a href="{{ route('admin.sliding-texts.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>
                            Create First Sliding Text
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

