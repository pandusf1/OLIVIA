<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $partners = [
            [
                'id'           => Str::uuid(),
                'partner_name' => 'LBH Semarang',
                'partner_type' => 'legal',
                'city'         => 'Semarang',
                'phone'        => '6224356565',
                'email'        => 'lbhsemarang@gmail.com',
                'verified'     => true,
                'created_at'   => now(),
            ],
            [
                'id'           => Str::uuid(),
                'partner_name' => 'RSUD KRMT Wongsonegoro',
                'partner_type' => 'medical',
                'city'         => 'Semarang',
                'phone'        => '6224543797',
                'email'        => null,
                'verified'     => true,
                'created_at'   => now(),
            ],
            [
                'id'           => Str::uuid(),
                'partner_name' => 'P2TP2A Kota Semarang',
                'partner_type' => 'shelter',
                'city'         => 'Semarang',
                'phone'        => '62243541687',
                'email'        => null,
                'verified'     => true,
                'created_at'   => now(),
            ],
            [
                'id'           => Str::uuid(),
                'partner_name' => 'Yayasan Pulih (Konselor)',
                'partner_type' => 'counselor',
                'city'         => 'Semarang',
                'phone'        => '6221788909',
                'email'        => null,
                'verified'     => true,
                'created_at'   => now(),
            ],
        ];

        DB::table('partners')->insertOrIgnore($partners);
    }
}
