<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewestFirstListingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $department = Department::create([
            'name' => 'Operations',
            'code' => 'OPS',
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
            'key' => 'admin',
            'is_system' => true,
        ]);

        $this->admin = User::factory()->create([
            'department_id' => $department->id,
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);
    }

    public function test_role_management_lists_newly_created_records_first(): void
    {
        Role::create([
            'name' => 'Older Custom Role',
            'key' => 'older_custom',
        ]);

        Role::create([
            'name' => 'Newest Custom Role',
            'key' => 'newest_custom',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSeeInOrder(['Newest Custom Role', 'Older Custom Role']);
    }

    public function test_department_management_lists_newly_created_records_first(): void
    {
        Department::create([
            'name' => 'Older Department',
            'code' => 'OLD',
        ]);

        Department::create([
            'name' => 'Newest Department',
            'code' => 'NEW',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.departments.index'))
            ->assertOk()
            ->assertSeeInOrder(['Newest Department', 'Older Department']);
    }
}
