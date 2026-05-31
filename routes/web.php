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
Route::get('/', function() {
    $cookieIds = [];
    if (request()->hasCookie('safora_my_reports')) {
        $cookieIds = json_decode(request()->cookie('safora_my_reports'), true) ?: [];
    }

    $sessionIds = session()->get('my_reports', []);
    $allIds = array_unique(array_merge($sessionIds, $cookieIds));

    if (!empty($allIds)) {
        session()->put('my_reports', $allIds);
    }

    $activeReport = null;
    if (!empty($allIds)) {
        $activeReport = \App\Models\Report::whereIn('id', $allIds)
            ->where('status', '!=', 'Resolved')
            ->latest()
            ->first();
    }

    return view('welcome', compact('activeReport'));
})->name('home');

// ─── PUBLIC (tanpa login) ────────────────────────────────────────
Route::get('/emergency', function() { return redirect('/'); });
Route::post('/emergency', [EmergencyController::class, 'store']);

Route::get('/tracking-search', [TrackingController::class, 'search'])->name('tracking.search');
Route::get('/tracking/{id}/live', [TrackingController::class, 'live'])->name('tracking.live');
Route::post('/tracking/{id}/location', [TrackingController::class, 'updateLocation'])->name('tracking.location');
Route::get('/tracking/{id}', [TrackingController::class, 'show'])->name('tracking.show');
Route::post('/tracking/{reportId}/evidence', [EvidenceController::class, 'store'])->name('evidence.store');
Route::delete('/evidence/{id}', [EvidenceController::class, 'destroy'])->name('evidence.destroy');
Route::get('/storage/evidences/{filename}', function ($filename) {
    $path = storage_path('app/public/evidences/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
});
Route::post('/tracking/{id}/resolve', [TrackingController::class, 'resolve'])->name('tracking.resolve');
Route::post('/tracking/{id}/chronology', [TrackingController::class, 'storeChronology'])->name('tracking.chronology');

// Sinkronisasi laporan aktif untuk korban guest/anonim via localStorage
Route::get('/tracking/active-check', function(\Illuminate\Http\Request $request) {
    $idsInput = $request->input('ids', []);
    if (is_string($idsInput)) {
        $ids = explode(',', $idsInput);
    } else {
        $ids = (array) $idsInput;
    }
    $ids = array_filter(array_map('trim', $ids));

    if (empty($ids)) {
        return response()->json(['active_report_id' => null]);
    }

    $sessionIds = session()->get('my_reports', []);
    $allIds = array_unique(array_merge($sessionIds, $ids));
    session()->put('my_reports', $allIds);
    cookie()->queue(cookie('safora_my_reports', json_encode($allIds), 60 * 24 * 30));

    // Filter laporan yang belum terselesaikan
    $active = \App\Models\Report::whereIn('id', $allIds)
        ->where('status', '!=', 'Resolved')
        ->latest()
        ->first();

    return response()->json([
        'active_report_id' => $active ? $active->id : null,
    ]);
});

// Chat berbasis laporan (public — bisa diakses pelapor, saksi <5km, atau partner)
Route::get('/chat/report/{reportId}', [\App\Http\Controllers\ChatController::class, 'reportChat'])->name('chat.report');
Route::get('/chat/report/{reportId}/poll', [\App\Http\Controllers\ChatController::class, 'pollMessages'])->name('chat.poll');
Route::post('/chat/report/{reportId}/send', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send.report');

// ─── AUTH REQUIRED ────────────────────────────────────────────────
Route::middleware(['auth', 'phone.required'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/reports', [DashboardController::class, 'reportsJson'])->name('dashboard.reports.json');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Profile (update & delete tetap via profile route)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/phone/verify', [ProfileController::class, 'verifyPhone'])->name('profile.phone.verify');
    Route::post('/profile/phone/resend', [ProfileController::class, 'resendPhoneVerification'])->name('profile.phone.resend');
    Route::delete('/profile/phone', [ProfileController::class, 'removePhone'])->name('profile.phone.remove');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Past Report (Laporan Biasa) & Editable Reports
    Route::get('/report/create', [\App\Http\Controllers\PastReportController::class, 'create'])->name('report.create');
    Route::post('/report', [\App\Http\Controllers\PastReportController::class, 'store'])->name('report.store');
    Route::post('/report/evidence/upload', [\App\Http\Controllers\PastReportController::class, 'uploadEvidenceTemp'])->name('report.evidence.upload');
    Route::delete('/report/evidence/delete', [\App\Http\Controllers\PastReportController::class, 'deleteEvidenceTemp'])->name('report.evidence.delete');
    Route::get('/report/{id}/edit', [\App\Http\Controllers\ReportController::class, 'edit'])->name('report.edit');
    Route::patch('/report/{id}', [\App\Http\Controllers\ReportController::class, 'update'])->name('report.update');
    Route::delete('/report/{id}', [\App\Http\Controllers\ReportController::class, 'destroy'])->name('report.destroy');

    // Password (dipakai di settings tab keamanan)
    Route::put('/password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.update');

    // Trusted Contacts
    Route::get('/trusted-contacts', [TrustedContactController::class, 'index'])->name('trusted-contact.index');
    Route::post('/trusted-contact', [TrustedContactController::class, 'store'])->name('trusted-contact.store');
    Route::post('/trusted-contact/verify', [TrustedContactController::class, 'verify'])->name('trusted-contact.verify');
    Route::post('/trusted-contact/resend', [TrustedContactController::class, 'resend'])->name('trusted-contact.resend');
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


    // Data Partner (info + pricelist)
    Route::get('/data-partner/{partnerId}', [\App\Http\Controllers\PembayaranMockController::class, 'showDataPartner'])->name('partner.data');

    // Kompatibilitas: route lama redirect ke page baru
    Route::get('/pembayaran/partner/{partnerId}', function (string $partnerId) {
        return redirect()->route('partner.data', ['partnerId' => $partnerId]);
    });

    // Halaman pembayaran untuk satu layanan
    Route::get('/pembayaran/{priceListId}', [\App\Http\Controllers\PembayaranMockController::class, 'showPembayaran'])->name('pembayaran.show');

    // Proses pembayaran demo (JSON response)
    Route::post('/pembayaran', [\App\Http\Controllers\PembayaranMockController::class, 'pay'])->name('pembayaran.pay');



    // Chat threads (legacy stubs)
    Route::get('/chat/threads', [\App\Http\Controllers\ChatController::class, 'indexThreads'])->name('chat.threads');
    Route::get('/chat/start/{partnerId}', [\App\Http\Controllers\ChatController::class, 'start'])->name('chat.start');
    Route::post('/chat/send/{partnerId}', [\App\Http\Controllers\ChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/messages/{partnerId}', [\App\Http\Controllers\ChatController::class, 'messages'])->name('chat.messages');


    // Evidence locker (halaman galeri bukti user)
    Route::get('/evidence', [EvidenceController::class, 'index'])->name('evidence.index');

    // Partner / Mitra
    Route::middleware('partner')->prefix('partner')->group(function () {
        Route::get('/', [PartnerController::class, 'index'])->name('partner.index');
        Route::get('/report/{id}', [PartnerController::class, 'show'])->name('partner.show');
        Route::post('/report/{id}/accept', [PartnerController::class, 'accept'])->name('partner.report.accept');
        Route::post('/report/{id}/status', [PartnerController::class, 'updateStatus'])->name('partner.status');
    });

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/reports/{id}/reroute', [AdminController::class, 'rerouteReport'])->name('admin.reports.reroute');
    Route::post('/reports/{id}/resolve', [AdminController::class, 'resolveReport'])->name('admin.reports.resolve');

    Route::get('/partners', [AdminPartnerController::class, 'index'])->name('admin.partners');

    Route::get('/partners/create', [AdminPartnerController::class, 'create'])->name('admin.partners.create');

    Route::post('/partners', [AdminPartnerController::class, 'store'])->name('admin.partners.store');

    Route::patch('/partners/{id}/verify', [AdminPartnerController::class, 'toggleVerify'])->name('admin.partners.verify');
    Route::patch('/partners/{id}/active', [AdminPartnerController::class, 'toggleActive'])->name('admin.partners.active');

    // Export CSV untuk semua user
    Route::get('/users/export-csv', [\App\Http\Controllers\AdminUsersExportController::class, 'exportCsv'])
        ->name('admin.users.exportCsv');
});
});


require __DIR__.'/auth.php';
