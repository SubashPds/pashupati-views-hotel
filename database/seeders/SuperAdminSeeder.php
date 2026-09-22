<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = strtolower(trim((string) config('security.bootstrap_admin_email')));
        // Re-running database seeds must never reset credentials or grant an existing user a new role.
        if (User::where('email', $email)->exists()) return;

        $data = \Illuminate\Support\Facades\Validator::make([
            'email' => $email,
            'password' => config('security.bootstrap_admin_password'),
        ], [
            'email' => ['required', 'email', 'max:254'],
            'password' => ['required', 'string', new \App\Rules\StrongPassword],
        ])->validate();

        User::create([
            'name' => 'Super Admin',
            'email' => $data['email'],
            'role' => 'superadmin',
            'is_active' => true,
            'password' => Hash::make($data['password']),
        ]);
    }
}
