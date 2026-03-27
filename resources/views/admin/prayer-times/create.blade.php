@extends('layouts.admin')

@section('title', 'Add Prayer Times')
@section('page-icon', '<i class="bi bi-plus-circle me-2"></i>')
@section('page-title', 'Add Prayer Times')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow">
            <div class="card-header card-header-custom">
                <h5 class="mb-0">
                    <i class="bi bi-plus-circle me-2"></i>
                    Add New Prayer Times
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.prayer-times.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control @error('date') is-invalid @enderror" 
                                   id="date" 
                                   name="date" 
                                   value="{{ old('date', now()->format('Y-m-d')) }}" 
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
                                <div class="col-md-4 mb-3">
                                    <label for="fajr" class="form-label fw-bold">Beginning Time <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           class="form-control @error('fajr') is-invalid @enderror" 
                                           id="fajr" 
                                           name="fajr" 
                                           value="{{ old('fajr', '05:30') }}" 
                                           required>
                                    @error('fajr')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="fajr_adhan" class="form-label fw-bold">Adhan Time</label>
                                    <input type="time" 
                                           class="form-control @error('fajr_adhan') is-invalid @enderror" 
                                           id="fajr_adhan" 
                                           name="fajr_adhan" 
                                           value="{{ old('fajr_adhan', '05:25') }}">
                                    @error('fajr_adhan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="fajr_jamaat" class="form-label fw-bold">Jamaat (Congregation)</label>
                                    <input type="time" 
                                           class="form-control @error('fajr_jamaat') is-invalid @enderror" 
                                           id="fajr_jamaat" 
                                           name="fajr_jamaat" 
                                           value="{{ old('fajr_jamaat', '05:45') }}">
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
                                           value="{{ old('sun_rise', '05:51') }}">
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
                                <div class="col-md-4 mb-3">
                                    <label for="zohar" class="form-label fw-bold">Beginning Time <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           class="form-control @error('zohar') is-invalid @enderror" 
                                           id="zohar" 
                                           name="zohar" 
                                           value="{{ old('zohar', '13:15') }}" 
                                           required>
                                    @error('zohar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="zohar_adhan" class="form-label fw-bold">Adhan Time</label>
                                    <input type="time" 
                                           class="form-control @error('zohar_adhan') is-invalid @enderror" 
                                           id="zohar_adhan" 
                                           name="zohar_adhan" 
                                           value="{{ old('zohar_adhan', '13:10') }}">
                                    @error('zohar_adhan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="zohar_jamaat" class="form-label fw-bold">Jamaat (Congregation)</label>
                                    <input type="time" 
                                           class="form-control @error('zohar_jamaat') is-invalid @enderror" 
                                           id="zohar_jamaat" 
                                           name="zohar_jamaat" 
                                           value="{{ old('zohar_jamaat', '13:30') }}">
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
                                <div class="col-md-4 mb-3">
                                    <label for="asr" class="form-label fw-bold">Beginning Time <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           class="form-control @error('asr') is-invalid @enderror" 
                                           id="asr" 
                                           name="asr" 
                                           value="{{ old('asr', '17:11') }}" 
                                           required>
                                    @error('asr')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="asr_adhan" class="form-label fw-bold">Adhan Time</label>
                                    <input type="time" 
                                           class="form-control @error('asr_adhan') is-invalid @enderror" 
                                           id="asr_adhan" 
                                           name="asr_adhan" 
                                           value="{{ old('asr_adhan', '17:06') }}">
                                    @error('asr_adhan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="asr_jamaat" class="form-label fw-bold">Jamaat (Congregation)</label>
                                    <input type="time" 
                                           class="form-control @error('asr_jamaat') is-invalid @enderror" 
                                           id="asr_jamaat" 
                                           name="asr_jamaat" 
                                           value="{{ old('asr_jamaat', '17:30') }}">
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
                                <div class="col-md-4 mb-3">
                                    <label for="maghrib" class="form-label fw-bold">Beginning Time <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           class="form-control @error('maghrib') is-invalid @enderror" 
                                           id="maghrib" 
                                           name="maghrib" 
                                           value="{{ old('maghrib', '20:34') }}" 
                                           required>
                                    @error('maghrib')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="maghrib_adhan" class="form-label fw-bold">Adhan Time</label>
                                    <input type="time" 
                                           class="form-control @error('maghrib_adhan') is-invalid @enderror" 
                                           id="maghrib_adhan" 
                                           name="maghrib_adhan" 
                                           value="{{ old('maghrib_adhan', '20:29') }}">
                                    @error('maghrib_adhan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="maghrib_jamaat" class="form-label fw-bold">Jamaat (Congregation)</label>
                                    <input type="time" 
                                           class="form-control @error('maghrib_jamaat') is-invalid @enderror" 
                                           id="maghrib_jamaat" 
                                           name="maghrib_jamaat" 
                                           value="{{ old('maghrib_jamaat', '20:39') }}">
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
                                <div class="col-md-4 mb-3">
                                    <label for="isha" class="form-label fw-bold">Beginning Time <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           class="form-control @error('isha') is-invalid @enderror" 
                                           id="isha" 
                                           name="isha" 
                                           value="{{ old('isha', '21:50') }}" 
                                           required>
                                    @error('isha')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="isha_adhan" class="form-label fw-bold">Adhan Time</label>
                                    <input type="time" 
                                           class="form-control @error('isha_adhan') is-invalid @enderror" 
                                           id="isha_adhan" 
                                           name="isha_adhan" 
                                           value="{{ old('isha_adhan', '21:45') }}">
                                    @error('isha_adhan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="isha_jamaat" class="form-label fw-bold">Jamaat (Congregation)</label>
                                    <input type="time" 
                                           class="form-control @error('isha_jamaat') is-invalid @enderror" 
                                           id="isha_jamaat" 
                                           name="isha_jamaat" 
                                           value="{{ old('isha_jamaat', '22:00') }}">
                                    @error('isha_jamaat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Eid Prayers -->
                    <div class="card mb-3 border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="bi bi-stars me-2"></i>Eid Prayers (Optional)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="eid_prayer_1" class="form-label fw-bold">Eid Prayer 1</label>
                                    <input type="time"
                                           class="form-control @error('eid_prayer_1') is-invalid @enderror"
                                           id="eid_prayer_1"
                                           name="eid_prayer_1"
                                           value="{{ old('eid_prayer_1') }}">
                                    @error('eid_prayer_1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="eid_prayer_2" class="form-label fw-bold">Eid Prayer 2</label>
                                    <input type="time"
                                           class="form-control @error('eid_prayer_2') is-invalid @enderror"
                                           id="eid_prayer_2"
                                           name="eid_prayer_2"
                                           value="{{ old('eid_prayer_2') }}">
                                    @error('eid_prayer_2')
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
                                           value="{{ old('jumah_1', '13:30') }}">
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
                                           value="{{ old('jumah_2') }}">
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
                            Save Prayer Times
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
