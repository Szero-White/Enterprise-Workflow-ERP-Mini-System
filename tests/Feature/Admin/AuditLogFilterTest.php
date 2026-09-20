<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $manager;

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
        ]);

        $managerRole = Role::create([
            'name' => 'Manager',
            'key' => 'manager',
        ]);

        $this->admin = User::factory()->create([
            'name' => 'System Admin',
            'department_id' => $department->id,
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Operations Manager',
            'department_id' => $department->id,
            'role_id' => $managerRole->id,
            'is_active' => true,
        ]);
    }

    public function test_audit_log_page_renders_guided_filters_and_collapsed_change_details(): void
    {
        AuditLog::create([
            'actor_id' => $this->manager->id,
            'action' => 'request.approved',
            'description' => 'Approved request for audit UX test',
            'auditable_type' => User::class,
            'auditable_id' => $this->manager->id,
            'old_values' => ['status' => 'pending'],
            'new_values' => ['status' => 'approved'],
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.audit-logs.index'))
            ->assertOk()
            ->assertSee('Tất cả hành động')
            ->assertSee('Tất cả người thực hiện')
            ->assertSee('Operations Manager')
            ->assertSee('Xem thay đổi')
            ->assertSee('Approved request for audit UX test');
    }

    public function test_audit_log_filters_can_be_combined_without_exposing_unmatched_rows(): void
    {
        $matched = AuditLog::create([
            'actor_id' => $this->manager->id,
            'action' => 'request.approved',
            'description' => 'MATCHED AUDIT ROW',
        ]);
        $matched->forceFill([
            'created_at' => '2026-09-20 10:00:00',
            'updated_at' => '2026-09-20 10:00:00',
        ])->save();

        $unmatchedAction = AuditLog::create([
            'actor_id' => $this->manager->id,
            'action' => 'request.rejected',
            'description' => 'UNMATCHED ACTION ROW',
        ]);
        $unmatchedAction->forceFill([
            'created_at' => '2026-09-20 10:05:00',
            'updated_at' => '2026-09-20 10:05:00',
        ])->save();

        $unmatchedActor = AuditLog::create([
            'actor_id' => $this->admin->id,
            'action' => 'request.approved',
            'description' => 'UNMATCHED ACTOR ROW',
        ]);
        $unmatchedActor->forceFill([
            'created_at' => '2026-09-20 10:10:00',
            'updated_at' => '2026-09-20 10:10:00',
        ])->save();

        $this->actingAs($this->admin)
            ->get(route('admin.audit-logs.index', [
                'action' => 'request.approved',
                'actor_id' => $this->manager->id,
                'from_date' => '2026-09-20',
                'to_date' => '2026-09-20',
            ]))
            ->assertOk()
            ->assertSee('MATCHED AUDIT ROW')
            ->assertDontSee('UNMATCHED ACTION ROW')
            ->assertDontSee('UNMATCHED ACTOR ROW');
    }

    public function test_audit_log_rejects_an_invalid_date_range(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.audit-logs.index', [
                'from_date' => '2026-09-21',
                'to_date' => '2026-09-20',
            ]))
            ->assertSessionHasErrors('to_date');
    }
}
