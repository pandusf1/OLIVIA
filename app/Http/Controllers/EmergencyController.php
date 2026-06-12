<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportStatusLog;
use App\Models\Mitra;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserLocation;
use App\Models\ReportMitraRouting;
use App\Models\ReportTimelineEvent;
use App\Models\ReportUserRouting;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class EmergencyController extends Controller
{
    public function index()
    {
        return view('pages.emergency');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
        ]);

        // Batasi pengguna tamu hanya 1 laporan aktif yang belum selesai di perangkat yang sama
        if (!auth()->check()) {
            $cookieIds = [];
            if ($request->hasCookie('safora_my_reports')) {
                $cookieIds = json_decode($request->cookie('safora_my_reports'), true) ?: [];
            }
            $sessionIds = $request->session()->get('my_reports', []);
            $allIds = array_unique(array_merge($cookieIds, $sessionIds));

            $hasActiveReport = false;
            if (!empty($allIds)) {
                $hasActiveReport = \App\Models\Report::whereIn('id', $allIds)
                    ->where('status', '!=', 'Resolved')
                    ->exists();
            }

            if ($hasActiveReport) {
                return response()->json([
                    'error' => 'Tidak bisa membuat laporan baru. Mohon login untuk membuat lebih dari 1 laporan.'
                ], 400);
            }
        }

        $userId = null;

        try {
            $userId = auth()->id();
        } catch (\Exception $e) {
            $userId = null;
        }

        $idempotencyKey = $request->header('Idempotency-Key') ?: $request->input('idempotency_key');
        $anonymous = filter_var($request->anonymous ?? false, FILTER_VALIDATE_BOOLEAN);

        \Log::info("Safora Emergency: Memulai proses penyimpanan laporan darurat.", [
            'category' => $request->category,
            'anonymous' => $anonymous,
            'user_id' => $userId ?: 'Guest/Anonim',
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'idempotency_key' => $idempotencyKey,
        ]);


        // unknown_emergency: jangan route ke mitra
        $mitras = collect();
        if (strtolower((string) $request->category) !== 'unknown_emergency') {
            if ($request->latitude && $request->longitude) {
                $mitras = Mitra::routeMultipleByCategory(
                    $request->category,
                    5,
                    (float) $request->latitude,
                    (float) $request->longitude
                );

                $mitras = $mitras->filter(function($p) use ($request) {
                    $dist = Mitra::distanceKm((float) $request->latitude, (float) $request->longitude, (float) $p->latitude, (float) $p->longitude);
                    return $dist <= 20.0;
                });
            }
        }
        $firstMitra = $mitras->first();
        $expiresAt = null; // Tidak ada batas waktu kedaluwarsa statis untuk laporan darurat


        $urgency = $this->determineUrgency($request->category);

        if (!empty($idempotencyKey)) {
            $existing = Report::query()
                ->where('report_type', 'Emergency')
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'ok' => true,
                        'report_id' => $existing->id,
                        'tracking_url' => url('/tracking/' . $existing->id),
                    ]);
                }
                return redirect('/tracking/' . $existing->id);
            }
        }

        $report = DB::transaction(function () use ($request, $mitras, $firstMitra, $expiresAt, $urgency, $idempotencyKey, $anonymous) {
            $report = Report::create([
                'user_id' => auth()->id(),
                'report_type' => 'Emergency',
                'category' => $request->category,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'location_text' => $request->location_text,
                'anonymous' => $anonymous,
                'idempotency_key' => $idempotencyKey,


                'status' => ($mitras->isNotEmpty()) ? 'Routed' : 'Submitted',
                'urgency_level' => $urgency,

                'routed_mitra_id' => $firstMitra?->id,
                'location_verified_at' => ($request->latitude && $request->longitude) ? now() : null,
                'last_activity_at' => now(),
            ]);

            ReportStatusLog::create([
                'report_id'  => $report->id,
                'old_status' => null,
                'new_status' => 'Submitted',
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);

            if ($mitras->isNotEmpty()) {
                ReportStatusLog::create([
                    'report_id' => $report->id,
                    'old_status' => 'Submitted',
                    'new_status' => 'Routed',
                    'changed_by' => auth()->id(),
                    'changed_at' => now(),
                ]);
            }

            $this->timeline($report, 'report_submitted', 'Laporan darurat Anda sudah kami terima. Anda tidak sendirian, Safora sedang memproses bantuan.');

            if ($report->location_verified_at) {
                $this->timeline($report, 'gps_verified', 'Lokasi GPS berhasil diterima sehingga mitra dapat melihat area kejadian lebih cepat.');
            } else {
                $this->timeline($report, 'gps_unavailable', 'GPS belum tersedia. Kami tetap meneruskan laporan dan Anda bisa membagikan lokasi lewat chat nanti.');
            }

            if ($mitras->isNotEmpty()) {
                $this->timeline(
                    $report,
                    'forwarded_to_mitras',
                    'Laporan Anda telah diteruskan ke ' . $mitras->count() . ' institusi terdekat yang relevan.'
                );
                $this->timeline($report, 'waiting_for_mitra', 'Kami sedang menunggu mitra tersedia menerima kasus ini. Estimasi respons awal 3-5 menit.');
            } else {
                $this->timeline($report, 'no_mitra_found', 'Kami belum menemukan mitra yang sesuai. Admin Safora akan tetap diberi peringatan.');
            }

            try {
                AuditLog::log('create_report', 'report', $report->id);
            } catch (\Exception $e) {
                // skip jika offline
            }

            foreach ($mitras as $mitra) {
                ReportMitraRouting::create([
                    'report_id' => $report->id,
                    'mitra_id' => $mitra->id,
                    'status' => 'pending',
                    'routed_at' => now(),
                    'expires_at' => $expiresAt,
                    'distance_km' => ($report->latitude && $report->longitude && $mitra->latitude && $mitra->longitude)
                        ? Mitra::distanceKm((float) $report->latitude, (float) $report->longitude, (float) $mitra->latitude, (float) $mitra->longitude)
                        : null,
                    'estimated_response_minutes' => $mitra->mitra_type === 'ambulance' ? 5 : 8,
                ]);

                try {
                    AuditLog::log('route_report_to_mitra', 'report', $report->id);
                } catch (\Exception $e) {
                    // skip jika offline
                }
            }

            return $report;
        });

        // Simpan ke session dan cookie 30 hari untuk ketahanan pelacakan guest/anonim
        $cookieReports = [];
        if ($request->hasCookie('safora_my_reports')) {
            $cookieReports = json_decode($request->cookie('safora_my_reports'), true) ?: [];
        }
        $sessionReports = $request->session()->get('my_reports', []);
        $allReports = array_unique(array_merge($cookieReports, $sessionReports, [$report->id]));

        $request->session()->put('my_reports', $allReports);
        cookie()->queue(cookie('safora_my_reports', json_encode($allReports), 60 * 24 * 30));

        $trackingLink = url('/tracking/' . $report->id);
        $mapsLink = $report->latitude
            ? "https://maps.google.com/?q={$report->latitude},{$report->longitude}"
            : 'Lokasi tidak tersedia';

        // Persiapkan objek response
        if ($request->expectsJson()) {
            $response = response()->json([
                'ok' => true,
                'report_id' => $report->id,
                'tracking_url' => $trackingLink,
                'call_phone' => $firstMitra?->phone ?? env('DEFAULT_EMERGENCY_PHONE', '112'),
            ]);
        } else {
            $response = redirect('/tracking/' . $report->id);
        }

        // Kirim respon awal ke client
        if (function_exists('fastcgi_finish_request')) {
            $response->send();
            fastcgi_finish_request();
        }

        // Kirim notifikasi WhatsApp ke mitra
        foreach ($mitras as $mitra) {
            if ($mitra->phone) {
                $mitraMessage =
                    "🚨 *Safora - Laporan Darurat Baru*\n\n" .
                    "Kategori: *{$report->category}*\n\n" .
                    "📍 Lokasi:\n{$mapsLink}\n\n" .
                    "🔗 Tracking:\n{$trackingLink}\n\n" .
                    "Buka dashboard Safora untuk menerima laporan ini.";

                try {
                    FonnteService::send($mitra->phone, $mitraMessage);
                } catch (\Exception $e) {
                    // skip jika layanan WA tidak tersedia
                }
            }
        }

        // Kirim ke admin untuk testing
        if (env('ADMIN_PHONE') && $mitras->isNotEmpty()) {
            $mitraMessage =
                "🚨 *Safora - Laporan Darurat Baru (Test Mitra)*\n\n" .
                "Kategori: *{$report->category}*\n\n" .
                "📍 Lokasi:\n{$mapsLink}\n\n" .
                "🔗 Tracking:\n{$trackingLink}\n\n" .
                "Buka dashboard Safora untuk menerima laporan ini.";

            try {
                FonnteService::send(env('ADMIN_PHONE'), $mitraMessage);
            } catch (\Exception $e) {
                // skip jika layanan WA tidak tersedia
            }
        }

        // Alert ke admin / nomor utama
        $adminMessage =
            "🚨 *Safora EMERGENCY ALERT*\n\n" .
            "Kategori: *{$report->category}*\n" .
            "Status: {$report->status}\n" .
            "Mitra diroute: {$mitras->count()}\n" .
            "Anonim: " . ($report->anonymous ? 'Ya' : 'Tidak') . "\n\n" .
            "📍 Lokasi:\n{$mapsLink}\n\n" .
            "🔗 Tracking:\n{$trackingLink}";

        try {
            FonnteService::send(env('ADMIN_PHONE', '6285124019353'), $adminMessage);
        } catch (\Exception $e) {
            // skip jika offline
        }

        // Alert ke trusted contacts jika user login
        if ($userId) {
            if ($this->notifyTrustedContacts($report, $trackingLink, $mapsLink, $firstMitra)) {
                $this->timeline($report, 'trusted_contacts_notified', 'Kontak terpercaya Anda sudah kami beri tautan tracking dan lokasi laporan.');
            }
        }

        // Alert ke 3 user terdekat (role='user') via users.phone + simpan routing
        $this->notifyNearestUsers($report, $trackingLink, $mapsLink, 3, $firstMitra);

        \Log::info("Safora Emergency: Laporan darurat berhasil dibuat & diroute.", [
            'report_id' => $report->id,
            'status' => $report->status,
            'routed_mitras_count' => $mitras->count(),
            'tracking_url' => $trackingLink,
        ]);

        return $response;
    }

    private function timeline(Report $report, string $type, string $message, array $metadata = []): void
    {
        ReportTimelineEvent::create([
            'report_id' => $report->id,
            'event_type' => $type,
            'event_message' => $message,
            'actor_type' => auth()->check() ? 'user' : 'system',
            'actor_id' => auth()->id(),
            'metadata' => $metadata ?: null,
        ]);
    }

    private function determineUrgency(string $category): string
    {
        return match (strtolower($category)) {
            'kekerasan', 'kesehatan', 'kecelakaan', 'kebakaran', 'ambulance', 'pemadam' => 'critical',
            'pelecehan', 'legal', 'counselor', 'bullying', 'perundungan' => 'high',
            default => 'normal',
        };
    }

    private function notifyTrustedContacts(Report $report, string $trackingLink, string $mapsLink, ?Mitra $mitra = null): bool
    {
        $user = $report->user;
        if (!$user) {
            return false;
        }

        $contacts = $user->trustedContacts()->where('is_verified', true)->get();
        if ($contacts->isEmpty()) {
            return false;
        }

        $helpInstruction = "";
        if ($mitra && $mitra->phone) {
            $helpInstruction = "\n\nTolong laporkan/hubungi *{$mitra->mitra_name}* di nomor *{$mitra->phone}*, bantu korban agar cepat tertangani.";
        }

        $sent = false;
        foreach ($contacts as $contact) {
            $message =
                "🚨 *ALERT DARURAT — Safora*\n\n" .
                "{$user->name} memerlukan bantuan!\n\n" .
                "Kategori: *{$report->category}*\n\n" .
                "📍 Lokasi:\n{$mapsLink}\n\n" .
                "🔗 Pantau status:\n{$trackingLink}" .
                $helpInstruction . "\n\n" .
                "_Pesan ini dikirim otomatis oleh Safora._";

            try {
                FonnteService::send($contact->contact_phone, $message);
                $sent = true;
            } catch (\Exception $e) {
                // skip jika layanan WA tidak tersedia
            }
        }

        return $sent;
    }

    private function notifyNearestUsers(Report $report, string $trackingLink, string $mapsLink, int $limit = 3, ?Mitra $mitra = null): void
    {
        if (!$report->latitude || !$report->longitude) {
            return;
        }

        $fromUserId = $report->user_id;

        $helpInstruction = "";
        if ($mitra && $mitra->phone) {
            $helpInstruction = "\n\nTolong laporkan/hubungi *{$mitra->mitra_name}* di nomor *{$mitra->phone}*, bantu korban agar cepat tertangani.";
        }

        $message =
            "🚨 *ALERT DARURAT — Safora*\n\n" .
            "Ada korban meminta pertolongan!\n\n" .
            "Kategori: *{$report->category}*\n\n" .
            "📍 Lokasi:\n{$mapsLink}\n\n" .
            "🔗 Pantau status:\n{$trackingLink}" .
            $helpInstruction . "\n\n" .
            "_Pesan ini dikirim otomatis oleh Safora._";

        // Ambil semua lokasi user dengan role='user'
        $targetsQuery = UserLocation::query()
            ->whereHas('user', function ($q) {
                $q->where('role', 'user')->where('receive_nearby_alerts', true);
            });

        if ($fromUserId) {
            $targetsQuery->where('user_id', '!=', $fromUserId);
        }

        $targets = $targetsQuery->get();

        if ($targets->isEmpty()) {
            return;
        }

        $lat = (float) $report->latitude;
        $lng = (float) $report->longitude;

        $targetsWithDistance = $targets
            ->map(function ($loc) use ($lat, $lng) {
                $distanceKm = $this->haversineKm($lat, $lng, (float) $loc->latitude, (float) $loc->longitude);

                return [
                    'user_id' => $loc->user_id,
                    'distance_km' => $distanceKm,
                ];
            })
            ->filter(function ($t) {
                return $t['distance_km'] <= 5.0;
            })
            ->sortBy('distance_km')
            ->take($limit)
            ->values();

        foreach ($targetsWithDistance as $t) {
            $user = User::query()->where('id', $t['user_id'])->where('role', 'user')->first();
            if (!$user || !$user->phone) {
                continue;
            }

            // WA
            try {
                FonnteService::send($user->phone, $message);

                $user->nearby_alert_count += 1;
                if ($user->nearby_alert_count >= $user->next_nearby_alert_threshold) {
                    $settingUrl = url('/settings');
                    $noticeMsg = "ℹ️ *Info Safora*\nAnda telah menerima beberapa alert korban terdekat. Jika Anda merasa terganggu, Anda bisa menonaktifkan fitur ini di pengaturan aplikasi.\n\nAtur di sini: {$settingUrl}";
                    FonnteService::send($user->phone, $noticeMsg);

                    $user->nearby_alert_count = 0;
                    $user->next_nearby_alert_threshold += 3;
                }
                $user->save();
            } catch (\Throwable $e) {
                \Log::error('notifyNearestUsers Fonnte send failed', [
                    'target_user_id' => $user->id,
                    'target_phone' => $user->phone,
                    'error' => $e->getMessage(),
                ]);
            }


            // routing record
            ReportUserRouting::create([
                'report_id' => $report->id,
                'target_user_id' => $user->id,
                'routed_at' => now(),
            ]);
        }
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
