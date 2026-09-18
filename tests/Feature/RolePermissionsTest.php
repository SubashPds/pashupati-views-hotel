<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    private function account(string $role, array $overrides = []): User
    {
        return User::create(array_merge(['name' => ucfirst($role), 'email' => $role.'@example.test', 'password' => 'Test-password-2026', 'role' => $role, 'is_active' => true], $overrides));
    }

    private function signIn(User $user): static
    {
        $this->flushSession();
        return $this->actingAs($user);
    }

    public function test_superadmin_can_create_and_edit_accounts_without_exposing_passwords(): void
    {
        $this->signIn($this->account('superadmin'));
        $this->get(route('admin.users.create'))->assertOk();
        $data = ['name' => 'Reception', 'email' => 'Reception@example.test', 'role' => 'admin', 'is_active' => '1', 'password' => 'Strong-pass-2026', 'password_confirmation' => 'Strong-pass-2026'];
        $this->post(route('admin.users.store'), $data)->assertSessionHasNoErrors()->assertRedirect(route('admin.users.index'));
        $user = User::where('email', 'reception@example.test')->firstOrFail();
        $this->assertTrue(Hash::check($data['password'], $user->password));
        $this->get(route('admin.users.edit', $user))->assertOk()->assertDontSee($user->password)->assertDontSee($data['password']);
        $this->get(route('admin.users.index'))->assertOk()->assertSee('Reception');
        $hash = $user->password;
        $this->put(route('admin.users.update', $user), array_merge($data, ['role' => 'user', 'password' => '', 'password_confirmation' => '']))->assertSessionHasNoErrors();
        $this->assertSame($hash, $user->fresh()->password);
        $this->assertSame('user', $user->fresh()->role);
        $this->put(route('admin.users.update', $user), array_merge($data, ['password' => 'New-pass-2026', 'password_confirmation' => 'New-pass-2026']))->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('New-pass-2026', $user->fresh()->password));
    }

    public function test_all_roles_can_log_in_but_inactive_accounts_cannot(): void
    {
        foreach (array_keys(Permissions::ROLES) as $role) {
            $user = $this->account($role);
            $this->post(route('login.post'), ['email' => $user->email, 'password' => 'Test-password-2026'])->assertRedirect(route('admin.dashboard'));
            $this->assertAuthenticatedAs($user);
            $this->get(route('admin.dashboard'))->assertOk();
            $this->post(route('logout'));
        }
        $user = $this->account('user', ['email' => 'inactive@example.test', 'is_active' => false]);
        $this->post(route('login.post'), ['email' => $user->email, 'password' => 'Test-password-2026'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->signIn($user)->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_non_superadmins_cannot_manage_accounts_or_permissions(): void
    {
        $target = $this->account('superadmin');
        $role = Role::where('name', 'admin')->firstOrFail();
        foreach (['admin', 'user'] as $name) {
            $this->signIn($this->account($name));
            $this->get(route('admin.users.index'))->assertForbidden();
            $this->get(route('admin.users.create'))->assertForbidden();
            $this->post(route('admin.users.store'), [])->assertForbidden();
            $this->get(route('admin.users.edit', $target))->assertForbidden();
            $this->put(route('admin.users.update', $target), ['role' => 'user'])->assertForbidden();
            $this->get(route('admin.roles.index'))->assertForbidden();
            $this->put(route('admin.roles.update', $role), ['permissions' => ['users']])->assertForbidden();
            $this->get(route('admin.dashboard'))->assertDontSee('Roles &amp; Permissions', false)->assertDontSee(route('admin.users.index'));
        }
    }

    public function test_permissions_control_read_and_write_and_apply_on_next_request(): void
    {
        $superadmin = $this->account('superadmin');
        $user = $this->account('user');
        $role = Role::where('name', 'user')->firstOrFail();
        $this->signIn($superadmin)->get(route('admin.roles.index'))->assertOk();
        $this->put(route('admin.roles.update', $role), ['permissions' => ['rooms']])->assertSessionHasNoErrors();
        $this->signIn($user)->get(route('admin.rooms.index'))->assertOk();
        $this->get(route('admin.rooms.create'))->assertOk();
        $this->post(route('admin.rooms.store'), ['name' => 'Permitted Room', 'category' => 'deluxe', 'price_per_night' => 1200, 'max_guests' => 2])->assertSessionHasNoErrors();
        $this->get(route('admin.packages.index'))->assertForbidden();
        $this->post(route('admin.packages.store'), ['name' => 'Forbidden Package'])->assertForbidden();
        $this->signIn($superadmin)->put(route('admin.roles.update', $role), [])->assertSessionHasNoErrors();
        $this->signIn($user)->get(route('admin.rooms.index'))->assertForbidden();
        $this->post(route('admin.rooms.store'), [])->assertForbidden();
        $this->delete('/admin/room-images/999')->assertForbidden();
    }

    public function test_dashboard_does_not_leak_guest_enquiries_to_unpermitted_users(): void
    {
        Enquiry::create(['guest_name' => 'Private Guest', 'email' => 'private@example.test', 'message' => 'Private request']);
        $this->signIn($this->account('user'))->get(route('admin.dashboard'))->assertOk()
            ->assertDontSee('Private Guest')->assertDontSee('Recent Enquiries')->assertDontSee('Total Rooms')->assertDontSee(route('admin.settings.index'));
    }

    public function test_self_lockout_invalid_roles_and_unknown_permissions_are_rejected(): void
    {
        $superadmin = $this->account('superadmin');
        $this->signIn($superadmin);
        $data = ['name' => $superadmin->name, 'email' => $superadmin->email, 'role' => 'superadmin', 'is_active' => '1'];
        foreach ([['role' => 'admin'], ['is_active' => '0']] as $change) {
            $this->put(route('admin.users.update', $superadmin), array_merge($data, $change))->assertSessionHasErrors('role');
        }
        $this->assertSame('superadmin', $superadmin->fresh()->role);
        $this->assertTrue($superadmin->fresh()->is_active);
        $role = Role::where('name', 'user')->firstOrFail();
        $this->put(route('admin.roles.update', $role), ['permissions' => ['users', 'roles']])->assertSessionHasErrors('permissions.0');
        $this->assertSame([], $role->fresh()->permissions);
        $this->put(route('admin.roles.update', Role::where('name', 'superadmin')->first()), [])->assertForbidden();
        $this->post(route('admin.users.store'), array_merge($data, ['role' => 'owner', 'password' => 'short', 'password_confirmation' => 'mismatch']))
            ->assertSessionHasErrors(['email', 'role', 'password']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_every_admin_route_has_an_explicit_access_mapping(): void
    {
        foreach (Route::getRoutes() as $route) {
            if (!str_starts_with($route->getName() ?? '', 'admin.')) continue;
            $module = explode('.', $route->getName())[1];
            $this->assertContains($module, array_merge(['dashboard', 'users', 'roles'], array_keys(Permissions::MODULES)), $route->getName());
            $this->assertContains('admin.access', $route->gatherMiddleware(), $route->getName());
        }
    }

    public function test_password_reset_ends_existing_dashboard_session(): void
    {
        $user = $this->account('admin');
        $this->post(route('login.post'), ['email' => $user->email, 'password' => 'Test-password-2026'])->assertRedirect(route('admin.dashboard'));
        $this->get(route('admin.dashboard'))->assertOk();
        $user->update(['password' => 'Replacement-password-2026']);
        // A subsequent HTTP request loads the updated account while retaining the old session hash.
        $this->actingAs($user->fresh())->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
