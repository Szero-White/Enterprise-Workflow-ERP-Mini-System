<?php

namespace Tests\Feature\Security;

use App\Models\Attachment;
use App\Models\Department;
use App\Models\FormTemplate;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OperationsSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_role_key_cannot_be_changed_or_deleted(): void
    {
        $department = Department::create(['name' => 'Admin', 'code' => 'ADMIN']);
        $adminRole = Role::create(['name' => 'Admin', 'key' => 'admin', 'is_system' => true]);
        $admin = User::factory()->create(['department_id' => $department->id, 'role_id' => $adminRole->id, 'is_active' => true]);

        $this->actingAs($admin)
            ->put(route('admin.roles.update', $adminRole), [
                'name' => 'System Administrator',
                'key' => 'super_admin',
                'description' => 'Protected system role',
            ])
            ->assertSessionHasErrors('key');

        $this->assertDatabaseHas('roles', ['id' => $adminRole->id, 'key' => 'admin']);

        $this->actingAs($admin)
            ->delete(route('admin.roles.destroy', $adminRole))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['id' => $adminRole->id]);
    }

    public function test_private_attachment_download_requires_request_visibility(): void
    {
        Storage::fake('local');

        $department = Department::create(['name' => 'Engineering', 'code' => 'ENG']);
        $role = Role::create(['name' => 'Employee', 'key' => 'employee']);
        $owner = User::factory()->create(['department_id' => $department->id, 'role_id' => $role->id, 'is_active' => true]);
        $other = User::factory()->create(['department_id' => $department->id, 'role_id' => $role->id, 'is_active' => true]);
        $template = FormTemplate::create(['name' => 'Private Request', 'code' => 'PRIVATE', 'is_active' => true, 'created_by' => $owner->id]);
        $request = WorkflowRequest::create([
            'request_code' => 'PRIVATE-001',
            'form_template_id' => $template->id,
            'workflow_template_id' => null,
            'current_step_id' => null,
            'created_by' => $owner->id,
            'status' => WorkflowRequest::STATUS_RETURNED,
            'submitted_at' => now(),
        ]);

        Storage::disk('local')->put('request_attachments/private.txt', 'secret');
        $attachment = Attachment::create([
            'request_id' => $request->id,
            'form_field_id' => null,
            'original_name' => 'private.txt',
            'path' => 'request_attachments/private.txt',
            'mime_type' => 'text/plain',
            'size' => 6,
            'uploaded_by' => $owner->id,
        ]);

        $this->actingAs($other)->get(route('attachments.download', $attachment))->assertForbidden();
        $this->actingAs($owner)->get(route('attachments.download', $attachment))->assertOk();
    }

    public function test_disabled_authenticated_user_is_blocked(): void
    {
        $department = Department::create(['name' => 'Engineering', 'code' => 'ENG']);
        $role = Role::create(['name' => 'Employee', 'key' => 'employee']);
        $user = User::factory()->create(['department_id' => $department->id, 'role_id' => $role->id, 'is_active' => false]);

        $this->actingAs($user)->get(route('dashboard'))->assertForbidden();
    }
}
