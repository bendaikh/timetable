@extends('layouts.admin')

@section('title', 'Prayer Times')
@section('page-icon', '<i class="bi bi-clock me-2"></i>')
@section('page-title', 'Prayer Times')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-clock me-2"></i>
                    Prayer Times Management
                </h5>
                <div class="btn-group">
                    <a href="{{ route('admin.prayer-times.import') }}" class="btn btn-outline-primary">
                        <i class="bi bi-cloud-download me-2"></i>
                        Import from Google Sheets
                    </a>
                    <a href="{{ route('admin.prayer-times.create') }}" class="btn btn-light">
                        <i class="bi bi-plus-circle me-2"></i>
                        Add Prayer Times
                    </a>
                </div>
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

                @if(session('import_errors') && count(session('import_errors')) > 0)
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Import completed with {{ count(session('import_errors')) }} errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach(session('import_errors') as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($prayerTimes->count() > 0)
                    <!-- Bulk Actions Bar -->
                    <div id="bulk-actions-bar" class="alert alert-info d-none mb-3" role="alert">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-check-square me-2"></i>
                                <span id="selected-count">0</span> prayer time(s) selected
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="select-all-btn">
                                    <i class="bi bi-check-all me-1"></i>
                                    Select All
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="select-none-btn">
                                    <i class="bi bi-x-square me-1"></i>
                                    Select None
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" id="bulk-delete-btn" disabled>
                                    <i class="bi bi-trash me-1"></i>
                                    Delete Selected
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="select-all-checkbox" class="form-check-input">
                                    </th>
                                    <th>Date</th>
                                    <th>Fajr</th>
                                    <th>Zohar</th>
                                    <th>Asr</th>
                                    <th>Maghrib</th>
                                    <th>Isha</th>
                                    <th>Sun Rise</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($prayerTimes as $prayerTime)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input prayer-time-checkbox" value="{{ $prayerTime->id }}">
                                    </td>
                                    <td>
                                        <strong>{{ $prayerTime->date->format('M j, Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $prayerTime->date->format('l') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ \Carbon\Carbon::parse($prayerTime->fajr)->format('h:i A') }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ \Carbon\Carbon::parse($prayerTime->zohar)->format('h:i A') }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">{{ \Carbon\Carbon::parse($prayerTime->asr)->format('h:i A') }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">{{ \Carbon\Carbon::parse($prayerTime->maghrib)->format('h:i A') }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark">{{ \Carbon\Carbon::parse($prayerTime->isha)->format('h:i A') }}</span>
                                    </td>
                                    <td>
                                        @if($prayerTime->sun_rise)
                                            <span class="badge bg-info">{{ \Carbon\Carbon::parse($prayerTime->sun_rise)->format('h:i A') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.prayer-times.show', $prayerTime) }}" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.prayer-times.edit', $prayerTime) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.prayer-times.destroy', $prayerTime) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this prayer time?')">
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
                    
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 pt-3 border-top">
                        <div class="text-muted mb-3 mb-md-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Showing {{ $prayerTimes->firstItem() }} to {{ $prayerTimes->lastItem() }} of {{ $prayerTimes->total() }} results
                        </div>
                        <nav aria-label="Prayer times pagination">
                            {{ $prayerTimes->links('pagination::bootstrap-4') }}
                        </nav>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-clock display-4 text-muted"></i>
                        <h4 class="mt-3 text-muted">No Prayer Times Found</h4>
                        <p class="text-muted">Start by adding prayer times for your masjid.</p>
                        <a href="{{ route('admin.prayer-times.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>
                            Add First Prayer Times
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const prayerTimeCheckboxes = document.querySelectorAll('.prayer-time-checkbox');
    const bulkActionsBar = document.getElementById('bulk-actions-bar');
    const selectedCountSpan = document.getElementById('selected-count');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const selectAllBtn = document.getElementById('select-all-btn');
    const selectNoneBtn = document.getElementById('select-none-btn');

    // Update bulk actions bar visibility and count
    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.prayer-time-checkbox:checked');
        const count = checkedBoxes.length;
        
        selectedCountSpan.textContent = count;
        
        if (count > 0) {
            bulkActionsBar.classList.remove('d-none');
            bulkDeleteBtn.disabled = false;
        } else {
            bulkActionsBar.classList.add('d-none');
            bulkDeleteBtn.disabled = true;
        }
        
        // Update select all checkbox state
        if (count === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (count === prayerTimeCheckboxes.length) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else {
            selectAllCheckbox.indeterminate = true;
        }
    }

    // Select all checkbox functionality
    selectAllCheckbox.addEventListener('change', function() {
        prayerTimeCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    // Individual checkbox functionality
    prayerTimeCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    // Select all button
    selectAllBtn.addEventListener('click', function() {
        prayerTimeCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
        updateBulkActions();
    });

    // Select none button
    selectNoneBtn.addEventListener('click', function() {
        prayerTimeCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
        updateBulkActions();
    });

    // Bulk delete functionality
    bulkDeleteBtn.addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('.prayer-time-checkbox:checked');
        const selectedIds = Array.from(checkedBoxes).map(checkbox => checkbox.value);
        
        if (selectedIds.length === 0) {
            alert('Please select at least one prayer time to delete.');
            return;
        }
        
        const confirmMessage = `Are you sure you want to delete ${selectedIds.length} prayer time(s)? This action cannot be undone.`;
        
        if (confirm(confirmMessage)) {
            // Create a form to submit the bulk delete request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.prayer-times.bulk-delete") }}';
            
            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Add method override
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            // Add selected IDs
            selectedIds.forEach(id => {
                const idField = document.createElement('input');
                idField.type = 'hidden';
                idField.name = 'prayer_time_ids[]';
                idField.value = id;
                form.appendChild(idField);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    });

    // Initialize bulk actions state
    updateBulkActions();
});
</script>
@endsection
