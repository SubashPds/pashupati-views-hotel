<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'superadmin@pashupativiews.com'],
            [
                'name'       => 'Super Admin',
                'email'      => 'superadmin@pashupativiews.com',
                'role'       => 'superadmin',
                'is_active'  => true,
                'password'   => Hash::make('PashuAdmin@2026'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
