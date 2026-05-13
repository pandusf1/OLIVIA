<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;

class PsikologPengacaraController extends Controller
{
    public function index(Request $request)
    {
        $userLocation = auth()->user()->userLocation()->first();
        if (!$userLocation) {
            return response()->json(['error' => 'Lokasi user belum tersedia.'], 422);
        }

        $lat = (float) $userLocation->latitude;
        $lng = (float) $userLocation->longitude;

        // ambil berdasarkan partner_type
        $legal = Partner::where('verified', true)
            ->where('partner_type', 'legal')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $counselor = Partner::where('verified', true)
            ->where('partner_type', 'counselor')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $legalSorted = $legal->map(fn ($p) => [
            'partner' => $p,
            'distance_km' => $this->haversineKm($lat, $lng, (float) $p->latitude, (float) $p->longitude),
        ])->sortBy('distance_km')->values()->take(3);

        $counselorSorted = $counselor->map(fn ($p) => [
            'partner' => $p,
            'distance_km' => $this->haversineKm($lat, $lng, (float) $p->latitude, (float) $p->longitude),
        ])->sortBy('distance_km')->values()->take(3);

        return response()->json([
            'legal' => $legalSorted,
            'counselor' => $counselorSorted,
        ]);
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
