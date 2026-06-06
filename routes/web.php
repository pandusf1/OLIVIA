<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmergencyController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\TrustedContactController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\WitnessController;
use App\Http\Controllers\MitraController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminMitraController;
use App\Http\Controllers\UserLocationController;
// Landing page
Route::get('/', function() {
    $cookieIds = [];
    if (request()->hasCookie('safora_my_reports')) {
        $cookieIds = json_decode(request()->cookie('safora_my_reports'), true) ?: [];
    }

    $sessionIds = session()->get('my_reports', []);
    $allIds = array_unique(array_merge($sessionIds, $cookieIds));

    if (auth()->check()) {
        $userReportIds = \App\Models\Report::where('user_id', auth()->id())
            ->pluck('id')
            ->toArray();
        $allIds = array_unique(array_merge($allIds, $userReportIds));
    }

    if (!empty($allIds)) {
        $allIds = \App\Models\Report::whereIn('id', $allIds)
            ->where(function ($query) {
                $query->whereNull('user_id');
                if (auth()->check()) {
                    $query->orWhere('user_id', auth()->id());
                }
            })
            ->pluck('id')
            ->toArray();
    } else {
        $allIds = [];
    }

    session()->put('my_reports', $allIds);
    cookie()->queue(cookie('safora_my_reports', json_encode($allIds), 60 * 24 * 30));

    $activeReport = null;
    if (!empty($allIds)) {
        // Prioritaskan laporan guest/anonim (user_id IS NULL)
        $activeReport = \App\Models\Report::whereIn('id', $allIds)
            ->where('status', '!=', 'Resolved')
            ->whereNull('user_id')
            ->latest()
            ->first();

        if (!$activeReport) {
            $activeReport = \App\Models\Report::whereIn('id', $allIds)
                ->where('status', '!=', 'Resolved')
                ->latest()
                ->first();
        }
    }

    return view('welcome', compact('activeReport'));
})->name('home');

// ─── PUBLIC (tanpa login) ────────────────────────────────────────
Route::get('/emergency', function() { return redirect('/'); });
Route::post('/emergency', [EmergencyController::class, 'store']);

Route::get('/tracking-search', [TrackingController::class, 'search'])->name('tracking.search');
Route::get('/tracking/{id}/live', [TrackingController::class, 'live'])->name('tracking.live');
Route::post('/tracking/{id}/location', [TrackingController::class, 'updateLocation'])->name('tracking.location');
Route::post('/tracking/{id}/mitra-location', [TrackingController::class, 'updateMitraLocation'])->name('tracking.mitra-location');
Route::get('/tracking/{id}', [TrackingController::class, 'show'])->name('tracking.show');
Route::post('/tracking/{reportId}/evidence', [EvidenceController::class, 'store'])->name('evidence.store');
Route::delete('/evidence/{id}', [EvidenceController::class, 'destroy'])->name('evidence.destroy');
Route::get('/evidences/view/{filename}', function ($filename) {
    // 1. Cek apakah ini ID bukti di database
    $evidence = \App\Models\Evidence::find($filename);
    if (!$evidence) {
        $basename = basename($filename);
        $evidence = \App\Models\Evidence::where('file_url', 'LIKE', '%' . $basename)->first();
    }

    if ($evidence) {
        if (str_starts_with($evidence->file_url, 'data:')) {
            if (preg_match('/^data:([^;]+);base64,(.+)$/', $evidence->file_url, $matches)) {
                $contentType = $matches[1];
                $data = base64_decode($matches[2]);
                return response($data)
                    ->header('Content-Type', $contentType)
                    ->header('Cache-Control', 'public, max-age=31536000'); // Cache browser 1 tahun
            }
        } else {
            $path = storage_path('app/public/' . $evidence->file_url);
            if (file_exists($path) && !is_dir($path)) {
                return response()->file($path);
            }
        }
    }

    // 2. Check storage_path
    $path = storage_path('app/public/evidences/' . $filename);
    if (file_exists($path) && !is_dir($path)) {
        return response()->file($path);
    }
    
    // 3. Check /tmp/evidences/
    $path = '/tmp/evidences/' . $filename;
    if (file_exists($path) && !is_dir($path)) {
        return response()->file($path);
    }
    
    // 4. Check /tmp/
    $path = '/tmp/' . $filename;
    if (file_exists($path) && !is_dir($path)) {
        return response()->file($path);
    }
    
    // 5. Try checking base name
    $filenameOnly = basename($filename);
    $path = storage_path('app/public/evidences/' . $filenameOnly);
    if (file_exists($path) && !is_dir($path)) {
        return response()->file($path);
    }
    
    abort(404);
})->name('evidences.view');

Route::post('/tracking/{id}/resolve', [TrackingController::class, 'resolve'])->name('tracking.resolve');
Route::post('/tracking/{id}/chronology', [TrackingController::class, 'storeChronology'])->name('tracking.chronology');
Route::post('/tracking/{id}/re-alert', [TrackingController::class, 'reAlert'])->name('tracking.re-alert');
Route::post('/tracking/{id}/status', [TrackingController::class, 'updateStatusByVictim'])->name('tracking.status.victim');


// Sinkronisasi laporan aktif untuk korban guest/anonim via localStorage
Route::get('/tracking/active-check', function(\Illuminate\Http\Request $request) {
    $idsInput = $request->input('ids', []);
    if (is_string($idsInput)) {
        $ids = explode(',', $idsInput);
    } else {
        $ids = (array) $idsInput;
    }
    $ids = array_filter(array_map('trim', $ids));

    $sessionIds = session()->get('my_reports', []);
    $allIds = array_unique(array_merge($sessionIds, $ids));

    if (auth()->check()) {
        $userReportIds = \App\Models\Report::where('user_id', auth()->id())
            ->pluck('id')
            ->toArray();
        $allIds = array_unique(array_merge($allIds, $userReportIds));
    }

    if (!empty($allIds)) {
        $allIds = \App\Models\Report::whereIn('id', $allIds)
            ->where(function ($query) {
                $query->whereNull('user_id');
                if (auth()->check()) {
                    $query->orWhere('user_id', auth()->id());
                }
            })
            ->pluck('id')
            ->toArray();
    } else {
        $allIds = [];
    }

    if (empty($allIds)) {
        session()->put('my_reports', []);
        cookie()->queue(cookie('safora_my_reports', json_encode([]), 60 * 24 * 30));
        return response()->json(['active_report_id' => null]);
    }

    session()->put('my_reports', $allIds);
    cookie()->queue(cookie('safora_my_reports', json_encode($allIds), 60 * 24 * 30));

    // Prioritaskan laporan guest/anonim (user_id IS NULL)
    $active = \App\Models\Report::whereIn('id', $allIds)
        ->where('status', '!=', 'Resolved')
        ->whereNull('user_id')
        ->latest()
        ->first();

    if (!$active) {
        $active = \App\Models\Report::whereIn('id', $allIds)
            ->where('status', '!=', 'Resolved')
            ->latest()
            ->first();
    }

    return response()->json([
        'active_report_id' => $active ? $active->id : null,
    ]);
});

// Chat berbasis laporan (public — bisa diakses pelapor, saksi <5km, atau mitra)
Route::get('/chat/report/{reportId}', [\App\Http\Controllers\ChatController::class, 'reportChat'])->name('chat.report');
Route::get('/chat/report/{reportId}/poll', [\App\Http\Controllers\ChatController::class, 'pollMessages'])->name('chat.poll');
Route::post('/chat/report/{reportId}/send', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send.report');

// ─── AUTH REQUIRED ────────────────────────────────────────────────
Route::middleware(['auth', 'phone.required'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/summary-data', [DashboardController::class, 'summaryData'])->name('dashboard.summary-data');
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
    Route::match(['post', 'delete'], '/report/evidence/delete', [\App\Http\Controllers\PastReportController::class, 'deleteEvidenceTemp'])->name('report.evidence.delete');
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

    // Mitra terdekat (gabungan: semua tipe)
    Route::get('/mitra-nearby', [\App\Http\Controllers\MitraNearbyController::class, 'index'])->name('mitra.nearby');

    // Psikolog & pengacara terdekat (khusus kategori)
    Route::get('/psikolog-pengacara-nearby', [\App\Http\Controllers\PsikologPengacaraController::class, 'index'])->name('psikolog-pengacara.nearby');

    // Reload lokasi user (tanpa reload halaman)
    Route::post('/user-location/reload', [UserLocationController::class, 'reload'])->name('user-location.reload');

    // Pencarian di map (type + query)
    Route::get('/map-search', [\App\Http\Controllers\MapSearchController::class, 'search'])->name('map.search');

    // Emergency markers untuk dashboard user terdekat
    Route::get('/dashboard/emergency-markers', [\App\Http\Controllers\DashboardEmergencyMarkersController::class, 'index'])
        ->name('dashboard.emergency.markers');


    // Data Mitra (info + pricelist)
    Route::get('/data-mitra/{mitraId}', [\App\Http\Controllers\PembayaranMockController::class, 'showDataMitra'])->name('mitra.data');

    // Kompatibilitas: route lama redirect ke page baru
    Route::get('/pembayaran/mitra/{mitraId}', function (string $mitraId) {
        return redirect()->route('mitra.data', ['mitraId' => $mitraId]);
    });

    // Halaman pembayaran untuk satu atau beberapa layanan (berbasis comma-separated IDs)
    Route::get('/pembayaran/{priceListIds}', [\App\Http\Controllers\PembayaranMockController::class, 'showPembayaran'])->name('pembayaran.show');

    // Proses pembayaran demo (JSON response)
    Route::post('/pembayaran', [\App\Http\Controllers\PembayaranMockController::class, 'pay'])->name('pembayaran.pay');



    // Chat threads (legacy stubs)
    Route::get('/chat/threads', [\App\Http\Controllers\ChatController::class, 'indexThreads'])->name('chat.threads');
    Route::get('/chat/start/{mitraId}', [\App\Http\Controllers\ChatController::class, 'start'])->name('chat.start');
    Route::post('/chat/send/{mitraId}', [\App\Http\Controllers\ChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/messages/{mitraId}', [\App\Http\Controllers\ChatController::class, 'messages'])->name('chat.messages');


    // Evidence locker (halaman galeri bukti user)
    Route::get('/evidence', [EvidenceController::class, 'index'])->name('evidence.index');

    // Mitra
    Route::middleware('mitra')->prefix('mitra')->group(function () {
        Route::get('/', [MitraController::class, 'index'])->name('mitra.index');
        Route::get('/report/{id}', [MitraController::class, 'show'])->name('mitra.show');
        Route::post('/report/{id}/accept', [MitraController::class, 'accept'])->name('mitra.report.accept');
        Route::post('/report/{id}/status', [MitraController::class, 'updateStatus'])->name('mitra.status');
        Route::post('/profile', [MitraController::class, 'updateProfile'])->name('mitra.profile.update');
        Route::post('/price-list', [MitraController::class, 'storePriceList'])->name('mitra.price-list.store');
        Route::delete('/price-list/{id}', [MitraController::class, 'destroyPriceList'])->name('mitra.price-list.destroy');
        Route::get('/client/{userId}', [MitraController::class, 'showClientDetails'])->name('mitra.client.show');
    });

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/reports/{id}/reroute', [AdminController::class, 'rerouteReport'])->name('admin.reports.reroute');
    Route::post('/reports/{id}/resolve', [AdminController::class, 'resolveReport'])->name('admin.reports.resolve');

    Route::get('/mitras', [AdminMitraController::class, 'index'])->name('admin.mitras');

    Route::get('/mitras/create', [AdminMitraController::class, 'create'])->name('admin.mitras.create');

    Route::post('/mitras', [AdminMitraController::class, 'store'])->name('admin.mitras.store');

    Route::patch('/mitras/{id}/verify', [AdminMitraController::class, 'toggleVerify'])->name('admin.mitras.verify');
    Route::patch('/mitras/{id}/active', [AdminMitraController::class, 'toggleActive'])->name('admin.mitras.active');

    // Export CSV untuk semua user
    Route::get('/users/export-csv', [\App\Http\Controllers\AdminUsersExportController::class, 'exportCsv'])
        ->name('admin.users.exportCsv');
});
});


require __DIR__.'/auth.php';
