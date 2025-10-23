@extends('layouts.admin')

@section('title', 'View Prayer Time')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">View Prayer Time</h1>
                <div>
                    <a href="{{ route('admin.prayer-times.edit', $prayerTime) }}" class="btn btn-primary me-2">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('admin.prayer-times.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Prayer Times
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar3 me-2"></i>
                        Prayer Times for {{ \Carbon\Carbon::parse($prayerTime->date)->format('l, F j, Y') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <h6 class="mb-3">Daily Prayer Times:</h6>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>Prayer</th>
                                                <th>Beginning Time</th>
                                                <th>Adhan Time</th>
                                                <th>Jamaat (Congregation)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong><i class="bi bi-sunrise me-2"></i>Fajr</strong></td>
                                                <td class="h5 mb-0">{{ \Carbon\Carbon::parse($prayerTime->fajr)->format('g:i A') }}</td>
                                                <td class="h5 mb-0">
                                                    @if($prayerTime->fajr_adhan)
                                                        <span class="badge bg-info">{{ \Carbon\Carbon::parse($prayerTime->fajr_adhan)->format('g:i A') }}</span>
                                                    @else
                                                        <span class="text-muted">Not set</span>
                                                    @endif
                                                </td>
                                                <td class="h5 mb-0">
                                                    @if($prayerTime->fajr_jamaat)
                                                        <span class="badge bg-success">{{ \Carbon\Carbon::parse($prayerTime->fajr_jamaat)->format('g:i A') }}</span>
                                                    @else
                                                        <span class="text-muted">Not set</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if($prayerTime->sun_rise)
                                            <tr class="table-light">
                                                <td><strong><i class="bi bi-sun me-2"></i>Sunrise</strong></td>
                                                <td class="h5 mb-0">{{ \Carbon\Carbon::parse($prayerTime->sun_rise)->format('g:i A') }}</td>
                                                <td class="text-muted">-</td>
                                                <td class="text-muted">-</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td><strong><i class="bi bi-brightness-high me-2"></i>Zohar</strong></td>
                                                <td class="h5 mb-0">{{ \Carbon\Carbon::parse($prayerTime->zohar)->format('g:i A') }}</td>
                                                <td class="h5 mb-0">
                                                    @if($prayerTime->zohar_adhan)
                                                        <span class="badge bg-info">{{ \Carbon\Carbon::parse($prayerTime->zohar_adhan)->format('g:i A') }}</span>
                                                    @else
                                                        <span class="text-muted">Not set</span>
                                                    @endif
                                                </td>
                                                <td class="h5 mb-0">
                                                    @if($prayerTime->zohar_jamaat)
                                                        <span class="badge bg-success">{{ \Carbon\Carbon::parse($prayerTime->zohar_jamaat)->format('g:i A') }}</span>
                                                    @else
                                                        <span class="text-muted">Not set</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong><i class="bi bi-cloud-sun me-2"></i>Asr</strong></td>
                                                <td class="h5 mb-0">{{ \Carbon\Carbon::parse($prayerTime->asr)->format('g:i A') }}</td>
                                                <td class="h5 mb-0">
                                                    @if($prayerTime->asr_adhan)
                                                        <span class="badge bg-info">{{ \Carbon\Carbon::parse($prayerTime->asr_adhan)->format('g:i A') }}</span>
                                                    @else
                                                        <span class="text-muted">Not set</span>
                                                    @endif
                                                </td>
                                                <td class="h5 mb-0">
                                                    @if($prayerTime->asr_jamaat)
                                                        <span class="badge bg-success">{{ \Carbon\Carbon::parse($prayerTime->asr_jamaat)->format('g:i A') }}</span>
                                                    @else
                                                        <span class="text-muted">Not set</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong><i class="bi bi-sunset me-2"></i>Maghrib</strong></td>
                                                <td class="h5 mb-0">{{ \Carbon\Carbon::parse($prayerTime->maghrib)->format('g:i A') }}</td>
                                                <td class="h5 mb-0">
                                                    @if($prayerTime->maghrib_adhan)
                                                        <span class="badge bg-info">{{ \Carbon\Carbon::parse($prayerTime->maghrib_adhan)->format('g:i A') }}</span>
                                                    @else
                                                        <span class="text-muted">Not set</span>
                                                    @endif
                                                </td>
                                                <td class="h5 mb-0">
                                                    @if($prayerTime->maghrib_jamaat)
                                                        <span class="badge bg-success">{{ \Carbon\Carbon::parse($prayerTime->maghrib_jamaat)->format('g:i A') }}</span>
                                                    @else
                                                        <span class="text-muted">Not set</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong><i class="bi bi-moon-stars me-2"></i>Isha</strong></td>
                                                <td class="h5 mb-0">{{ \Carbon\Carbon::parse($prayerTime->isha)->format('g:i A') }}</td>
                                                <td class="h5 mb-0">
                                                    @if($prayerTime->isha_adhan)
                                                        <span class="badge bg-info">{{ \Carbon\Carbon::parse($prayerTime->isha_adhan)->format('g:i A') }}</span>
                                                    @else
                                                        <span class="text-muted">Not set</span>
                                                    @endif
                                                </td>
                                                <td class="h5 mb-0">
                                                    @if($prayerTime->isha_jamaat)
                                                        <span class="badge bg-success">{{ \Carbon\Carbon::parse($prayerTime->isha_jamaat)->format('g:i A') }}</span>
                                                    @else
                                                        <span class="text-muted">Not set</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            @if($prayerTime->jumah_1 || $prayerTime->jumah_2)
                            <div class="mb-4">
                                <h6 class="mb-3">Jummah Prayer Times:</h6>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead class="table-success">
                                            <tr>
                                                <th>Prayer</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($prayerTime->jumah_1)
                                            <tr>
                                                <td><strong><i class="bi bi-people me-2"></i>Jummah 1</strong></td>
                                                <td class="h5 mb-0">{{ \Carbon\Carbon::parse($prayerTime->jumah_1)->format('g:i A') }}</td>
                                            </tr>
                                            @endif
                                            @if($prayerTime->jumah_2)
                                            <tr>
                                                <td><strong><i class="bi bi-people me-2"></i>Jummah 2</strong></td>
                                                <td class="h5 mb-0">{{ \Carbon\Carbon::parse($prayerTime->jumah_2)->format('g:i A') }}</td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif

                            @if($prayerTime->eid_prayer_1 || $prayerTime->eid_prayer_2)
                            <div class="mb-4">
                                <h6 class="mb-3">Eid Prayer Times:</h6>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead class="table-warning">
                                            <tr>
                                                <th>Prayer</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($prayerTime->eid_prayer_1)
                                            <tr>
                                                <td><strong><i class="bi bi-star me-2"></i>Eid Prayer 1</strong></td>
                                                <td class="h5 mb-0">{{ \Carbon\Carbon::parse($prayerTime->eid_prayer_1)->format('g:i A') }}</td>
                                            </tr>
                                            @endif
                                            @if($prayerTime->eid_prayer_2)
                                            <tr>
                                                <td><strong><i class="bi bi-star me-2"></i>Eid Prayer 2</strong></td>
                                                <td class="h5 mb-0">{{ \Carbon\Carbon::parse($prayerTime->eid_prayer_2)->format('g:i A') }}</td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>Date:</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($prayerTime->date)->format('F j, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Day:</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($prayerTime->date)->format('l') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created:</strong></td>
                                            <td>{{ $prayerTime->created_at->format('M j, Y g:i A') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Updated:</strong></td>
                                            <td>{{ $prayerTime->updated_at->format('M j, Y g:i A') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="alert alert-info mt-3">
                                <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Legend</h6>
                                <p class="mb-1"><strong>Beginning Time:</strong> Prayer start time</p>
                                <p class="mb-1"><strong>Adhan Time:</strong> Call to prayer time</p>
                                <p class="mb-0"><strong>Jamaat:</strong> Congregation start time</p>
                            </div>

                            <div class="card bg-light mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('admin.prayer-times.destroy', $prayerTime) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Are you sure you want to delete this prayer time?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm w-100">
                                            <i class="bi bi-trash me-1"></i> Delete Prayer Time
                                        </button>
                                    </form>
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
