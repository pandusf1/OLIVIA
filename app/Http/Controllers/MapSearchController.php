<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;

class MapSearchController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'type' => 'nullable|string',
            'query' => 'nullable|string',
        ]);

        $userLocation = auth()->user()->userLocation()->first();
        if (!$userLocation) {
            return response()->json(['error' => 'Lokasi user belum tersedia.'], 422);
        }

        $lat = (float) $userLocation->latitude;
        $lng = (float) $userLocation->longitude;

        $type = $request->string('type')->toString();
        $q = $request->string('query')->toString();

        $partnersQuery = Partner::query()
            ->where('verified', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // Map type to partner_type
        if ($type !== '') {
            $normalized = strtolower($type);

            $partnersQuery->where(function ($qq) use ($normalized) {
                $qq->where('partner_type', 'ilike', '%'.$normalized.'%')
                    ->orWhere('partner_type', '=', $this->normalizePartnerType($normalized));
            });
        }

        if ($q !== '') {
            $partnersQuery->where(function ($qq) use ($q) {
                $qq->where('partner_name', 'ilike', '%'.$q.'%')
                    ->orWhere('city', 'ilike', '%'.$q.'%')
                    ->orWhere('partner_type', 'ilike', '%'.$q.'%');
            });
        }

        $partners = $partnersQuery->get();

        $partnersWithDistance = $partners->map(function ($partner) use ($lat, $lng) {
            return [
                'partner' => $partner,
                'distance_km' => $this->haversineKm($lat, $lng, (float) $partner->latitude, (float) $partner->longitude),
            ];
        })
        ->sortBy('distance_km')
        ->values();

        return response()->json([
            'data' => $partnersWithDistance,
        ]);
    }

    private function normalizePartnerType(string $type): string
    {
        return match ($type) {
            'ambulance', 'ambulans' => 'ambulance',
            'lbh', 'lb h', 'legal', 'pengacara', 'pengacara legal' => 'legal',
            'psikolog', 'konselor', 'counselor', 'konseling' => 'counselor',
            default => $type,
        };
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

