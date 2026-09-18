<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@pashupativiews.com'],
            [
                'name'       => 'Super Admin',
                'email'      => 'superadmin@pashupativiews.com',
                'role'       => 'superadmin',
                'is_active'  => true,
                'password'   => Hash::make('PashuAdmin@2026'),
            ]
        );
    }
}
