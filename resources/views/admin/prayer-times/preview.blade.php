@extends('layouts.admin')

@section('title', 'Preview Prayer Times Import')
@section('page-icon', '<i class="bi bi-eye me-2"></i>')
@section('page-title', 'Preview Import')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header card-header-custom">
                <h5 class="mb-0">
                    <i class="bi bi-eye me-2"></i>
                    Preview Prayer Times Import
                </h5>
            </div>
            <div class="card-body">
                @if(!empty($errors))
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Data Parsing Errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if(empty($prayerTimes))
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>
                    No valid prayer times data found. Please check your Google Sheets format.
                </div>
                @else
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    Found {{ count($prayerTimes) }} valid prayer times entries.
                    @if(!empty($existingDates))
                        <br><strong>Note:</strong> {{ count($existingDates) }} dates already exist in the database.
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Fajr</th>
                                <th>Zohar</th>
                                <th>Asr</th>
                                <th>Maghrib</th>
                                <th>Isha</th>
                                <th>Sun Rise</th>
                                <th>Jumah 1</th>
                                <th>Jumah 2</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prayerTimes as $prayerTime)
                            <tr class="{{ in_array($prayerTime['date'], $existingDates) ? 'table-warning' : '' }}">
                                <td>
                                    <strong>{{ \Carbon\Carbon::parse($prayerTime['date'])->format('M j, Y') }}</strong>
                                    <br>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($prayerTime['date'])->format('l') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ \Carbon\Carbon::parse($prayerTime['fajr'])->format('h:i A') }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">{{ \Carbon\Carbon::parse($prayerTime['zohar'])->format('h:i A') }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ \Carbon\Carbon::parse($prayerTime['asr'])->format('h:i A') }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-danger">{{ \Carbon\Carbon::parse($prayerTime['maghrib'])->format('h:i A') }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-dark">{{ \Carbon\Carbon::parse($prayerTime['isha'])->format('h:i A') }}</span>
                                </td>
                                <td>
                                    @if($prayerTime['sun_rise'])
                                        <span class="badge bg-info">{{ \Carbon\Carbon::parse($prayerTime['sun_rise'])->format('h:i A') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($prayerTime['jumah_1'])
                                        <span class="badge bg-secondary">{{ \Carbon\Carbon::parse($prayerTime['jumah_1'])->format('h:i A') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($prayerTime['jumah_2'])
                                        <span class="badge bg-secondary">{{ \Carbon\Carbon::parse($prayerTime['jumah_2'])->format('h:i A') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if(in_array($prayerTime['date'], $existingDates))
                                        <span class="badge bg-warning">Exists</span>
                                    @else
                                        <span class="badge bg-success">New</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('admin.prayer-times.import') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Back to Import
                    </a>
                    
                    @if(!empty($prayerTimes))
                    <form method="POST" action="{{ route('admin.prayer-times.import.process') }}" class="d-inline" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="import_type" value="{{ $request->import_type }}">
                        @if($request->import_type === 'url')
                            <input type="hidden" name="google_sheets_url" value="{{ $request->google_sheets_url }}">
                        @else
                            <input type="file" name="google_sheets_file" style="display: none;" id="hidden_file_input">
                        @endif
                        <input type="hidden" name="range" value="{{ $request->range }}">
                        <div class="form-check d-inline-block me-3">
                            <input class="form-check-input" type="checkbox" id="overwrite_existing" name="overwrite_existing" value="1">
                            <label class="form-check-label" for="overwrite_existing">
                                Overwrite existing entries
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-download me-2"></i>
                            Import {{ count($prayerTimes) }} Prayer Times
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

