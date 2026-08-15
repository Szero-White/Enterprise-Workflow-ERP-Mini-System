<?php

namespace Tests\Feature\Security;

use App\Models\Department;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\Role;
use App\Models\User;
use App\Services\DynamicFieldValidationService;
use Database\Seeders\OrganizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PublicDemoSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_added_to_web_responses(): void
    {
        config([
            'security.hsts_enabled' => true,
            'security.hsts_max_age' => 3600,
            'security.hsts_include_subdomains' => false,
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
            ->assertHeader('Strict-Transport-Security', 'max-age=3600');
    }

    public function test_public_demo_allows_admin_configuration_mutations_with_normal_authorization(): void
    {
        config(['demo.enabled' => true]);
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.roles.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('admin.roles.store'), [
                'name' => 'Demo Sandbox Role',
                'key' => 'demo_sandbox_role',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('roles', [
            'name' => 'Demo Sandbox Role',
            'key' => 'demo_sandbox_role',
        ]);
    }

    public function test_public_demo_disables_dynamic_file_uploads(): void
    {
        config(['demo.enabled' => true, 'demo.uploads_enabled' => false]);

        $admin = $this->createUserWithRole('admin');
        $template = FormTemplate::create([
            'name' => 'Upload Demo',
            'code' => 'UPLOAD_DEMO',
            'submission_type' => 'dynamic',
            'is_active' => false,
            'created_by' => $admin->id,
        ]);
        FormField::create([
            'form_template_id' => $template->id,
            'label' => 'Attachment',
            'field_key' => 'attachment',
            'field_type' => 'file',
            'is_required' => false,
            'sort_order' => 1,
        ]);

        $rules = app(DynamicFieldValidationService::class)->rulesFor($template);
        $validator = Validator::make([
            'attachment' => UploadedFile::fake()->create('payload.pdf', 10, 'application/pdf'),
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('attachment'));
    }

    public function test_public_demo_rate_limits_authenticated_write_requests(): void
    {
        config([
            'demo.enabled' => true,
            'demo.max_writes_per_minute' => 1,
            'demo.max_writes_per_hour' => 10,
        ]);

        $user = $this->createUserWithRole('employee');
        $minuteKey = sprintf('public-demo-write:minute:%s|127.0.0.1', $user->id);
        $hourKey = sprintf('public-demo-write:hour:%s', $user->id);
        RateLimiter::clear($minuteKey);
        RateLimiter::clear($hourKey);

        $this->actingAs($user)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('notifications.read-all'))
            ->assertStatus(429);
    }

    public function test_public_demo_enforces_an_hourly_write_ceiling(): void
    {
        config([
            'demo.enabled' => true,
            'demo.max_writes_per_minute' => 10,
            'demo.max_writes_per_hour' => 1,
        ]);

        $user = $this->createUserWithRole('employee');
        $minuteKey = sprintf('public-demo-write:minute:%s|127.0.0.1', $user->id);
        $hourKey = sprintf('public-demo-write:hour:%s', $user->id);
        RateLimiter::clear($minuteKey);
        RateLimiter::clear($hourKey);

        $this->actingAs($user)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('notifications.read-all'))
            ->assertStatus(429);
    }

    public function test_demo_seeding_refuses_default_password_when_demo_mode_is_enabled(): void
    {
        config([
            'demo.enabled' => true,
            'demo.password' => 'password',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->seed(OrganizationSeeder::class);
    }

    private function createUserWithRole(string $roleKey): User
    {
        $department = Department::firstOrCreate(
            ['code' => 'TEST'],
            ['name' => 'Test Department']
        );
        $role = Role::firstOrCreate(
            ['key' => $roleKey],
            ['name' => ucfirst($roleKey), 'is_system' => true]
        );

        return User::factory()->create([
            'department_id' => $department->id,
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }
}
