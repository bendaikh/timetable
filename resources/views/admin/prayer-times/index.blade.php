@extends('layouts.admin')

@section('title', 'Prayer Times')
@section('page-icon')
{!! '<i class="bi bi-clock me-2"></i>' !!}
@endsection
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
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.prayer-times.import') }}" class="btn btn-outline-light border-2">
                        <i class="bi bi-cloud-download me-2"></i>
                        Import from Google Sheets
                    </a>
                    <a href="{{ route('admin.prayer-times.create') }}" class="btn btn-light">
                        <i class="bi bi-plus-circle me-2"></i>
                        Add Prayer Times
                    </a>
                    @if($prayerTimes->total() > 0)
                    <button type="button" class="btn btn-danger" id="delete-all-btn" onclick="deleteAllRecords()">
                        <i class="bi bi-trash me-2"></i>
                        Delete All
                    </button>
                    @endif
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
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="mb-2 mb-md-0">
                                <i class="bi bi-check-square me-2"></i>
                                <span id="selected-count">0</span> prayer time(s) selected
                                <span id="select-all-pages-link" class="ms-2 d-none">
                                    | <a href="#" id="select-all-pages-btn" class="text-info">Select all {{ $prayerTimes->total() }} records</a>
                                </span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="select-all-btn">
                                    <i class="bi bi-check-all me-1"></i>
                                    Select Page
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="select-none-btn">
                                    <i class="bi bi-x-square me-1"></i>
                                    Clear
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
// Delete all records function
function deleteAllRecords() {
    const totalRecords = {{ $prayerTimes->total() }};
    
    // Show confirmation dialog with count
    const confirmMessage = `⚠️ WARNING!\n\nYou are about to PERMANENTLY DELETE all ${totalRecords} prayer time records.\n\nThis action CANNOT be undone.\n\nAre you absolutely sure?`;
    
    if (confirm(confirmMessage)) {
        // Double confirmation for safety
        if (confirm(`Please confirm again: Delete all ${totalRecords} records?`)) {
            // Create and submit the form
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
            
            // Add delete all flag
            const deleteAllField = document.createElement('input');
            deleteAllField.type = 'hidden';
            deleteAllField.name = 'delete_all';
            deleteAllField.value = 'true';
            form.appendChild(deleteAllField);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const prayerTimeCheckboxes = document.querySelectorAll('.prayer-time-checkbox');
    const bulkActionsBar = document.getElementById('bulk-actions-bar');
    const selectedCountSpan = document.getElementById('selected-count');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const selectAllBtn = document.getElementById('select-all-btn');
    const selectNoneBtn = document.getElementById('select-none-btn');
    const selectAllPagesLink = document.getElementById('select-all-pages-link');
    const selectAllPagesBtn = document.getElementById('select-all-pages-btn');
    
    // Store selected IDs across pages in sessionStorage
    const storageKey = 'prayerTimesSelectedIds';
    let selectedIds = JSON.parse(sessionStorage.getItem(storageKey) || '[]');
    let selectAllPages = sessionStorage.getItem('prayerTimesSelectAllPages') === 'true';

    // Initialize checkboxes based on stored selection
    function initializeCheckboxes() {
        prayerTimeCheckboxes.forEach(checkbox => {
            checkbox.checked = selectedIds.includes(parseInt(checkbox.value));
        });
    }

    // Save selected IDs to storage
    function saveSelection() {
        sessionStorage.setItem(storageKey, JSON.stringify(selectedIds));
        sessionStorage.setItem('prayerTimesSelectAllPages', selectAllPages);
    }

    // Update bulk actions bar visibility and count
    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.prayer-time-checkbox:checked');
        const count = selectAllPages ? {{ $prayerTimes->total() }} : checkedBoxes.length;
        
        selectedCountSpan.textContent = count;
        
        // Show "select all records" link if we have selected items but haven't selected all pages
        if (checkedBoxes.length > 0 && checkedBoxes.length < prayerTimeCheckboxes.length && !selectAllPages) {
            selectAllPagesLink.classList.remove('d-none');
        } else {
            selectAllPagesLink.classList.add('d-none');
        }
        
        if (count > 0) {
            bulkActionsBar.classList.remove('d-none');
            bulkDeleteBtn.disabled = false;
        } else {
            bulkActionsBar.classList.add('d-none');
            bulkDeleteBtn.disabled = true;
            selectAllPagesLink.classList.add('d-none');
        }
        
        // Update select all checkbox state
        if (checkedBoxes.length === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (checkedBoxes.length === prayerTimeCheckboxes.length) {
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
            if (this.checked) {
                if (!selectedIds.includes(parseInt(checkbox.value))) {
                    selectedIds.push(parseInt(checkbox.value));
                }
            } else {
                selectedIds = selectedIds.filter(id => id !== parseInt(checkbox.value));
            }
        });
        selectAllPages = false;
        saveSelection();
        updateBulkActions();
    });

    // Individual checkbox functionality
    prayerTimeCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const id = parseInt(this.value);
            if (this.checked) {
                if (!selectedIds.includes(id)) {
                    selectedIds.push(id);
                }
            } else {
                selectedIds = selectedIds.filter(x => x !== id);
                selectAllPages = false;
            }
            saveSelection();
            updateBulkActions();
        });
    });

    // Select all on current page button
    selectAllBtn.addEventListener('click', function() {
        prayerTimeCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
            const id = parseInt(checkbox.value);
            if (!selectedIds.includes(id)) {
                selectedIds.push(id);
            }
        });
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
        selectAllPages = false;
        saveSelection();
        updateBulkActions();
    });

    // Select all records across all pages
    if (selectAllPagesBtn) {
        selectAllPagesBtn.addEventListener('click', function(e) {
            e.preventDefault();
            selectAllPages = true;
            selectedIds = []; // Clear individual selections
            saveSelection();
            selectedCountSpan.textContent = {{ $prayerTimes->total() }};
            selectAllPagesLink.classList.add('d-none');
            updateBulkActions();
        });
    }

    // Select none button
    selectNoneBtn.addEventListener('click', function() {
        prayerTimeCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
        selectedIds = [];
        selectAllPages = false;
        saveSelection();
        updateBulkActions();
    });

    // Bulk delete functionality
    bulkDeleteBtn.addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('.prayer-time-checkbox:checked');
        let idsToDelete = [];
        
        if (selectAllPages) {
            // Delete all records
            idsToDelete = 'all';
        } else {
            idsToDelete = Array.from(checkedBoxes).map(checkbox => checkbox.value);
        }
        
        if ((selectAllPages && {{ $prayerTimes->total() }} === 0) || 
            (!selectAllPages && idsToDelete.length === 0)) {
            alert('Please select at least one prayer time to delete.');
            return;
        }
        
        const deleteCount = selectAllPages ? {{ $prayerTimes->total() }} : idsToDelete.length;
        const confirmMessage = `Are you sure you want to delete ${deleteCount} prayer time(s)? This action cannot be undone.`;
        
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
            
            // Add selected IDs or delete all flag
            if (selectAllPages) {
                const deleteAllField = document.createElement('input');
                deleteAllField.type = 'hidden';
                deleteAllField.name = 'delete_all';
                deleteAllField.value = 'true';
                form.appendChild(deleteAllField);
            } else {
                idsToDelete.forEach(id => {
                    const idField = document.createElement('input');
                    idField.type = 'hidden';
                    idField.name = 'prayer_time_ids[]';
                    idField.value = id;
                    form.appendChild(idField);
                });
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    });

    // Initialize
    initializeCheckboxes();
    updateBulkActions();
});
</script>
@endsection
