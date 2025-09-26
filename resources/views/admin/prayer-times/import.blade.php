@extends('layouts.admin')

@section('title', 'Import Prayer Times from Google Sheets')
@section('page-icon', '<i class="bi bi-cloud-download me-2"></i>')
@section('page-title', 'Import Prayer Times')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header card-header-custom">
                <h5 class="mb-0">
                    <i class="bi bi-cloud-download me-2"></i>
                    Import Prayer Times from Google Sheets
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Instructions:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>File Upload (Recommended):</strong> Download your Google Sheet as CSV or Excel and upload it directly</li>
                        <li><strong>URL Import:</strong> Make sure your Google Sheet is publicly accessible (Anyone with the link can view)</li>
                        <li>Your sheet should have columns with prayer time information (the system will auto-detect the correct columns)</li>
                        <li>Supported column names: Date, Fajr Beginning/Jamaat, Zuhr Beginning/Jamaat, Asr Beginning/Jamaat, Maghrib Beginning/Jamaat, Isha Beginning/Jamaat, Sunrise, Jumma 1/2</li>
                        <li>Date format can be: YYYY-MM-DD, MM/DD/YYYY, DD/MM/YYYY, etc.</li>
                        <li>Time format can be: HH:MM, HH:MM:SS, 12:00 PM, etc.</li>
                        <li>Sunrise, Jumma 1, and Jumma 2 are optional</li>
                    </ul>
                </div>

                <div class="alert alert-success">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                    <strong>Your Google Sheets Format:</strong>
                    <p class="mb-2 mt-2">The system supports your exact header format:</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Fajr Beginning</th>
                                    <th>Fajr Jamaat</th>
                                    <th>Sunrise</th>
                                    <th>Zuhr Beginning</th>
                                    <th>Zuhr Jamaat</th>
                                    <th>Asr Beginning</th>
                                    <th>Asr Jamaat</th>
                                    <th>Maghrib Beginning</th>
                                    <th>Maghrib Jamaat</th>
                                    <th>Isha Beginning</th>
                                    <th>Isha Jamaat</th>
                                    <th>Jumma 1</th>
                                    <th>Jumma 2</th>
                                    <th>hijri_date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>2024-01-01</td>
                                    <td>05:30</td>
                                    <td>05:45</td>
                                    <td>06:45</td>
                                    <td>12:15</td>
                                    <td>12:30</td>
                                    <td>15:45</td>
                                    <td>16:00</td>
                                    <td>18:20</td>
                                    <td>18:25</td>
                                    <td>19:50</td>
                                    <td>20:05</td>
                                    <td>12:30</td>
                                    <td>13:30</td>
                                    <td>15 Jumada al-Awwal 1445</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-2 mb-0"><strong>Note:</strong> The system will automatically use the "Beginning" times for each prayer. The "Jamaat" times and "hijri_date" are ignored but won't cause errors.</p>
                </div>

                <form method="POST" action="{{ route('admin.prayer-times.import.process') }}" id="importForm" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Import Method <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="import_type" id="import_url" value="url" {{ old('import_type', 'file') === 'url' ? 'checked' : '' }}>
                            <label class="form-check-label" for="import_url">
                                Import from Google Sheets URL
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="import_type" id="import_file" value="file" {{ old('import_type', 'file') === 'file' ? 'checked' : '' }}>
                            <label class="form-check-label" for="import_file">
                                Upload Google Sheets File
                            </label>
                        </div>
                    </div>

                    <div class="mb-4" id="url_section" style="display: none;">
                        <label for="google_sheets_url" class="form-label fw-bold">
                            Google Sheets URL <span class="text-danger">*</span>
                        </label>
                        <input type="url" 
                               class="form-control @error('google_sheets_url') is-invalid @enderror" 
                               id="google_sheets_url" 
                               name="google_sheets_url" 
                               value="{{ old('google_sheets_url') }}" 
                               placeholder="https://docs.google.com/spreadsheets/d/...">
                        @error('google_sheets_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Paste the full Google Sheets URL here. Make sure the sheet is publicly accessible.
                        </div>
                    </div>

                    <div class="mb-4" id="file_section">
                        <label for="google_sheets_file" class="form-label fw-bold">
                            Upload Google Sheets File <span class="text-danger">*</span>
                        </label>
                        <input type="file" 
                               class="form-control @error('google_sheets_file') is-invalid @enderror" 
                               id="google_sheets_file" 
                               name="google_sheets_file" 
                               accept=".csv,.xlsx,.xls">
                        @error('google_sheets_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Upload a CSV, XLS, or XLSX file. You can download your Google Sheet as:
                            <ul class="mb-0 mt-1">
                                <li><strong>CSV:</strong> File → Download → Comma-separated values (.csv)</li>
                                <li><strong>Excel:</strong> File → Download → Microsoft Excel (.xlsx)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="range" class="form-label fw-bold">Data Range</label>
                        <input type="text" 
                               class="form-control @error('range') is-invalid @enderror" 
                               id="range" 
                               name="range" 
                               value="{{ old('range', 'A:Z') }}" 
                               placeholder="A:Z">
                        @error('range')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Specify the range to import (e.g., A:Z, A1:J100). Leave as A:Z to import all data.
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="overwrite_existing" 
                                   name="overwrite_existing" 
                                   value="1"
                                   {{ old('overwrite_existing') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="overwrite_existing">
                                Overwrite existing prayer times
                            </label>
                        </div>
                        <div class="form-text">
                            If checked, existing prayer times for the same dates will be updated. Otherwise, they will be skipped.
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.prayer-times.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>
                            Back to Prayer Times
                        </a>
                        <div>
                            <button type="button" class="btn btn-outline-primary me-2" id="previewBtn">
                                <i class="bi bi-eye me-2"></i>
                                Preview Data
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-cloud-download me-2"></i>
                                Import Prayer Times
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="card shadow mt-4">
            <div class="card-header card-header-custom bg-warning">
                <h5 class="mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Import Errors
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <strong>The following errors occurred during import:</strong>
                </div>
                <ul class="list-group">
                    @foreach(session('import_errors') as $error)
                    <li class="list-group-item">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const previewBtn = document.getElementById('previewBtn');
    const importForm = document.getElementById('importForm');
    const urlInput = document.getElementById('google_sheets_url');
    const fileInput = document.getElementById('google_sheets_file');
    const rangeInput = document.getElementById('range');
    const importTypeRadios = document.querySelectorAll('input[name="import_type"]');
    const urlSection = document.getElementById('url_section');
    const fileSection = document.getElementById('file_section');

    // Handle import type switching
    importTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'url') {
                urlSection.style.display = 'block';
                fileSection.style.display = 'none';
                fileInput.required = false;
                urlInput.required = true;
            } else {
                urlSection.style.display = 'none';
                fileSection.style.display = 'block';
                urlInput.required = false;
                fileInput.required = true;
            }
        });
    });

    // Initialize form state
    const selectedType = document.querySelector('input[name="import_type"]:checked').value;
    if (selectedType === 'url') {
        urlSection.style.display = 'block';
        fileSection.style.display = 'none';
        fileInput.required = false;
        urlInput.required = true;
    } else {
        urlSection.style.display = 'none';
        fileSection.style.display = 'block';
        urlInput.required = false;
        fileInput.required = true;
    }

    previewBtn.addEventListener('click', function() {
        const selectedType = document.querySelector('input[name="import_type"]:checked').value;
        
        if (selectedType === 'url' && !urlInput.value.trim()) {
            alert('Please enter a Google Sheets URL first.');
            return;
        }
        
        if (selectedType === 'file' && !fileInput.files.length) {
            alert('Please select a file to upload first.');
            return;
        }

        // Create a temporary form for preview
        const previewForm = document.createElement('form');
        previewForm.method = 'POST';
        previewForm.action = '{{ route("admin.prayer-times.preview") }}';
        previewForm.enctype = 'multipart/form-data';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        previewForm.appendChild(csrfToken);

        // Add import type
        const importTypeField = document.createElement('input');
        importTypeField.type = 'hidden';
        importTypeField.name = 'import_type';
        importTypeField.value = selectedType;
        previewForm.appendChild(importTypeField);

        if (selectedType === 'url') {
            // Add URL
            const urlField = document.createElement('input');
            urlField.type = 'hidden';
            urlField.name = 'google_sheets_url';
            urlField.value = urlInput.value;
            previewForm.appendChild(urlField);
        } else {
            // Add file
            const fileField = document.createElement('input');
            fileField.type = 'file';
            fileField.name = 'google_sheets_file';
            fileField.files = fileInput.files;
            previewForm.appendChild(fileField);
        }

        // Add range
        const rangeField = document.createElement('input');
        rangeField.type = 'hidden';
        rangeField.name = 'range';
        rangeField.value = rangeInput.value;
        previewForm.appendChild(rangeField);

        document.body.appendChild(previewForm);
        previewForm.submit();
    });

    // Validate URL format
    urlInput.addEventListener('blur', function() {
        const url = this.value.trim();
        if (url && !url.includes('docs.google.com/spreadsheets')) {
            this.classList.add('is-invalid');
            let feedback = this.parentNode.querySelector('.invalid-feedback');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                this.parentNode.appendChild(feedback);
            }
            feedback.textContent = 'Please enter a valid Google Sheets URL';
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Validate file format
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            const allowedExtensions = ['.csv', '.xls', '.xlsx'];
            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
            
            if (!allowedTypes.includes(file.type) && !allowedExtensions.includes(fileExtension)) {
                this.classList.add('is-invalid');
                let feedback = this.parentNode.querySelector('.invalid-feedback');
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    this.parentNode.appendChild(feedback);
                }
                feedback.textContent = 'Please select a valid CSV, XLS, or XLSX file';
            } else {
                this.classList.remove('is-invalid');
            }
        }
    });
});
</script>
@endsection

