<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportStatusLog;
use App\Models\Partner;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserLocation;
use App\Models\ReportPartnerRouting;
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

        $userId = null;

        try {
            $userId = auth()->id();
        } catch (\Exception $e) {
            $userId = null;
        }

        $partners = Partner::routeMultipleByCategory(
            $request->category,
            5,
            $request->latitude ? (float) $request->latitude : null,
            $request->longitude ? (float) $request->longitude : null
        );
        $firstPartner = $partners->first();
        $expiryMinutes = (int) env('REPORT_ROUTING_EXPIRY_MINUTES', 3);
        $expiresAt = now()->addMinutes(max(1, $expiryMinutes));
        $urgency = $this->determineUrgency($request->category);

        $report = DB::transaction(function () use ($request, $partners, $firstPartner, $expiresAt, $urgency) {
            $report = Report::create([
                'user_id' => auth()->id(),
                'report_type' => 'Emergency',
                'category' => $request->category,
                'description' => $request->description,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'location_text' => $request->location_text,
                'anonymous' => $request->anonymous ?? false,

                'status' => $partners->isNotEmpty() ? 'Routed' : 'Submitted',
                'urgency_level' => $urgency,

                'routed_partner_id' => $firstPartner?->id,
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

            if ($partners->isNotEmpty()) {
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
                $this->timeline($report, 'gps_verified', 'Lokasi GPS berhasil diterima sehingga partner dapat melihat area kejadian lebih cepat.');
            } else {
                $this->timeline($report, 'gps_unavailable', 'GPS belum tersedia. Kami tetap meneruskan laporan dan Anda bisa membagikan lokasi lewat chat nanti.');
            }

            if ($partners->isNotEmpty()) {
                $this->timeline(
                    $report,
                    'forwarded_to_partners',
                    'Laporan Anda telah diteruskan ke ' . $partners->count() . ' institusi terdekat yang relevan.'
                );
                $this->timeline($report, 'waiting_for_partner', 'Kami sedang menunggu partner tersedia menerima kasus ini. Estimasi respons awal 3-5 menit.');
            } else {
                $this->timeline($report, 'no_partner_found', 'Kami belum menemukan partner yang sesuai. Admin Safora akan tetap diberi peringatan.');
            }

            try {
                AuditLog::log('create_report', 'report', $report->id);
            } catch (\Exception $e) {
                // skip jika offline
            }

            foreach ($partners as $partner) {
                ReportPartnerRouting::create([
                    'report_id' => $report->id,
                    'partner_id' => $partner->id,
                    'status' => 'pending',
                    'routed_at' => now(),
                    'expires_at' => $expiresAt,
                    'distance_km' => ($report->latitude && $report->longitude && $partner->latitude && $partner->longitude)
                        ? Partner::distanceKm((float) $report->latitude, (float) $report->longitude, (float) $partner->latitude, (float) $partner->longitude)
                        : null,
                    'estimated_response_minutes' => $partner->partner_type === 'ambulance' ? 5 : 8,
                ]);

                try {
                    AuditLog::log('route_report_to_partner', 'report', $report->id);
                } catch (\Exception $e) {
                    // skip jika offline
                }
            }

            return $report;
        });

        $trackingLink = url('/tracking/' . $report->id);
        $mapsLink = $report->latitude
            ? "https://maps.google.com/?q={$report->latitude},{$report->longitude}"
            : 'Lokasi tidak tersedia';

        foreach ($partners as $partner) {
            if ($partner->phone) {
                $partnerMessage =
                    "🚨 *Safora - Laporan Darurat Baru*\n\n" .
                    "Kategori: *{$report->category}*\n\n" .
                    "📍 Lokasi:\n{$mapsLink}\n\n" .
                    "🔗 Tracking:\n{$trackingLink}\n\n" .
                    "Buka dashboard Safora untuk menerima laporan ini.";

                try {
                    FonnteService::send($partner->phone, $partnerMessage);
                } catch (\Exception $e) {
                    // skip jika layanan WA tidak tersedia
                }
            }
        }

        // Alert ke admin / nomor utama
        $adminMessage =
            "🚨 *Safora EMERGENCY ALERT*\n\n" .
            "Kategori: *{$report->category}*\n" .
            "Status: {$report->status}\n" .
            "Partner diroute: {$partners->count()}\n" .
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
            if ($this->notifyTrustedContacts($report, $trackingLink, $mapsLink)) {
                $this->timeline($report, 'trusted_contacts_notified', 'Kontak terpercaya Anda sudah kami beri tautan tracking dan lokasi laporan.');
            }
        }

        // Alert ke 3 user terdekat (role='user') via users.phone + simpan routing
        $this->notifyNearestUsers($report, $trackingLink, $mapsLink, 3);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'report_id' => $report->id,
                'tracking_url' => $trackingLink,
            ]);
        }

        return redirect('/tracking/' . $report->id);
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
            'kekerasan', 'kesehatan', 'kecelakaan', 'ancaman' => 'critical',
            'pelecehan' => 'high',
            default => 'normal',
        };
    }

    private function notifyTrustedContacts(Report $report, string $trackingLink, string $mapsLink): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        $contacts = $user->trustedContacts;
        if ($contacts->isEmpty()) {
            return false;
        }

        $sent = false;
        foreach ($contacts as $contact) {
            $message =
                "🚨 *ALERT DARURAT — Safora*\n\n" .
                "{$user->name} memerlukan bantuan!\n\n" .
                "Kategori: *{$report->category}*\n\n" .
                "📍 Lokasi:\n{$mapsLink}\n\n" .
                "🔗 Pantau status:\n{$trackingLink}\n\n" .
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

    private function notifyNearestUsers(Report $report, string $trackingLink, string $mapsLink, int $limit = 3): void
    {
        if (!$report->latitude || !$report->longitude) {
            return;
        }

        $fromUserId = $report->user_id;
        if (!$fromUserId) {
            return;
        }

        $message =
            "🚨 *ALERT DARURAT — Safora*\n\n" .
            "Ada korban meminta pertolongan!\n\n" .
            "Kategori: *{$report->category}*\n\n" .
            "📍 Lokasi:\n{$mapsLink}\n\n" .
            "🔗 Pantau status:\n{$trackingLink}\n\n" .
            "_Pesan ini dikirim otomatis oleh Safora._";

        // Ambil semua lokasi user dengan role='user'
        $targets = UserLocation::query()
            ->whereHas('user', function ($q) {
                $q->where('role', 'user');
            })
            ->where('user_id', '!=', $fromUserId)
            ->get();

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
            } catch (\Exception $e) {
                // skip jika layanan WA tidak tersedia
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
