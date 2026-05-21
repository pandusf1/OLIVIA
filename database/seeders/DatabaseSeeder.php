<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::firstOrCreate(
            ['email' => 'admin@Safora.id'],
            [
                'name'     => 'Admin Safora',
                'password' => Hash::make('Safora2024'),
                'role'     => 'admin',
            ]
        );

        // Seed demo users biasa + partner + lokasi + price_lists dilakukan oleh PartnerSeeder.
        $this->call(PartnerSeeder::class);

    }
}
