<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmergencyController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\TrustedContactController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\WitnessController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;

// Landing page
Route::get('/', fn() => view('welcome'))->name('home');

// ─── PUBLIC (tanpa login) ────────────────────────────────────────
Route::get('/emergency', [EmergencyController::class, 'index'])->name('emergency');
Route::post('/emergency', [EmergencyController::class, 'store']);

Route::get('/tracking-search', [TrackingController::class, 'search'])->name('tracking.search');
Route::get('/tracking/{id}', [TrackingController::class, 'show'])->name('tracking.show');
Route::post('/tracking/{reportId}/evidence', [EvidenceController::class, 'store'])->name('evidence.store');

Route::get('/witness', [WitnessController::class, 'index'])->name('witness');
Route::post('/witness', [WitnessController::class, 'store'])->name('witness.store');

// ─── AUTH REQUIRED ────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Profile (update & delete tetap via profile route)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Password (dipakai di settings tab keamanan)
    Route::put('/password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.update');

    // Trusted Contacts
    Route::get('/trusted-contacts', [TrustedContactController::class, 'index'])->name('trusted-contact.index');
    Route::post('/trusted-contact', [TrustedContactController::class, 'store'])->name('trusted-contact.store');
    Route::delete('/trusted-contact/{id}', [TrustedContactController::class, 'destroy'])->name('trusted-contact.destroy');

    // Evidence locker (halaman galeri bukti user)
    Route::get('/evidence', [EvidenceController::class, 'index'])->name('evidence.index');

    // Partner / Mitra
    Route::middleware('partner')->prefix('partner')->group(function () {
        Route::get('/', [PartnerController::class, 'index'])->name('partner.index');
        Route::get('/report/{id}', [PartnerController::class, 'show'])->name('partner.show');
        Route::post('/report/{id}/status', [PartnerController::class, 'updateStatus'])->name('partner.status');
    });

});

require __DIR__.'/auth.php';
