<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Schema;

class Partner extends Model
{
    use HasUuids;

    protected $table = 'partners';

    protected $fillable = [
        'partner_name',
        'partner_type',
        'city',
        'address',
        'image_url',
        'phone',
        'email',
        'verified',
        'is_active',
        'latitude',
        'longitude',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $casts = [
        'verified' => 'boolean',
        'is_active' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function reportRoutings()
    {
        return $this->hasMany(ReportPartnerRouting::class);
    }

    public function routedReports()
    {
        return $this->belongsToMany(Report::class, 'report_partner_routings')
            ->withPivot(['status', 'routed_at', 'responded_at', 'expires_at'])
            ->withTimestamps();
    }

    public function priceLists()
    {
        return $this->hasMany(PriceList::class);
    }

    public function payments()
    {
        return $this->hasMany(UserPartnerPayment::class);
    }

    public static function routeByCategory($category)
    {
        return self::routeMultipleByCategory($category, 1)->first();
    }

    public static function partnerTypesForCategory($category): array
    {
        $normalized = strtolower(trim((string) $category));
        $normalized = str_replace(['_', '-'], ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return match ($normalized) {
            'ambulance', 'ambulans', 'medis', 'medis darurat', 'kesehatan', 'kecelakaan' => ['ambulance'],
            'legal', 'lbh', 'pengacara', 'bantuan hukum', 'salah tangkap', 'pelecehan' => ['legal'],
            'counselor', 'konselor', 'psikolog', 'psikososial' => ['counselor'],
            'pemadam', 'damkar', 'rescue', 'pemadam rescue', 'pemadam / rescue', 'kebakaran' => ['pemadam'],
            'kekerasan', 'ancaman' => ['legal', 'counselor'],
            'lainnya' => ['ambulance', 'legal', 'counselor', 'pemadam'],
            default => [],
        };
    }

    public static function matchesCategory($partnerType, $category): bool
    {
        return in_array(strtolower((string) $partnerType), self::partnerTypesForCategory($category), true);
    }

    public static function routeMultipleByCategory($category, $limit = 5, ?float $latitude = null, ?float $longitude = null)
    {
        $partnerTypes = self::partnerTypesForCategory($category);

        if (!$partnerTypes) {
            return collect();
        }

        $normalized = strtolower(trim((string) $category));
        $normalized = str_replace(['_', '-'], ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        if ($normalized === 'lainnya') {
            // Get all active partners of the four types
            $query = self::query()
                ->whereIn('partner_type', ['ambulance', 'legal', 'counselor', 'pemadam']);

            if (Schema::hasColumn('partners', 'is_active')) {
                $query->where('is_active', true);
            }

            $allPartners = $query->get();

            // Calculate distance for all partners
            if ($latitude !== null && $longitude !== null) {
                $allPartners = $allPartners->map(function ($partner) use ($latitude, $longitude) {
                    $partner->distance_km = ($partner->latitude !== null && $partner->longitude !== null)
                        ? self::haversineKm($latitude, $longitude, (float) $partner->latitude, (float) $partner->longitude)
                        : PHP_FLOAT_MAX;
                    return $partner;
                });
            } else {
                $allPartners = $allPartners->map(function ($partner) {
                    $partner->distance_km = PHP_FLOAT_MAX;
                    return $partner;
                });
            }

            // Group all partners by type
            $grouped = $allPartners->groupBy('partner_type');

            $selectedPartners = collect();
            $types = ['ambulance', 'legal', 'counselor', 'pemadam'];

            // 1. For each type, get the closest (verified desc, distance_km asc) partner
            foreach ($types as $type) {
                $partnersOfType = $grouped->get($type, collect());
                if ($partnersOfType->isNotEmpty()) {
                    $closest = $partnersOfType->sortBy([
                        ['verified', 'desc'],
                        ['distance_km', 'asc'],
                    ])->first();
                    $selectedPartners->push($closest);
                }
            }

            // 2. We need to backfill up to $limit partners from the remaining pool
            $selectedIds = $selectedPartners->pluck('id')->toArray();
            $remainingPool = $allPartners->reject(function ($partner) use ($selectedIds) {
                return in_array($partner->id, $selectedIds);
            });

            $needed = $limit - $selectedPartners->count();
            if ($needed > 0 && $remainingPool->isNotEmpty()) {
                $backfill = $remainingPool->sortBy([
                    ['verified', 'desc'],
                    ['distance_km', 'asc'],
                ])->take($needed);

                foreach ($backfill as $partner) {
                    $selectedPartners->push($partner);
                }
            }

            // Finally, sort the limit partners by verified desc and distance_km asc
            return $selectedPartners->sortBy([
                ['verified', 'desc'],
                ['distance_km', 'asc'],
            ])->values();
        }

        $query = self::query()
            ->whereIn('partner_type', $partnerTypes)
            ->orderByDesc('verified');

        if (Schema::hasColumn('partners', 'is_active')) {
            $query->where('is_active', true);
        }

        $partners = $query->get();

        if ($latitude !== null && $longitude !== null) {
            $partners = $partners
                ->map(function ($partner) use ($latitude, $longitude) {
                    $partner->distance_km = ($partner->latitude !== null && $partner->longitude !== null)
                        ? self::haversineKm($latitude, $longitude, (float) $partner->latitude, (float) $partner->longitude)
                        : PHP_FLOAT_MAX;

                    return $partner;
                })
                ->sortBy([
                    ['verified', 'desc'],
                    ['distance_km', 'asc'],
                ])
                ->values();
        }

        return $partners->take($limit)->values();
    }

    public static function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        return self::haversineKm($lat1, $lon1, $lat2, $lon2);
    }

    private static function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}
