<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportStatusLog;
use App\Models\Partner;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserLocation;
use App\Models\ReportUserRouting;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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

        $partner = Partner::routeByCategory($request->category);

        $report = Report::create([
            'user_id' => auth()->id(),
            'report_type' => 'Emergency',
            'category' => $request->category,
            'description' => $request->description,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_text' => $request->location_text,
            'anonymous' => $request->anonymous ?? false,

            'status' => $partner ? 'Routed' : 'Submitted',

            'routed_partner_id' => $partner?->id,
        ]);

        ReportStatusLog::create([
            'report_id'  => $report->id,
            'old_status' => null,
            'new_status' => 'Submitted',
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);

        if ($partner) {
            ReportStatusLog::create([
                'report_id' => $report->id,
                'old_status' => 'Submitted',
                'new_status' => 'Routed',
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);
        }

        try {
            AuditLog::log('create_report', 'report', $report->id);
        } catch (\Exception $e) {
            // skip jika offline
        }

        $trackingLink = url('/tracking/' . $report->id);
        $mapsLink = $report->latitude
            ? "https://maps.google.com/?q={$report->latitude},{$report->longitude}"
            : 'Lokasi tidak tersedia';

        // Alert ke admin / nomor utama
        $adminMessage =
            "🚨 *SAVORA EMERGENCY ALERT*\n\n" .
            "Kategori: *{$report->category}*\n" .
            "Status: Submitted\n" .
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
            $this->notifyTrustedContacts($report, $trackingLink, $mapsLink);
        }

        // Alert ke 3 user terdekat (role='user') via users.phone + simpan routing
        $this->notifyNearestUsers($report, $trackingLink, $mapsLink, 3);

        return redirect('/tracking/' . $report->id);
    }

    private function notifyTrustedContacts(Report $report, string $trackingLink, string $mapsLink): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        $contacts = $user->trustedContacts;
        if ($contacts->isEmpty()) {
            return;
        }

        foreach ($contacts as $contact) {
            $message =
                "🚨 *ALERT DARURAT — Savora*\n\n" .
                "{$user->name} memerlukan bantuan!\n\n" .
                "Kategori: *{$report->category}*\n\n" .
                "📍 Lokasi:\n{$mapsLink}\n\n" .
                "🔗 Pantau status:\n{$trackingLink}\n\n" .
                "_Pesan ini dikirim otomatis oleh Savora._";

            FonnteService::send($contact->contact_phone, $message);
        }
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
            "🚨 *ALERT DARURAT — Savora*\n\n" .
            "Ada korban meminta pertolongan!\n\n" .
            "Kategori: *{$report->category}*\n\n" .
            "📍 Lokasi:\n{$mapsLink}\n\n" .
            "🔗 Pantau status:\n{$trackingLink}\n\n" .
            "_Pesan ini dikirim otomatis oleh Savora._";

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
            FonnteService::send($user->phone, $message);

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

