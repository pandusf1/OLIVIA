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
            ['email' => 'admin@surara.id'],
            [
                'name'     => 'Admin SuraRa',
                'password' => Hash::make('surara2024'),
                'role'     => 'admin',
            ]
        );

        // Akun mitra demo
        User::firstOrCreate(
            ['email' => 'lbh@surara.id'],
            [
                'name'     => 'LBH Semarang',
                'password' => Hash::make('surara2024'),
                'role'     => 'partner',
            ]
        );

        $this->call(PartnerSeeder::class);
    }
}
