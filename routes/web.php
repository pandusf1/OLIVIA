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
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminPartnerController;
use App\Http\Controllers\UserLocationController;
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
    Route::get('/trusted-contact/{id}/edit', [TrustedContactController::class, 'edit'])->name('trusted-contact.edit');
    Route::patch('/trusted-contact/{id}', [TrustedContactController::class, 'update'])->name('trusted-contact.update');
    Route::delete('/trusted-contact/{id}', [TrustedContactController::class, 'destroy'])->name('trusted-contact.destroy');

    // Partner terdekat (gabungan: semua tipe)
    Route::get('/partner-nearby', [\App\Http\Controllers\PartnerNearbyController::class, 'index'])->name('partner.nearby');

    // Psikolog & pengacara terdekat (khusus kategori)
    Route::get('/psikolog-pengacara-nearby', [\App\Http\Controllers\PsikologPengacaraController::class, 'index'])->name('psikolog-pengacara.nearby');

    // Reload lokasi user (tanpa reload halaman)
    Route::post('/user-location/reload', [UserLocationController::class, 'reload'])->name('user-location.reload');

    // Pencarian di map (type + query)
    Route::get('/map-search', [\App\Http\Controllers\MapSearchController::class, 'search'])->name('map.search');

    // Emergency markers untuk dashboard user terdekat
    Route::get('/dashboard/emergency-markers', [\App\Http\Controllers\DashboardEmergencyMarkersController::class, 'index'])
        ->name('dashboard.emergency.markers');


    // Data Partner (pengganti pembanyaran demo)
    Route::get('/data-partner/{partnerId}', [\App\Http\Controllers\PembayaranMockController::class, 'showDataPartner'])->name('partner.data');

    // Kompatibilitas: route lama redirect ke page baru
    Route::get('/pembayaran/partner/{partnerId}', function (string $partnerId) {
        return redirect()->route('partner.data', ['partnerId' => $partnerId]);
    });

    // (endpoint submit payment demo masih dibiarkan agar tidak error jika masih ada form lama)
    Route::post('/pembayaran', [\App\Http\Controllers\PembayaranMockController::class, 'pay'])->name('pembayaran.pay');


    // Chat
    Route::get('/chat/threads', [\App\Http\Controllers\ChatController::class, 'indexThreads'])->name('chat.threads');
    Route::get('/chat/start/{partnerId}', [\App\Http\Controllers\ChatController::class, 'start'])->name('chat.start');
    Route::post('/chat/send/{partnerId}', [\App\Http\Controllers\ChatController::class, 'send'])->name('chat.send');


    // Evidence locker (halaman galeri bukti user)
    Route::get('/evidence', [EvidenceController::class, 'index'])->name('evidence.index');

    // Partner / Mitra
    Route::middleware('partner')->prefix('partner')->group(function () {
        Route::get('/', [PartnerController::class, 'index'])->name('partner.index');
        Route::get('/report/{id}', [PartnerController::class, 'show'])->name('partner.show');
        Route::post('/report/{id}/status', [PartnerController::class, 'updateStatus'])->name('partner.status');
    });

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

    Route::get('/', [AdminController::class, 'index'])->name('admin.index');

    Route::get('/partners', [AdminPartnerController::class, 'index'])->name('admin.partners');

    Route::get('/partners/create', [AdminPartnerController::class, 'create'])->name('admin.partners.create');

    Route::post('/partners', [AdminPartnerController::class, 'store'])->name('admin.partners.store');

    Route::patch('/partners/{id}/verify', [AdminPartnerController::class, 'toggleVerify'])->name('admin.partners.verify');

    // Export CSV untuk semua user
    Route::get('/users/export-csv', [\App\Http\Controllers\AdminUsersExportController::class, 'exportCsv'])
        ->name('admin.users.exportCsv');
});
});


require __DIR__.'/auth.php';
