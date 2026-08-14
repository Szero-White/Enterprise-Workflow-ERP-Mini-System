<?php

namespace Tests\Feature\Workflow;

use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowConfigurationVersioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_one_workflow_can_be_active_for_a_form(): void
    {
        [$admin, $adminRole] = $this->createAdmin();
        $form = $this->createDraftForm($admin);
        FormField::create($this->fieldData($form));

        $first = $this->createWorkflow($form, $admin, $adminRole, 'Workflow v1');
        $second = $this->createWorkflow($form, $admin, $adminRole, 'Workflow v2', 2);

        $this->actingAs($admin)
            ->post(route('admin.workflow-templates.activate', $first))
            ->assertRedirect();

        $this->assertTrue($first->fresh()->is_active);

        $this->actingAs($admin)
            ->post(route('admin.workflow-templates.activate', $second))
            ->assertRedirect();

        $this->assertFalse($first->fresh()->is_active);
        $this->assertTrue($second->fresh()->is_active);
    }

    public function test_form_cannot_be_activated_without_ready_workflow(): void
    {
        [$admin] = $this->createAdmin();
        $form = $this->createDraftForm($admin);
        FormField::create($this->fieldData($form));

        $this->actingAs($admin)
            ->post(route('admin.form-templates.activate', $form))
            ->assertSessionHasErrors('form_template');

        $this->assertFalse($form->fresh()->is_active);
    }

    public function test_first_submission_locks_configuration_and_clone_creates_editable_version(): void
    {
        [$admin, $adminRole] = $this->createAdmin();
        $employeeRole = Role::create(['name' => 'Employee', 'key' => 'employee', 'is_system' => true]);
        $employee = User::factory()->create(['role_id' => $employeeRole->id, 'is_active' => true]);

        $form = $this->createDraftForm($admin);
        $field = FormField::create($this->fieldData($form));
        $workflow = $this->createWorkflow($form, $admin, $adminRole, 'Approval');

        $workflow->update(['is_active' => true]);
        $form->update(['is_active' => true]);

        $this->actingAs($employee)
            ->post(route('employee.requests.store', $form), ['reason' => 'Need approval'])
            ->assertRedirect();

        $this->assertNotNull($form->fresh()->locked_at);
        $this->assertNotNull($workflow->fresh()->locked_at);

        $this->actingAs($admin)
            ->put(route('admin.form-templates.fields.update', [$form, $field]), [
                'label' => 'Changed label',
                'field_key' => 'reason',
                'field_type' => 'textarea',
                'is_required' => 1,
                'sort_order' => 1,
            ])
            ->assertSessionHas('error');

        $this->actingAs($admin)
            ->post(route('admin.form-templates.clone-version', $form))
            ->assertRedirect();

        $clone = FormTemplate::query()->where('code', 'LEAVE')->where('version', 2)->firstOrFail();
        $this->assertFalse($clone->is_active);
        $this->assertNull($clone->locked_at);
        $this->assertCount(1, $clone->fields);
        $this->assertCount(1, $clone->workflows);
        $this->assertCount(1, $clone->workflows->first()->steps);
    }

    private function createAdmin(): array
    {
        $role = Role::create(['name' => 'Admin', 'key' => 'admin', 'is_system' => true]);
        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);

        return [$user, $role];
    }

    private function createDraftForm(User $admin): FormTemplate
    {
        return FormTemplate::create([
            'name' => 'Leave Request',
            'code' => 'LEAVE',
            'version' => 1,
            'submission_type' => 'dynamic',
            'is_active' => false,
            'created_by' => $admin->id,
        ]);
    }

    private function fieldData(FormTemplate $form): array
    {
        return [
            'form_template_id' => $form->id,
            'label' => 'Reason',
            'field_key' => 'reason',
            'field_type' => 'textarea',
            'is_required' => true,
            'sort_order' => 1,
        ];
    }

    private function createWorkflow(
        FormTemplate $form,
        User $admin,
        Role $approverRole,
        string $name,
        int $version = 1
    ): WorkflowTemplate {
        $workflow = WorkflowTemplate::create([
            'form_template_id' => $form->id,
            'name' => $name,
            'version' => $version,
            'is_active' => false,
            'created_by' => $admin->id,
        ]);

        WorkflowStep::create([
            'workflow_template_id' => $workflow->id,
            'step_name' => 'Admin approval',
            'step_order' => 1,
            'approver_type' => WorkflowStep::APPROVER_ROLE,
            'approver_role_id' => $approverRole->id,
        ]);

        return $workflow;
    }
}
