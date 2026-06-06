<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class MitraSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $password = 'safora2026';

        // Reset isi tabel
        if (Schema::hasTable('user_mitra_payments')) {
            DB::table('user_mitra_payments')->delete();
        }
        if (Schema::hasTable('chat_messages')) {
            DB::table('chat_messages')->delete();
        }
        if (Schema::hasTable('chat_threads')) {
            DB::table('chat_threads')->delete();
        }
        DB::table('price_lists')->delete();
        DB::table('mitras')->delete();
        DB::table('users')->delete();
        if (Schema::hasTable('user_locations')) {
            DB::table('user_locations')->delete();
        }

        // Seed akun admin dan user biasa
        $adminId = (string) Str::uuid();
        $userDemoId = (string) Str::uuid();

        User::insert([
            [
                'id' => $adminId,
                'email' => 'admin@safora.id',
                'name' => 'Admin Safora',
                'password' => Hash::make($password),
                'role' => 'admin',
                'mitra_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => $userDemoId,
                'email' => 'user@safora.id',
                'name' => 'User Demo',
                'password' => Hash::make($password),
                'role' => 'user',
                'mitra_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Seed user umum (korban/saksi) + lokasi
        $victims = [
            ['email' => 'korban@safora.id', 'name' => 'Korban Demo', 'latitude' => -6.966900, 'longitude' => 110.413000],
            ['email' => 'saksi1@safora.id', 'name' => 'Saksi Demo 1', 'latitude' => -6.970200, 'longitude' => 110.416000],
            ['email' => 'saksi2@safora.id', 'name' => 'Saksi Demo 2', 'latitude' => -6.962800, 'longitude' => 110.419500],
            ['email' => 'korban2@safora.id', 'name' => 'Korban Demo 2', 'latitude' => -6.975000, 'longitude' => 110.410800],
            ['email' => 'korban3@safora.id', 'name' => 'Korban Demo 3', 'latitude' => -6.958500, 'longitude' => 110.421200],
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
                'mitra_id' => null,
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

        // Expanded list of 35 major cities in Indonesia with their coordinates
        $areaCenters = [
            'Jakarta' => [-6.1751, 106.8650],
            'Surabaya' => [-7.2575, 112.7521],
            'Bandung' => [-6.9175, 107.6191],
            'Medan' => [3.5952, 98.6722],
            'Semarang' => [-6.9667, 110.4166],
            'Makassar' => [-5.1477, 119.4327],
            'Palembang' => [-2.9761, 104.7754],
            'Yogyakarta' => [-7.7956, 110.3695],
            'Denpasar' => [-8.6705, 115.2126],
            'Balikpapan' => [-1.2654, 116.8312],
            'Pontianak' => [-0.0263, 109.3425],
            'Samarinda' => [-0.5016, 117.1537],
            'Banjarmasin' => [-3.3186, 114.5944],
            'Manado' => [1.4748, 124.8428],
            'Ambon' => [-3.6954, 128.1814],
            'Jayapura' => [-2.5916, 140.7178],
            'Banda Aceh' => [5.5483, 95.3238],
            'Pekanbaru' => [0.5071, 101.4478],
            'Padang' => [-0.9471, 100.4172],
            'Bandar Lampung' => [-5.3971, 105.2668],
            'Kupang' => [-10.1772, 123.6070],
            'Mataram' => [-8.5833, 116.1167],
            'Batam' => [1.1301, 104.0529],
            'Surakarta' => [-7.5666, 110.8167],
            'Malang' => [-7.9839, 112.6214],
            'Cirebon' => [-6.7320, 108.5555],
            'Tasikmalaya' => [-7.3274, 108.2207],
            'Bogor' => [-6.5971, 106.8060],
            'Serang' => [-6.1104, 106.1625],
            'Jambi' => [-1.6101, 103.6131],
            'Bengkulu' => [-3.7928, 102.2608],
            'Palu' => [-0.8917, 119.8707],
            'Kendari' => [-3.9722, 122.5149],
            'Gorontalo' => [0.5435, 123.0568],
            'Sorong' => [-0.8762, 131.2558],
        ];

        $cityNames = array_keys($areaCenters);

        $configs = [
            [
                'mitra_type' => 'ambulance',
                'prefixes' => ['PSC 119', 'Ambulans PMI', 'Layanan Medis Darurat', 'Ambulans Gawat Darurat'],
            ],
            [
                'mitra_type' => 'legal',
                'prefixes' => ['LBH', 'Posbakum Pengadilan', 'LPSK Cabang', 'Layanan Advokasi Hukum'],
            ],
            [
                'mitra_type' => 'counselor',
                'prefixes' => ['Layanan SEJIWA', 'Pusat Konseling & Trauma Healing', 'Dinas Sosial (Rehabilitasi ODGJ)', 'Layanan Psikologi Klinik'],
            ],
            [
                'mitra_type' => 'pemadam',
                'prefixes' => ['Dinas Pemadam Kebakaran & Penyelamatan', 'Damkar Rescue', 'Satpol PP & Penyelamatan', 'Posko Evakuasi BPBD'],
            ],
            [
                'mitra_type' => 'pppa',
                'prefixes' => ['UPTD PPA', 'P2TP2A', 'Layanan PPA (Sahabat Perempuan & Anak)', 'Dinas Sosial (Perlindungan Anak)'],
            ]
        ];

        $mitrasToInsert = [];
        $usersToInsert = [];

        foreach ($cityNames as $cityIndex => $cityName) {
            [$cLat, $cLng] = $areaCenters[$cityName];

            foreach ($configs as $typeIndex => $config) {
                $mitraType = $config['mitra_type'];
                $prefixes = $config['prefixes'];

                $prefix = $prefixes[($cityIndex + $typeIndex) % count($prefixes)];
                $mitraId = (string) Str::uuid();
                $mitraName = $prefix . ' ' . $cityName;

                // Unique clean email & phone
                $cleanName = preg_replace('/[^a-z0-9_]/', '', strtolower(str_replace([' ', 'Kab.'], ['_', 'Kab'], $mitraName)));
                $email = $cleanName . '@safora.id';
                $phone = '62' . str_pad((string) (80000000 + ($cityIndex * 100 + $typeIndex * 19) % 99999999), 10, '0', STR_PAD_LEFT);

                // Use circular distribution to prevent overlapping coordinates
                $angle = ($typeIndex * (360 / count($configs))) * M_PI / 180;
                $radius = 0.015 + ($cityIndex % 3) * 0.005; // ~1.5km to ~2.5km radius offset
                $lat = $cLat + sin($angle) * $radius;
                $lng = $cLng + cos($angle) * $radius;

                $streetNo = 10 + (($cityIndex * 7 + $typeIndex) % 90);
                $address = "Jl. Veteran No. {$streetNo}, {$cityName}";

                $img = match(true){
                    $mitraType === 'legal' => '/192.jpg',
                    $mitraType === 'counselor' => '/512.jpg',
                    $mitraType === 'ambulance' => '/192.jpg',
                    default => '/512.jpg',
                };

                $mitrasToInsert[] = [
                    'id' => $mitraId,
                    'mitra_name' => $mitraName,
                    'mitra_type' => $mitraType,
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
                    'name' => $mitraName,
                    'password' => Hash::make($password),
                    'role' => 'mitra',
                    'mitra_id' => $mitraId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Seed 7 PPPA Semarang mitras to preserve specific local requirements
        $pppaMitras = [
            ['name' => 'PPPA Kota Semarang Utama', 'lat' => -6.980000, 'lng' => 110.420000, 'address' => 'Jl. Pemuda No. 148, Sekayu, Kec. Semarang Tengah, Kota Semarang'],
            ['name' => 'PPPA Semarang Barat', 'lat' => -6.995000, 'lng' => 110.380000, 'address' => 'Jl. Bojong Salaman No. 2, Salamanmloyo, Kec. Semarang Barat, Kota Semarang'],
            ['name' => 'PPPA Semarang Timur', 'lat' => -6.985000, 'lng' => 110.440000, 'address' => 'Jl. Majapahit No. 110, Gayamsari, Kec. Semarang Timur, Kota Semarang'],
            ['name' => 'PPPA Semarang Selatan', 'lat' => -7.010000, 'lng' => 110.425000, 'address' => 'Jl. Sriwijaya No. 29, Tegalsari, Kec. Candisari, Kota Semarang'],
            ['name' => 'PPPA Semarang Utara', 'lat' => -6.955000, 'lng' => 110.410000, 'address' => 'Jl. Kakap No. 50, Kuningan, Kec. Semarang Utara, Kota Semarang'],
            ['name' => 'PPPA Tembalang', 'lat' => -7.050000, 'lng' => 110.438000, 'address' => 'Jl. Prof. Soedarto No. 12, Tembalang, Kec. Tembalang, Kota Semarang'],
            ['name' => 'PPPA Banyumanik', 'lat' => -7.065000, 'lng' => 110.425000, 'address' => 'Jl. Perintis Kemerdekaan No. 88, Kec. Banyumanik, Kota Semarang'],
        ];

        foreach ($pppaMitras as $i => $pppa) {
            $mitraId = (string) Str::uuid();
            $email = 'pppa_semarang_' . ($i + 1) . '@safora.id';
            $phone = '628512401935' . ($i + 1);

            $mitrasToInsert[] = [
                'id' => $mitraId,
                'mitra_name' => $pppa['name'],
                'mitra_type' => 'pppa',
                'city' => 'Semarang',
                'address' => $pppa['address'],
                'image_url' => '/512.jpg',
                'phone' => $phone,
                'email' => $email,
                'verified' => true,
                'latitude' => $pppa['lat'],
                'longitude' => $pppa['lng'],
                'created_at' => $now,
            ];

            $usersToInsert[] = [
                'id' => (string) Str::uuid(),
                'email' => $email,
                'name' => $pppa['name'],
                'password' => Hash::make($password),
                'role' => 'mitra',
                'mitra_id' => $mitraId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('mitras')->insert($mitrasToInsert);
        if (!empty($usersToInsert)) {
            DB::table('users')->upsert(
                $usersToInsert,
                ['email'],
                ['name', 'password', 'role', 'mitra_id', 'updated_at']
            );
        }

        // Price list only for legal and counselor mitras
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

        $priceRows = [];
        foreach ($mitrasToInsert as $p) {
            if (!in_array($p['mitra_type'], ['legal', 'counselor'], true)) {
                continue;
            }

            $typeKey = $p['mitra_type'];
            foreach ($priceCatalog[$typeKey] as $item) {
                $priceRows[] = [
                    'mitra_id' => $p['id'],
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
