<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerNearbyController extends Controller
{
    public function index()
    {
        $userLocation = auth()->user()->userLocation()->first();
        if (!$userLocation) {
            return response()->json(['error' => 'Lokasi user belum tersedia.'], 422);
        }

        $partners = Partner::query()
            ->where('verified', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $lat = $userLocation->latitude;
        $lng = $userLocation->longitude;

        $partnersWithDistance = $partners->map(function ($partner) use ($lat, $lng) {
            $distanceKm = $this->haversineKm($lat, $lng, (float) $partner->latitude, (float) $partner->longitude);

            return [
                'partner' => $partner,
                'distance_km' => $distanceKm,
            ];
        })
        ->sortBy('distance_km')
        ->values();

        return response()->json(['data' => $partnersWithDistance]);
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
