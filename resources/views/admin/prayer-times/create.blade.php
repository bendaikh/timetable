@extends('layouts.admin')

@section('title', 'Add Prayer Times')
@section('page-icon', '<i class="bi bi-plus-circle me-2"></i>')
@section('page-title', 'Add Prayer Times')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
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

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fajr" class="form-label fw-bold">Fajr <span class="text-danger">*</span></label>
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
                        
                        <div class="col-md-6 mb-3">
                            <label for="zohar" class="form-label fw-bold">Zohar <span class="text-danger">*</span></label>
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
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="asr" class="form-label fw-bold">Asr <span class="text-danger">*</span></label>
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
                        
                        <div class="col-md-6 mb-3">
                            <label for="maghrib" class="form-label fw-bold">Maghrib <span class="text-danger">*</span></label>
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
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="isha" class="form-label fw-bold">Isha <span class="text-danger">*</span></label>
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
                        
                        <div class="col-md-6 mb-3">
                            <label for="sun_rise" class="form-label fw-bold">Sun Rise</label>
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

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="jumah_1" class="form-label fw-bold">Jumah Prayer 1</label>
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
                            <label for="jumah_2" class="form-label fw-bold">Jumah Prayer 2</label>
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

@section('scripts')
<script>
    // Auto-fill next day when date changes
    document.getElementById('date').addEventListener('change', function() {
        // You can add logic here to fetch prayer times for the selected date
        // or auto-calculate based on location
    });
</script>
@endsection
