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

    public static function routeMultipleByCategory($category, $limit = 5, ?float $latitude = null, ?float $longitude = null)
    {
        $partnerTypes = match (strtolower((string) $category)) {
            'salah tangkap', 'pelecehan' => ['legal'],
            'kecelakaan', 'kesehatan' => ['ambulance'],
            'kekerasan' => ['counselor', 'legal'],
            'ancaman' => ['legal', 'counselor'],
            'lainnya' => ['ambulance', 'legal', 'counselor', 'pemadam'],
            default => ['ambulance', 'legal', 'counselor'],
        };

        if (!$partnerTypes) {
            return collect();
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
