<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Schema;

class Mitra extends Model
{
    use HasUuids;

    protected $table = 'mitras';

    protected $fillable = [
        'mitra_name',
        'mitra_type',
        'city',
        'address',
        'image_url',
        'phone',
        'email',
        'verified',
        'is_active',
        'latitude',
        'longitude',
        'catatan',
        'bank_name',
        'nomor_rekening',
        'ewallet_name',
        'nomor_ewallet',
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
        return $this->hasMany(ReportMitraRouting::class, 'mitra_id');
    }

    public function routedReports()
    {
        return $this->belongsToMany(Report::class, 'report_mitra_routings', 'mitra_id', 'report_id')
            ->withPivot(['status', 'routed_at', 'responded_at', 'expires_at'])
            ->withTimestamps();
    }

    public function priceLists()
    {
        return $this->hasMany(PriceList::class, 'mitra_id');
    }

    public function payments()
    {
        return $this->hasMany(UserMitraPayment::class, 'mitra_id');
    }

    public static function routeByCategory($category)
    {
        return self::routeMultipleByCategory($category, 1)->first();
    }

    public static function mitraTypesForCategory($category): array
    {
        $normalized = strtolower(trim((string) $category));
        $normalized = str_replace(['_', '-'], ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return match ($normalized) {
            'ambulance', 'ambulans', 'medis', 'medis darurat', 'kesehatan', 'kecelakaan', 'medis & kecelakaan' => ['ambulance'],
            'legal', 'lbh', 'pengacara', 'bantuan hukum', 'hukum & keamanan', 'salah tangkap' => ['legal'],
            'pelecehan', 'pelecehan & bullying', 'bullying', 'perundungan', 'kekerasan' => ['pppa'],
            'counselor', 'konselor', 'psikolog', 'psikososial', 'krisis mental', 'konseling & trauma' => ['counselor'],
            'pemadam', 'damkar', 'rescue', 'pemadam rescue', 'pemadam / rescue', 'kebakaran', 'kebakaran & penyelamatan' => ['pemadam'],
            'sosial & lansia/anak terlantar', 'sosial' => ['pppa', 'counselor'],
            'lainnya' => ['ambulance', 'legal', 'counselor', 'pemadam', 'pppa'],
            default => [],
        };
    }

    public static function matchesCategory($mitraType, $category): bool
    {
        return in_array(strtolower((string) $mitraType), self::mitraTypesForCategory($category), true);
    }

    public static function routeMultipleByCategory($category, $limit = 5, ?float $latitude = null, ?float $longitude = null)
    {
        $mitraTypes = self::mitraTypesForCategory($category);

        if (!$mitraTypes) {
            return collect();
        }

        $normalized = strtolower(trim((string) $category));
        $normalized = str_replace(['_', '-'], ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        if ($normalized === 'lainnya') {
            // Ambil semua mitra aktif dari lima tipe utama
            $query = self::query()
                ->whereIn('mitra_type', ['ambulance', 'legal', 'counselor', 'pemadam', 'pppa']);

            if (Schema::hasColumn('mitras', 'is_active')) {
                $query->where('is_active', true);
            }

            $allMitras = $query->get();

            // Hitung jarak untuk setiap mitra
            if ($latitude !== null && $longitude !== null) {
                $allMitras = $allMitras->map(function ($mitra) use ($latitude, $longitude) {
                    $mitra->distance_km = ($mitra->latitude !== null && $mitra->longitude !== null)
                        ? self::haversineKm($latitude, $longitude, (float) $mitra->latitude, (float) $mitra->longitude)
                        : PHP_FLOAT_MAX;
                    return $mitra;
                });
            } else {
                $allMitras = $allMitras->map(function ($mitra) {
                    $mitra->distance_km = PHP_FLOAT_MAX;
                    return $mitra;
                });
            }

            // Kelompokkan semua mitra berdasarkan tipenya
            $grouped = $allMitras->groupBy('mitra_type');

            $selectedMitras = collect();
            $types = ['ambulance', 'legal', 'counselor', 'pemadam', 'pppa'];

            // 1. Untuk setiap tipe, ambil mitra terdekat (prioritas terverifikasi desc, lalu jarak terdekat asc)
            foreach ($types as $type) {
                $mitrasOfType = $grouped->get($type, collect());
                if ($mitrasOfType->isNotEmpty()) {
                    $closest = $mitrasOfType->sortBy([
                        ['verified', 'desc'],
                        ['distance_km', 'asc'],
                    ])->first();
                    $selectedMitras->push($closest);
                }
            }

            // 2. Ambil sisa mitra dari pool jika jumlahnya kurang dari limit
            $selectedIds = $selectedMitras->pluck('id')->toArray();
            $remainingPool = $allMitras->reject(function ($mitra) use ($selectedIds) {
                return in_array($mitra->id, $selectedIds);
            });

            $needed = $limit - $selectedMitras->count();
            if ($needed > 0 && $remainingPool->isNotEmpty()) {
                $backfill = $remainingPool->sortBy([
                    ['verified', 'desc'],
                    ['distance_km', 'asc'],
                ])->take($needed);

                foreach ($backfill as $mitra) {
                    $selectedMitras->push($mitra);
                }
            }

            // Terakhir, urutkan daftar mitra berdasarkan status verifikasi dan jarak terdekat
            return $selectedMitras->sortBy([
                ['verified', 'desc'],
                ['distance_km', 'asc'],
            ])->values();
        }

        $query = self::query()
            ->whereIn('mitra_type', $mitraTypes)
            ->orderByDesc('verified');

        if (Schema::hasColumn('mitras', 'is_active')) {
            $query->where('is_active', true);
        }

        $mitras = $query->get();

        if ($latitude !== null && $longitude !== null) {
            $mitras = $mitras
                ->map(function ($mitra) use ($latitude, $longitude) {
                    $mitra->distance_km = ($mitra->latitude !== null && $mitra->longitude !== null)
                        ? self::haversineKm($latitude, $longitude, (float) $mitra->latitude, (float) $mitra->longitude)
                        : PHP_FLOAT_MAX;

                    return $mitra;
                })
                ->sortBy([
                    ['verified', 'desc'],
                    ['distance_km', 'asc'],
                ])
                ->values();
        }

        return $mitras->take($limit)->values();
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
