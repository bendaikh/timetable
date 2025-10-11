@extends('layouts.admin')

@section('title', 'Edit Prayer Times')
@section('page-icon', '<i class="bi bi-pencil me-2"></i>')
@section('page-title', 'Edit Prayer Times')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow">
            <div class="card-header card-header-custom">
                <h5 class="mb-0">
                    <i class="bi bi-pencil me-2"></i>
                    Edit Prayer Times - {{ $prayerTime->date->format('M j, Y') }}
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.prayer-times.update', $prayerTime) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control @error('date') is-invalid @enderror" 
                                   id="date" 
                                   name="date" 
                                   value="{{ old('date', $prayerTime->date->format('Y-m-d')) }}" 
                                   required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> Enter both <strong>Adhan</strong> (call to prayer) and <strong>Jamaat</strong> (congregation) times for each prayer.
                    </div>

                    <!-- Fajr -->
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-sunrise me-2"></i>Fajr Prayer</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fajr" class="form-label fw-bold">Adhan (Call to Prayer) <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           class="form-control @error('fajr') is-invalid @enderror" 
                                           id="fajr" 
                                           name="fajr" 
                                           value="{{ old('fajr', \Carbon\Carbon::parse($prayerTime->fajr)->format('H:i')) }}" 
                                           required>
                                    @error('fajr')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="fajr_jamaat" class="form-label fw-bold">Jamaat (Congregation)</label>
                                    <input type="time" 
                                           class="form-control @error('fajr_jamaat') is-invalid @enderror" 
                                           id="fajr_jamaat" 
                                           name="fajr_jamaat" 
                                           value="{{ old('fajr_jamaat', $prayerTime->fajr_jamaat ? \Carbon\Carbon::parse($prayerTime->fajr_jamaat)->format('H:i') : '') }}">
                                    @error('fajr_jamaat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sunrise -->
                    <div class="card mb-3 border-warning">
                        <div class="card-header bg-warning">
                            <h6 class="mb-0"><i class="bi bi-sun me-2"></i>Sunrise (No Jamaat)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="sun_rise" class="form-label fw-bold">Sunrise Time</label>
                                    <input type="time" 
                                           class="form-control @error('sun_rise') is-invalid @enderror" 
                                           id="sun_rise" 
                                           name="sun_rise" 
                                           value="{{ old('sun_rise', $prayerTime->sun_rise ? \Carbon\Carbon::parse($prayerTime->sun_rise)->format('H:i') : '') }}">
                                    @error('sun_rise')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Zohar -->
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-brightness-high me-2"></i>Zohar Prayer</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="zohar" class="form-label fw-bold">Adhan (Call to Prayer) <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           class="form-control @error('zohar') is-invalid @enderror" 
                                           id="zohar" 
                                           name="zohar" 
                                           value="{{ old('zohar', \Carbon\Carbon::parse($prayerTime->zohar)->format('H:i')) }}" 
                                           required>
                                    @error('zohar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="zohar_jamaat" class="form-label fw-bold">Jamaat (Congregation)</label>
                                    <input type="time" 
                                           class="form-control @error('zohar_jamaat') is-invalid @enderror" 
                                           id="zohar_jamaat" 
                                           name="zohar_jamaat" 
                                           value="{{ old('zohar_jamaat', $prayerTime->zohar_jamaat ? \Carbon\Carbon::parse($prayerTime->zohar_jamaat)->format('H:i') : '') }}">
                                    @error('zohar_jamaat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Asr -->
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-cloud-sun me-2"></i>Asr Prayer</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="asr" class="form-label fw-bold">Adhan (Call to Prayer) <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           class="form-control @error('asr') is-invalid @enderror" 
                                           id="asr" 
                                           name="asr" 
                                           value="{{ old('asr', \Carbon\Carbon::parse($prayerTime->asr)->format('H:i')) }}" 
                                           required>
                                    @error('asr')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="asr_jamaat" class="form-label fw-bold">Jamaat (Congregation)</label>
                                    <input type="time" 
                                           class="form-control @error('asr_jamaat') is-invalid @enderror" 
                                           id="asr_jamaat" 
                                           name="asr_jamaat" 
                                           value="{{ old('asr_jamaat', $prayerTime->asr_jamaat ? \Carbon\Carbon::parse($prayerTime->asr_jamaat)->format('H:i') : '') }}">
                                    @error('asr_jamaat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Maghrib -->
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-sunset me-2"></i>Maghrib Prayer</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="maghrib" class="form-label fw-bold">Adhan (Call to Prayer) <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           class="form-control @error('maghrib') is-invalid @enderror" 
                                           id="maghrib" 
                                           name="maghrib" 
                                           value="{{ old('maghrib', \Carbon\Carbon::parse($prayerTime->maghrib)->format('H:i')) }}" 
                                           required>
                                    @error('maghrib')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="maghrib_jamaat" class="form-label fw-bold">Jamaat (Congregation)</label>
                                    <input type="time" 
                                           class="form-control @error('maghrib_jamaat') is-invalid @enderror" 
                                           id="maghrib_jamaat" 
                                           name="maghrib_jamaat" 
                                           value="{{ old('maghrib_jamaat', $prayerTime->maghrib_jamaat ? \Carbon\Carbon::parse($prayerTime->maghrib_jamaat)->format('H:i') : '') }}">
                                    @error('maghrib_jamaat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Isha -->
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-moon-stars me-2"></i>Isha Prayer</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="isha" class="form-label fw-bold">Adhan (Call to Prayer) <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           class="form-control @error('isha') is-invalid @enderror" 
                                           id="isha" 
                                           name="isha" 
                                           value="{{ old('isha', \Carbon\Carbon::parse($prayerTime->isha)->format('H:i')) }}" 
                                           required>
                                    @error('isha')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="isha_jamaat" class="form-label fw-bold">Jamaat (Congregation)</label>
                                    <input type="time" 
                                           class="form-control @error('isha_jamaat') is-invalid @enderror" 
                                           id="isha_jamaat" 
                                           name="isha_jamaat" 
                                           value="{{ old('isha_jamaat', $prayerTime->isha_jamaat ? \Carbon\Carbon::parse($prayerTime->isha_jamaat)->format('H:i') : '') }}">
                                    @error('isha_jamaat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Jummah Prayers -->
                    <div class="card mb-3 border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="bi bi-people me-2"></i>Jummah Prayers (Optional)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="jumah_1" class="form-label fw-bold">Jummah Prayer 1</label>
                                    <input type="time" 
                                           class="form-control @error('jumah_1') is-invalid @enderror" 
                                           id="jumah_1" 
                                           name="jumah_1" 
                                           value="{{ old('jumah_1', $prayerTime->jumah_1 ? \Carbon\Carbon::parse($prayerTime->jumah_1)->format('H:i') : '') }}">
                                    @error('jumah_1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="jumah_2" class="form-label fw-bold">Jummah Prayer 2</label>
                                    <input type="time" 
                                           class="form-control @error('jumah_2') is-invalid @enderror" 
                                           id="jumah_2" 
                                           name="jumah_2" 
                                           value="{{ old('jumah_2', $prayerTime->jumah_2 ? \Carbon\Carbon::parse($prayerTime->jumah_2)->format('H:i') : '') }}">
                                    @error('jumah_2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.prayer-times.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>
                            Back to Prayer Times
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Update Prayer Times
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
