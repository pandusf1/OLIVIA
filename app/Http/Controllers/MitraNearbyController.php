<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use Illuminate\Http\Request;

class MitraNearbyController extends Controller
{
    public function index()
    {
        $userLocation = auth()->user()->userLocation()->first();
        if (!$userLocation) {
            return response()->json(['error' => 'Lokasi user belum tersedia.'], 422);
        }

        $mitras = Mitra::query()
            ->where('verified', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $lat = $userLocation->latitude;
        $lng = $userLocation->longitude;

        // Tidak ada batas radius untuk memastikan tetap ada hasil terdekat,
        // sekalipun jaraknya sangat jauh.
        $mitrasWithDistance = $mitras->map(function ($mitra) use ($lat, $lng) {
            $distanceKm = $this->haversineKm($lat, $lng, (float) $mitra->latitude, (float) $mitra->longitude);

            return [
                'mitra' => $mitra,
                'distance_km' => round($distanceKm, 2),
            ];
        })
        ->sortBy('distance_km')
        ->values();

        return response()->json(['data' => $mitrasWithDistance]);
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
