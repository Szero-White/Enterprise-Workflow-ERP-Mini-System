<?php

namespace Tests\Feature\Security;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_home_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_login_page_renders_successfully(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        $role = Role::create(['name' => 'Employee', 'key' => 'employee', 'is_system' => true]);
        User::factory()->create(['email' => 'limited@example.com', 'role_id' => $role->id, 'is_active' => true]);
        $key = 'limited@example.com|127.0.0.1';
        RateLimiter::clear($key);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.post'), [
                'email' => 'limited@example.com',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $this->assertTrue(RateLimiter::tooManyAttempts($key, 5));

        $this->post(route('login.post'), [
            'email' => 'limited@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
    }

    public function test_last_active_admin_cannot_remove_own_admin_access(): void
    {
        $adminRole = Role::create(['name' => 'Admin', 'key' => 'admin', 'is_system' => true]);
        $employeeRole = Role::create(['name' => 'Employee', 'key' => 'employee', 'is_system' => true]);
        $admin = User::factory()->create([
            'name' => 'Only Admin',
            'email' => 'admin@example.com',
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role_id' => $employeeRole->id,
                'is_active' => 1,
            ])
            ->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->hasRole('admin'));
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_user_creation_rejects_weak_password(): void
    {
        $adminRole = Role::create(['name' => 'Admin', 'key' => 'admin', 'is_system' => true]);
        $employeeRole = Role::create(['name' => 'Employee', 'key' => 'employee', 'is_system' => true]);
        $admin = User::factory()->create(['role_id' => $adminRole->id, 'is_active' => true]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Weak Password User',
                'email' => 'weak@example.com',
                'password' => 'password',
                'role_id' => $employeeRole->id,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'weak@example.com']);
    }
}
