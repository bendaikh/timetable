@extends('layouts.admin')

@section('title', 'Hadeeths')
@section('page-icon', '<i class="bi bi-book me-2"></i>')
@section('page-title', 'Hadeeths')

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
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                    
                    <div class="d-flex justify-content-center">
                        {{ $hadeeths->links() }}
                    </div>
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
