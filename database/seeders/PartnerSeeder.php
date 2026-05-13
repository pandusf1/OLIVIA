<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Koordinat dummy (Semarang)
        $coords = [
            'LBH Semarang' => [-6.966667, 110.416664],
            'Kantor Pengacara Nusa' => [-6.972500, 110.406900],
            'Konselor Kota Semarang' => [-6.959200, 110.420800],
            'Psikolog Spesialis Trauma' => [-6.975300, 110.424200],
        ];

        // Pastikan partner_type sesuai kebutuhan fitur:
        // - legal (pengacara)
        // - counselor (psikolog)
        $partners = [
            [
                'id' => Str::uuid(),
                'partner_name' => 'LBH Semarang',
                'partner_type' => 'legal',
                'city' => 'Semarang',
                'phone' => '6224356565',
                'email' => 'lbhsemarang@gmail.com',
                'verified' => true,
                'latitude' => $coords['LBH Semarang'][0],
                'longitude' => $coords['LBH Semarang'][1],
                'created_at' => $now,
            ],
            [
                'id' => Str::uuid(),
                'partner_name' => 'Kantor Pengacara Nusa',
                'partner_type' => 'legal',
                'city' => 'Semarang',
                'phone' => '6224501234',
                'email' => 'kontak@nusa-advokat.id',
                'verified' => true,
                'latitude' => $coords['Kantor Pengacara Nusa'][0],
                'longitude' => $coords['Kantor Pengacara Nusa'][1],
                'created_at' => $now,
            ],
            [
                'id' => Str::uuid(),
                'partner_name' => 'Konselor Kota Semarang',
                'partner_type' => 'counselor',
                'city' => 'Semarang',
                'phone' => '6224012345',
                'email' => null,
                'verified' => true,
                'latitude' => $coords['Konselor Kota Semarang'][0],
                'longitude' => $coords['Konselor Kota Semarang'][1],
                'created_at' => $now,
            ],
            [
                'id' => Str::uuid(),
                'partner_name' => 'Psikolog Spesialis Trauma',
                'partner_type' => 'counselor',
                'city' => 'Semarang',
                'phone' => '6224098765',
                'email' => null,
                'verified' => true,
                'latitude' => $coords['Psikolog Spesialis Trauma'][0],
                'longitude' => $coords['Psikolog Spesialis Trauma'][1],
                'created_at' => $now,
            ],
        ];

        DB::table('partners')->insert($partners);

        // Seed Price Lists dummy
        $legalPartners = DB::table('partners')->whereIn('partner_type', ['legal'])->get();
        $counselorPartners = DB::table('partners')->whereIn('partner_type', ['counselor'])->get();

        foreach ($legalPartners as $p) {
            DB::table('price_lists')->insert([
                [
                    'partner_id' => $p->id,
                    'service_name' => 'Konsultasi Pengacara (20 menit)',
                    'price' => 150000,
                    'currency' => 'IDR',
                    'duration' => '20 menit',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'partner_id' => $p->id,
                    'service_name' => 'Pendampingan Lanjutan (1 jam)',
                    'price' => 400000,
                    'currency' => 'IDR',
                    'duration' => '1 jam',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        foreach ($counselorPartners as $p) {
            DB::table('price_lists')->insert([
                [
                    'partner_id' => $p->id,
                    'service_name' => 'Konsultasi Psikolog (30 menit)',
                    'price' => 120000,
                    'currency' => 'IDR',
                    'duration' => '30 menit',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'partner_id' => $p->id,
                    'service_name' => 'Sesi Pendampingan (45 menit)',
                    'price' => 250000,
                    'currency' => 'IDR',
                    'duration' => '45 menit',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        // Seed User korban/saksi + lokasi
        // (mengisi user biasa agar map/card & chat bisa dipakai)
        $users = [
            [
                'email' => 'korban@surara.id',
                'name' => 'Korban Demo',
                'latitude' => -6.966900,
                'longitude' => 110.413000,
            ],
            [
                'email' => 'saksi1@surara.id',
                'name' => 'Saksi Demo 1',
                'latitude' => -6.970200,
                'longitude' => 110.416000,
            ],
            [
                'email' => 'saksi2@surara.id',
                'name' => 'Saksi Demo 2',
                'latitude' => -6.962800,
                'longitude' => 110.419500,
            ],
            [
                'email' => 'korban2@surara.id',
                'name' => 'Korban Demo 2',
                'latitude' => -6.975000,
                'longitude' => 110.410800,
            ],
            [
                'email' => 'korban3@surara.id',
                'name' => 'Korban Demo 3',
                'latitude' => -6.958500,
                'longitude' => 110.421200,
            ],
        ];

        foreach ($users as $u) {
            $user = \App\Models\User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => \Illuminate\Support\Facades\Hash::make('surara2024'),
                    'role' => 'user',
                ]
            );

            if (!\App\Models\UserLocation::where('user_id', $user->id)->exists()) {
                \App\Models\UserLocation::create([
                    'user_id' => $user->id,
                    'latitude' => $u['latitude'],
                    'longitude' => $u['longitude'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}

