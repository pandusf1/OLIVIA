<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Partner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdditionalPartnerSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $password = 'safora2026';

        $cities = [
            'Semarang' => [-6.9667, 110.4166],
            'Surabaya' => [-7.2575, 112.7521],
        ];

        // Definisi 6 layanan penanganan
        // 1. Ambulance (PSC 119 / PMI) -> ambulance
        // 2. Pemadam & Rescue (Damkar / SAR) -> pemadam
        // 3. Layanan PPPA (UPTD PPA / SAPA 129) -> pppa
        // 4. Bantuan Hukum (LBH / LPSK) -> legal
        // 5. Konselor / Psikolog (SEJIWA / Mandiri) -> counselor
        // 6. Dinas Sosial (Dinsos / Satpol PP) -> Dinsos (pppa) & Satpol PP (pemadam)
        
        $partnersConfig = [
            // Ambulance
            [
                'name' => 'PSC 119',
                'type' => 'ambulance',
                'distance' => 3.5,
                'angle' => 0,
            ],
            [
                'name' => 'Ambulans PMI',
                'type' => 'ambulance',
                'distance' => 8.5,
                'angle' => 60,
            ],
            [
                'name' => 'Ambulans PMI Wilayah Kedua',
                'type' => 'ambulance',
                'distance' => 13.0,
                'angle' => 120,
            ],

            // Pemadam & Rescue
            [
                'name' => 'Damkar Rescue',
                'type' => 'pemadam',
                'distance' => 2.5,
                'angle' => 45,
            ],
            [
                'name' => 'SAR Rescue',
                'type' => 'pemadam',
                'distance' => 9.0,
                'angle' => 135,
            ],
            [
                'name' => 'Posko Evakuasi BPBD',
                'type' => 'pemadam',
                'distance' => 14.5,
                'angle' => 225,
            ],

            // Layanan PPPA
            [
                'name' => 'UPTD PPA',
                'type' => 'pppa',
                'distance' => 4.0,
                'angle' => 90,
            ],
            [
                'name' => 'Layanan PPA SAPA 129',
                'type' => 'pppa',
                'distance' => 9.5,
                'angle' => 180,
            ],

            // Bantuan Hukum
            [
                'name' => 'LBH Penyelamat',
                'type' => 'legal',
                'distance' => 5.5,
                'angle' => 270,
            ],
            [
                'name' => 'LPSK Cabang Utama',
                'type' => 'legal',
                'distance' => 11.5,
                'angle' => 315,
            ],

            // Konselor / Psikolog
            [
                'name' => 'Trauma Healing SEJIWA',
                'type' => 'counselor',
                'distance' => 6.0,
                'angle' => 30,
            ],
            [
                'name' => 'Psikolog Mandiri',
                'type' => 'counselor',
                'distance' => 10.5,
                'angle' => 150,
            ],

            // Dinas Sosial (Dinsos / Satpol PP)
            [
                'name' => 'Dinas Sosial (Dinsos)',
                'type' => 'pppa',
                'distance' => 12.0,
                'angle' => 210,
            ],
            [
                'name' => 'Satpol PP Rescue',
                'type' => 'pemadam',
                'distance' => 16.0,
                'angle' => 330,
            ],
        ];

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
                ['service_name' => 'Sesi Konseling (45 session)', 'price' => 250000, 'duration' => '45 menit'],
                ['service_name' => 'Sesi Konseling (60 session)', 'price' => 300000, 'duration' => '60 menit'],
                ['service_name' => 'Rencana Pendampingan (paket 3 sesi)', 'price' => 650000, 'duration' => '3 x 45 menit'],
                ['service_name' => 'Pendampingan Berkelanjutan (paket 6 sesi)', 'price' => 1200000, 'duration' => '6 x 45 menit'],
            ],
        ];

        foreach ($cities as $cityName => [$centerLat, $centerLng]) {
            foreach ($partnersConfig as $pConfig) {
                $partnerName = $pConfig['name'] . ' ' . $cityName;

                // Cek jika sudah ada agar tidak duplikat
                $exists = Partner::where('partner_name', $partnerName)->exists();
                if ($exists) {
                    continue;
                }

                // Kalkulasi offset koordinat agar tersebar merata
                $latOffset = ($pConfig['distance'] / 111.12) * sin(deg2rad($pConfig['angle']));
                $lngOffset = ($pConfig['distance'] / (111.12 * cos(deg2rad($centerLat)))) * cos(deg2rad($pConfig['angle']));
                $lat = round($centerLat + $latOffset, 6);
                $lng = round($centerLng + $lngOffset, 6);

                $partnerId = (string) Str::uuid();
                $cleanName = preg_replace('/[^a-z0-9_]/', '', strtolower(str_replace([' ', 'Kab.'], ['_', 'Kab'], $partnerName)));
                $email = $cleanName . '@safora.id';
                $phone = '62' . rand(810000000, 899999999);

                $img = match(true){
                    $pConfig['type'] === 'legal' => '/192.jpg',
                    $pConfig['type'] === 'counselor' => '/512.jpg',
                    $pConfig['type'] === 'ambulance' => '/192.jpg',
                    default => '/512.jpg',
                };

                // Insert Partner
                Partner::create([
                    'id' => $partnerId,
                    'partner_name' => $partnerName,
                    'partner_type' => $pConfig['type'],
                    'city' => $cityName,
                    'address' => "Jl. Raya " . $pConfig['name'] . " No. " . rand(1, 150) . ", " . $cityName,
                    'image_url' => $img,
                    'phone' => $phone,
                    'email' => $email,
                    'verified' => true,
                    'is_active' => true,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'created_at' => $now,
                ]);

                // Insert User Account
                User::create([
                    'id' => (string) Str::uuid(),
                    'email' => $email,
                    'name' => $partnerName,
                    'password' => Hash::make($password),
                    'role' => 'partner',
                    'partner_id' => $partnerId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Seed Price List if counselor or legal
                if (in_array($pConfig['type'], ['legal', 'counselor'], true)) {
                    $priceRows = [];
                    foreach ($priceCatalog[$pConfig['type']] as $item) {
                        $priceRows[] = [
                            'partner_id' => $partnerId,
                            'service_name' => $item['service_name'],
                            'price' => $item['price'],
                            'currency' => 'IDR',
                            'duration' => $item['duration'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                    DB::table('price_lists')->insert($priceRows);
                }
            }
        }
    }
}
