<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\PrayerTimeController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\HadeethController;
use App\Http\Controllers\Admin\SettingController;
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
});
