<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;



class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $password = 'savora2024';

        // Reset isi tabel (requested: reset users, partners, price_lists)
        DB::table('price_lists')->delete();
        DB::table('partners')->delete();
        DB::table('users')->delete();
        // Jika relasi user_locations punya FK ke users, idealnya juga reset agar tidak error.
        if (Schema::hasTable('user_locations')) {
            DB::table('user_locations')->delete();
        }

        // Seed akun admin dan user biasa
        $adminId = (string) Str::uuid();
        $userDemoId = (string) Str::uuid();

        User::insert([
            [
                'id' => $adminId,
                'email' => 'admin@savora.id',
                'name' => 'Admin Savora',
                'password' => Hash::make($password),
                'role' => 'admin',
                'partner_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => $userDemoId,
                'email' => 'user@savora.id',
                'name' => 'User Demo',
                'password' => Hash::make($password),
                'role' => 'user',
                'partner_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Seed user umum (korban/saksi) + lokasi
        $victims = [
            ['email' => 'korban@savora.id', 'name' => 'Korban Demo', 'latitude' => -6.966900, 'longitude' => 110.413000],
            ['email' => 'saksi1@savora.id', 'name' => 'Saksi Demo 1', 'latitude' => -6.970200, 'longitude' => 110.416000],
            ['email' => 'saksi2@savora.id', 'name' => 'Saksi Demo 2', 'latitude' => -6.962800, 'longitude' => 110.419500],
            ['email' => 'korban2@savora.id', 'name' => 'Korban Demo 2', 'latitude' => -6.975000, 'longitude' => 110.410800],
            ['email' => 'korban3@savora.id', 'name' => 'Korban Demo 3', 'latitude' => -6.958500, 'longitude' => 110.421200],
        ];

        $victimRows = [];
        $victimLocationRows = [];
        foreach ($victims as $v) {
            $id = (string) Str::uuid();
            $victimRows[] = [
                'id' => $id,
                'email' => $v['email'],
                'name' => $v['name'],
                'password' => Hash::make($password),
                'role' => 'user',
                'partner_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $victimLocationRows[] = [
                'user_id' => $id,
                'latitude' => $v['latitude'],
                'longitude' => $v['longitude'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        User::insert($victimRows);
        if (!empty($victimLocationRows)) {
            UserLocation::insert($victimLocationRows);
        }

        // Partner seed
        $areaCenters = [
            'Semarang' => [-6.966667, 110.416664],
            'Kab. Semarang' => [-7.125000, 110.389000],
            'Demak' => [-6.892900, 110.634700],
            'Salatiga' => [-7.327600, 110.500100],
            'Kendal' => [-6.902100, 110.209500],
            'Grobogan' => [-7.151500, 110.915700],
            'Kudus' => [-6.805600, 110.843500],
            'Pati' => [-6.750000, 111.000000],
            'Jepara' => [-6.594500, 110.685500],
        ];

        $cityNames = array_keys($areaCenters);
        $perCityCountPerType = 3;

        $legalConfig = [
            'partner_type' => 'legal',
            'prefixes' => ['LBH', 'YLBH', 'Klinik Hukum', 'Bantuan Hukum'],
        ];

        $counselorConfig = [
            'partner_type' => 'counselor',
            'prefixes' => ['Psikolog', 'Konselor', 'Layanan Psikososial', 'Tim Konseling'],
        ];

        $ambulanceConfig = [
            'partner_type' => 'ambulance',
            'prefixes' => ['Ambulans', 'Unit Medis', 'Layanan Darurat', 'Respon Cepat'],
        ];

        $pemadamConfig = [
            'partner_type' => 'pemadam',
            'prefixes' => ['Pemadam Kebakaran', 'Damkar Kota', 'Unit Pemadam', 'Satuan Pemadam'],
        ];

        $types = [$legalConfig, $counselorConfig, $ambulanceConfig, $pemadamConfig];

        $partnersToInsert = [];
        $usersToInsert = [];

        foreach ($cityNames as $cityIndex => $cityName) {
            [$cLat, $cLng] = $areaCenters[$cityName];

            foreach ($types as $typeConfig) {
                $partnerType = $typeConfig['partner_type'];
                $prefixes = $typeConfig['prefixes'];

                for ($i = 0; $i < $perCityCountPerType; $i++) {
                    $prefix = $prefixes[($cityIndex + $i) % count($prefixes)];

                    $partnerId = (string) Str::uuid();
                    $partnerName = $prefix . ' ' . $cityName . ' ' . ($i + 1);

                    $email = strtolower(str_replace([' ', 'Kab.'], ['_', 'Kab'], $partnerName)) . '@savora.id';
                    $phone = '62' . str_pad((string) (80000000 + ($cityIndex * 100 + $i * 19) % 99999999), 10, '0', STR_PAD_LEFT);

                    $lat = $cLat + ((($cityIndex + 1) % 11) - 5) * 0.03 + ($i % 3) * 0.008;
                    $lng = $cLng + ((($cityIndex + 7) % 13) - 6) * 0.03 + ($i % 4) * 0.006;

                    // dummy 1x1 building + address for tooltip
                    $streetNo = 10 + (($cityIndex * 7 + $i) % 90);
                    $district = match(true){
                        $cityName === 'Semarang' => 'Tembalang',
                        $cityName === 'Kab. Semarang' => 'Banyubiru',
                        $cityName === 'Demak' => 'Sayung',
                        $cityName === 'Salatiga' => 'Sidomukti',
                        $cityName === 'Kendal' => 'Kaliwungu',
                        $cityName === 'Grobogan' => 'Grobogan',
                        $cityName === 'Kudus' => 'Kota',
                        $cityName === 'Pati' => 'Pati',
                        $cityName === 'Jepara' => 'Jepara',
                        default => 'Kecamatan',
                    };

                    // Reuse existing public placeholder images (1x1-ish). You can replace with real ones later.
                    $img = match(true){
                        $partnerType === 'legal' => '/192.jpg',
                        $partnerType === 'counselor' => '/512.jpg',
                        $partnerType === 'ambulance' => '/192.jpg',
                        default => '/512.jpg',
                    };

                    $address = "$district, Jalan Veteran No. {$streetNo}, {$cityName}";

                    $partnersToInsert[] = [
                        'id' => $partnerId,
                        'partner_name' => $partnerName,
                        'partner_type' => $partnerType,
                        'city' => $cityName,
                        'address' => $address,
                        'image_url' => $img,
                        'phone' => $phone,
                        'email' => $email,
                        'verified' => true,
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'created_at' => $now,
                    ];


                    $usersToInsert[] = [
                        'id' => (string) Str::uuid(),
                        'email' => $email,
                        'name' => $partnerName,
                        'password' => Hash::make($password),
                        'role' => 'partner',
                        'partner_id' => $partnerId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        DB::table('partners')->insert($partnersToInsert);
        if (!empty($usersToInsert)) {
            DB::table('users')->upsert(
                $usersToInsert,
                ['email'],
                ['name', 'password', 'role', 'partner_id', 'updated_at']
            );
        }

        // Price list hanya untuk partner pengacara (legal) dan psikolog (counselor)
        $priceCatalog = [
            'legal' => [
                ['service_name' => 'Konsultasi Pengacara (20 menit)', 'price' => 150000, 'duration' => '20 menit'],
                ['service_name' => 'Penjelasan Kasus & Strategi Awal (45 menit)', 'price' => 300000, 'duration' => '45 menit'],
                ['service_name' => 'Penyusunan Surat Kuasa / Dokumen', 'price' => 300000, 'duration' => '1-2 hari'],
                ['service_name' => 'Pembuatan Surat Keberatan / Bantahan', 'price' => 400000, 'duration' => '1-3 hari'],
                ['service_name' => 'Pendampingan Pemeriksaan (1 sesi)', 'price' => 500000, 'duration' => '±3 jam'],
                ['service_name' => 'Pendampingan Persidangan / Sidang (1 sesi)', 'price' => 750000, 'duration' => '±1 hari'],
            ],
            'counselor' => [
                ['service_name' => 'Konsultasi Psikolog (30 menit)', 'price' => 120000, 'duration' => '30 menit'],
                ['service_name' => 'Asesmen Awal Psikologis (45 menit)', 'price' => 200000, 'duration' => '45 menit'],
                ['service_name' => 'Sesi Konseling (45 menit)', 'price' => 250000, 'duration' => '45 menit'],
                ['service_name' => 'Sesi Konseling (60 menit)', 'price' => 300000, 'duration' => '60 menit'],
                ['service_name' => 'Rencana Pendampingan (paket 3 sesi)', 'price' => 650000, 'duration' => '3 x 45 menit'],
                ['service_name' => 'Pendampingan Berkelanjutan (paket 6 sesi)', 'price' => 1200000, 'duration' => '6 x 45 menit'],
            ],
        ];

        $priceRows = [];
        foreach ($partnersToInsert as $p) {
            if (!in_array($p['partner_type'], ['legal', 'counselor'], true)) {
                continue;
            }

            $typeKey = $p['partner_type'];
            foreach ($priceCatalog[$typeKey] as $item) {
                $priceRows[] = [
                    'partner_id' => $p['id'],
                    'service_name' => $item['service_name'],
                    'price' => $item['price'],
                    'currency' => 'IDR',
                    'duration' => $item['duration'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($priceRows)) {
            DB::table('price_lists')->insert($priceRows);
        }
    }
}

