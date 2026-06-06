<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use Illuminate\Http\Request;

class MapSearchController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'type' => 'nullable|string',
            'query' => 'nullable|string',
        ]);

        $user = auth()->user();
        $userLocation = $user?->userLocation()->first();
        if (!$userLocation) {
            // Debug: berikan clue kenapa lokasi belum ada.
            $debugLocationExists = \App\Models\UserLocation::where('user_id', auth()->id())->first();

            // Debug tambahan: kirim juga nilai latitude/longitude jika ada record-nya.
            $debugLoc = $debugLocationExists;
            return response()->json([
                'data' => [],
                'error' => 'Lokasi user belum tersedia (relasi userLocation null).',
                'user_id' => auth()->id(),
                'user_location_table_found' => (bool) $debugLocationExists,
                'user_location_debug' => $debugLoc ? [
                    'latitude' => $debugLoc->latitude,
                    'longitude' => $debugLoc->longitude,
                ] : null,
            ], 200);

        }




        $lat = (float) $userLocation->latitude;
        $lng = (float) $userLocation->longitude;

        $type = $request->string('type')->toString();
        $q = $request->string('query')->toString();

        $mitrasQuery = Mitra::query()
            ->where('verified', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // Map type to mitra_type
        if ($type !== '') {
            $normalized = strtolower($type);

            $mitrasQuery->where(function ($qq) use ($normalized) {
                $qq->where('mitra_type', 'ilike', '%'.$normalized.'%')
                    ->orWhere('mitra_type', '=', $this->normalizeMitraType($normalized));
            });
        }

        if ($q !== '') {
            $mitrasQuery->where(function ($qq) use ($q) {
                $qq->where('mitra_name', 'ilike', '%'.$q.'%')
                    ->orWhere('city', 'ilike', '%'.$q.'%')
                    ->orWhere('mitra_type', 'ilike', '%'.$q.'%');
            });
        }

        $mitras = $mitrasQuery->get();

        $mitrasWithDistance = $mitras->map(function ($mitra) use ($lat, $lng) {
            return [
                'mitra' => $mitra,
                'distance_km' => round($this->haversineKm($lat, $lng, (float) $mitra->latitude, (float) $mitra->longitude), 2),
            ];
        })
        ->sortBy('distance_km')
        ->values();

        $page = $request->integer('page', 1);
        $limit = (int) $request->integer('limit', 0);
        // Default: tampilkan semua mitra (hapus batas 20)
        if ($limit <= 0) {
            $limit = $mitrasWithDistance->count();
        }
        $offset = ($page - 1) * $limit;

        $paginatedMitras = $mitrasWithDistance->slice($offset, $limit)->values();
        $hasMore = ($offset + $limit) < $mitrasWithDistance->count();

        // Get active emergency reports with locations
        $activeReports = \App\Models\Report::with('user')
            ->where('report_type', 'Emergency')
            ->where('status', '!=', 'Resolved')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($report) use ($lat, $lng) {
                return [
                    'id' => $report->id,
                    'category' => $report->category,
                    'latitude' => $report->latitude,
                    'longitude' => $report->longitude,
                    'victim_name' => $report->anonymous ? 'Anonim' : ($report->user->name ?? 'Anonim'),
                    'distance_km' => round($this->haversineKm($lat, $lng, (float) $report->latitude, (float) $report->longitude), 2),
                ];
            })
            ->sortBy('distance_km')
            ->values();

        return response()->json([
            'data' => $paginatedMitras,
            'emergencies' => $activeReports,
            'has_more' => $hasMore,
        ]);
    }

    private function normalizeMitraType(string $type): string
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

