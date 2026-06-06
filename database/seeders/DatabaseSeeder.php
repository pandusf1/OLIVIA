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
            ['email' => 'admin@safora.id'],
            [
                'name'     => 'Admin Safora',
                'password' => Hash::make('safora2026'),
                'role'     => 'admin',
            ]
        );

        // Seed demo users biasa + mitra + lokasi + price_lists dilakukan oleh MitraSeeder.
        $this->call([
            MitraSeeder::class,
            AdditionalMitraSeeder::class,
        ]);

    }
}
