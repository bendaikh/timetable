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
                <a href="{{ route('admin.prayer-times.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-circle me-2"></i>
                    Add Prayer Times
                </a>
            </div>
            <div class="card-body">
                @if($prayerTimes->count() > 0)
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($prayerTimes as $prayerTime)
                                <tr>
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
                    
                    <div class="d-flex justify-content-center">
                        {{ $prayerTimes->links() }}
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
