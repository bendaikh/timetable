@extends('layouts.admin')

@section('title', 'Hadeeths')
@section('page-icon')
{!! '<i class="bi bi-book me-2"></i>' !!}
@endsection
@section('page-title', 'Hadeeths')

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
                    <i class="bi bi-book me-2"></i>
                    Hadeeths Management
                </h5>
                <a href="{{ route('admin.hadeeths.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-circle me-2"></i>
                    Add Hadeeth
                </a>
            </div>
            <div class="card-body">
                @if($hadeeths->count() > 0)
                    <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
                        <table class="table table-hover" style="min-width: 100%; margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Arabic Text</th>
                                    <th>English Translation</th>
                                    <th>Reference</th>
                                    <th>Status</th>
                                    <th>Display Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hadeeths as $hadeeth)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">{{ $hadeeth->display_order }}</span>
                                    </td>
                                    <td>
                                        <div style="max-width: 200px; direction: rtl; font-family: 'Amiri', serif;">
                                            {{ Str::limit($hadeeth->arabic_text, 50) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div style="max-width: 250px;">
                                            {{ Str::limit($hadeeth->english_translation, 60) }}
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $hadeeth->reference }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $hadeeth->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $hadeeth->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($hadeeth->display_date)
                                            <small class="text-muted">{{ $hadeeth->display_date->format('M j, Y') }}</small>
                                        @else
                                            <span class="text-muted">Any day</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.hadeeths.show', $hadeeth) }}" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.hadeeths.edit', $hadeeth) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.hadeeths.destroy', $hadeeth) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this hadeeth?')">
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
                        {{ $hadeeths->links('pagination::bootstrap-4') }}
                    </nav>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-book display-4 text-muted"></i>
                        <h4 class="mt-3 text-muted">No Hadeeths Found</h4>
                        <p class="text-muted">Start by adding hadeeths to display on your timetable.</p>
                        <a href="{{ route('admin.hadeeths.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>
                            Add First Hadeeth
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
