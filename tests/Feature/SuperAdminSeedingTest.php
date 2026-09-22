<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SuperAdminSeedingTest extends TestCase
{
    use RefreshDatabase;

    public function test_reseeding_does_not_reset_credentials_or_promote_an_existing_user(): void
    {
        config(['security.bootstrap_admin_email' => 'existing@example.test', 'security.bootstrap_admin_password' => 'New bootstrap passphrase']);
        $user = User::create(['name' => 'Existing', 'email' => 'existing@example.test', 'role' => 'user', 'is_active' => false, 'password' => 'Existing password secret']);
        $before = $user->getAttributes();
        $this->seed(SuperAdminSeeder::class);
        $this->assertSame($before, $user->fresh()->getAttributes());
    }

    public function test_initial_admin_requires_an_explicit_strong_password(): void
    {
        config(['security.bootstrap_admin_email' => 'new@example.test', 'security.bootstrap_admin_password' => null]);
        $this->expectException(ValidationException::class);
        $this->seed(SuperAdminSeeder::class);
    }
}
