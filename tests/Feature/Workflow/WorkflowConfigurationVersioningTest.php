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

    public function test_publishing_cloned_form_automatically_activates_inherited_workflow(): void
    {
        [$admin, $adminRole] = $this->createAdmin();

        $source = $this->createDraftForm($admin);
        FormField::create($this->fieldData($source));
        $sourceWorkflow = $this->createWorkflow($source, $admin, $adminRole, 'Leave approval');
        $sourceWorkflow->update(['is_active' => true]);
        $source->update(['is_active' => true]);

        $this->actingAs($admin)
            ->post(route('admin.form-templates.clone-version', $source))
            ->assertRedirect();

        $clone = FormTemplate::query()->where('code', 'LEAVE')->where('version', 2)->firstOrFail();
        $inheritedWorkflow = $clone->workflows()->with('steps')->firstOrFail();

        $this->assertFalse($clone->is_active);
        $this->assertFalse($inheritedWorkflow->is_active);
        $this->assertCount(1, $inheritedWorkflow->steps);

        $this->actingAs($admin)
            ->post(route('admin.form-templates.activate', $clone))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue($clone->fresh()->is_active);
        $this->assertFalse($source->fresh()->is_active);
        $this->assertTrue($inheritedWorkflow->fresh()->is_active);
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
            ->assertForbidden();

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

    public function test_form_actions_follow_draft_current_and_legacy_lifecycle(): void
    {
        [$admin, $adminRole] = $this->createAdmin();

        $current = $this->createDraftForm($admin);
        FormField::create($this->fieldData($current));
        $currentWorkflow = $this->createWorkflow($current, $admin, $adminRole, 'Current approval');
        $currentWorkflow->update(['is_active' => true]);
        $current->update(['is_active' => true]);

        $this->actingAs($admin)
            ->get(route('admin.form-templates.edit', $current))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.form-templates.fields.create', $current))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.form-templates.clone-version', $current))
            ->assertRedirect();

        $draft = FormTemplate::query()->where('code', 'LEAVE')->where('version', 2)->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.form-templates.edit', $draft))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.form-templates.fields.create', $draft))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('admin.form-templates.clone-version', $draft))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.form-templates.activate', $draft))
            ->assertRedirect();

        $legacy = $current->fresh();
        $this->assertFalse($legacy->is_active);

        $this->actingAs($admin)
            ->get(route('admin.form-templates.edit', $legacy))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.form-templates.clone-version', $legacy))
            ->assertForbidden();
    }

    public function test_workflow_actions_follow_draft_and_current_lifecycle(): void
    {
        [$admin, $adminRole] = $this->createAdmin();
        $form = $this->createDraftForm($admin);
        FormField::create($this->fieldData($form));
        $workflow = $this->createWorkflow($form, $admin, $adminRole, 'Approval');

        $workflow->update(['is_active' => true]);
        $form->update(['is_active' => true]);

        $this->actingAs($admin)
            ->get(route('admin.workflow-templates.edit', $workflow))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.workflow-templates.steps.create', $workflow))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.workflow-templates.clone-version', $workflow))
            ->assertRedirect();

        $draft = WorkflowTemplate::query()
            ->where('form_template_id', $form->id)
            ->where('version', 2)
            ->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.workflow-templates.edit', $draft))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.workflow-templates.steps.create', $draft))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('admin.workflow-templates.clone-version', $draft))
            ->assertForbidden();
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
