@extends('layouts.admin')

@section('title', 'Sliding Texts')
@section('page-icon')
{!! '<i class="bi bi-text-left me-2"></i>' !!}
@endsection
@section('page-title', 'Sliding Texts')

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
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                    
                    <div class="d-flex justify-content-center">
                        {{ $slidingTexts->links() }}
                    </div>
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

