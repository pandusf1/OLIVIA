<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardEmergencyMarkersController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $userLocation = $user->userLocation()->first();

        if (!$userLocation) {
            return response()->json(['data' => [], 'error' => 'Lokasi user belum tersedia.'], 422);
        }

        $lat = (float) $userLocation->latitude;
        $lng = (float) $userLocation->longitude;

        // Laporan emergency yang active dan ditujukan ke user login
        $activeStatuses = ['Submitted', 'Routed', 'Viewed', 'In Progress'];

        $markers = \App\Models\Report::query()
            ->where('report_type', 'Emergency')
            ->whereIn('status', $activeStatuses)
            ->where(function($q) use ($user) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', '!=', $user->id);
            })
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with(['user'])
            ->get()
            ->map(function ($r) use ($lat, $lng) {
                $distanceKm = $this->haversineKm($lat, $lng, (float) $r->latitude, (float) $r->longitude);

                $victimName = 'Anonim';
                if ($r->user && !$r->anonymous) {
                    $victimName = $r->user->name;
                }

                return [
                    'id' => $r->id,
                    'category' => $r->category,
                    'victim_name' => $victimName,
                    'latitude' => (float) $r->latitude,
                    'longitude' => (float) $r->longitude,
                    'status' => $r->status,
                    'created_at' => optional($r->created_at)->toISOString(),
                    'distance_km' => round($distanceKm, 2),
                ];
            })
            ->sortBy('distance_km')
            ->values();

        return response()->json(['data' => $markers]);
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

