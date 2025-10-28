<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\PrayerTimeController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\HadeethController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MediaScheduleController;
use App\Http\Controllers\Admin\SlidingTextController;
use App\Http\Controllers\Admin\BoxesManagementController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\MediaDisplayController;
use App\Http\Controllers\ApiController;

// Public Routes
Route::get('/', [TimetableController::class, 'index'])->name('timetable.index');

// API Routes for real-time data
Route::prefix('api')->group(function () {
    Route::get('/prayer-times', [ApiController::class, 'prayerTimes']);
    Route::get('/announcements', [ApiController::class, 'announcements']);
    Route::get('/hadeeth', [ApiController::class, 'hadeeth']);
    Route::get('/next-prayer', [ApiController::class, 'nextPrayer']);
    Route::get('/settings', [ApiController::class, 'settings']);
    
    // Media display API routes
    Route::get('/current-media', [MediaDisplayController::class, 'getCurrentMedia']);
    Route::get('/countdown-info', [MediaDisplayController::class, 'getCountdownInfo']);
    Route::get('/media-status', [MediaDisplayController::class, 'getStatus']);
    Route::get('/debug-schedules', [MediaDisplayController::class, 'debugSchedules']);
});

// Auth Routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    
    Route::post('/login', function (Illuminate\Http\Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (auth()->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/admin');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    });
});

Route::post('/logout', function (Illuminate\Http\Request $request) {
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    
    // Profile management routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('admin.profile.password');
    
    // Google Sheets import routes (must come before resource routes)
    Route::get('prayer-times/import', [PrayerTimeController::class, 'showImport'])->name('admin.prayer-times.import');
    Route::post('prayer-times/import', [PrayerTimeController::class, 'import'])->name('admin.prayer-times.import.process');
    Route::post('prayer-times/preview', [PrayerTimeController::class, 'preview'])->name('admin.prayer-times.preview');
    Route::delete('prayer-times/bulk-delete', [PrayerTimeController::class, 'bulkDelete'])->name('admin.prayer-times.bulk-delete');
    
    Route::resource('prayer-times', PrayerTimeController::class, [
        'as' => 'admin'
    ]);
    
    Route::resource('announcements', AnnouncementController::class, [
        'as' => 'admin'
    ]);
    
    Route::resource('hadeeths', HadeethController::class, [
        'as' => 'admin'
    ]);
    
    Route::resource('settings', SettingController::class, [
        'as' => 'admin'
    ]);
    
    Route::post('settings/batch-update', [SettingController::class, 'updateBatch'])
        ->name('admin.settings.batch-update');
    
    Route::post('settings/delete-logo', [SettingController::class, 'deleteLogo'])
        ->name('admin.settings.delete-logo');
    
    // Media management routes
    Route::resource('media', MediaController::class, [
        'as' => 'admin'
    ]);
    
    Route::get('media/{medium}/preview', [MediaController::class, 'preview'])
        ->name('admin.media.preview');
    
    Route::post('media/{media}/toggle-status', [MediaController::class, 'toggleStatus'])
        ->name('admin.media.toggle-status');
    
    Route::post('media-schedules/check-overlap', [MediaScheduleController::class, 'checkOverlap'])
        ->name('admin.media-schedules.check-overlap');
    
    Route::resource('media-schedules', MediaScheduleController::class, [
        'as' => 'admin'
    ]);
    
    Route::post('media-schedules/{mediaSchedule}/toggle-status', [MediaScheduleController::class, 'toggleStatus'])
        ->name('admin.media-schedules.toggle-status');
    
    // Sliding text management routes
    Route::resource('sliding-texts', SlidingTextController::class, [
        'as' => 'admin'
    ]);
    
    // Boxes management routes
    Route::prefix('boxes')->group(function () {
        Route::get('/', [BoxesManagementController::class, 'index'])->name('admin.boxes.index');
        Route::get('/{boxType}/edit', [BoxesManagementController::class, 'edit'])->name('admin.boxes.edit');
        Route::put('/{boxType}', [BoxesManagementController::class, 'update'])->name('admin.boxes.update');
        Route::post('/{boxType}/update-ajax', [BoxesManagementController::class, 'updateAjax'])->name('admin.boxes.update-ajax');
        Route::post('/{boxType}/toggle', [BoxesManagementController::class, 'toggleActive'])->name('admin.boxes.toggle');
        Route::post('/{boxType}/reset', [BoxesManagementController::class, 'reset'])->name('admin.boxes.reset');
        Route::get('/{boxType}/preview', [BoxesManagementController::class, 'getPreview'])->name('admin.boxes.preview');
        Route::get('/all', [BoxesManagementController::class, 'getAllBoxes'])->name('admin.boxes.all');
        Route::post('/update-order', [BoxesManagementController::class, 'updateOrder'])->name('admin.boxes.update-order');
        Route::post('/initialize-defaults', [BoxesManagementController::class, 'initializeDefaults'])->name('admin.boxes.initialize-defaults');
    });
});
