<?php

namespace Tests\Feature\Workflow;

use App\Enums\LifecycleStatus;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowRequest;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use App\Services\Workflow\WorkflowLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_workflow_statuses_follow_form_version_lifecycle(): void
    {
        [$admin, $role] = $this->admin();
        $oldForm = $this->form($admin, 1, true);
        $oldWorkflow = $this->workflow($oldForm, $admin, $role, true);
        $draftForm = $this->form($admin, 2, false);
        $draftWorkflow = $this->workflow($draftForm, $admin, $role, false);

        $service = app(WorkflowLifecycleService::class);
        $oldWorkflow = $oldWorkflow->fresh('formTemplate');
        $draftWorkflow = $draftWorkflow->fresh('formTemplate');
        $service->decorateWorkflows(collect([$oldWorkflow, $draftWorkflow]));

        $this->assertSame(LifecycleStatus::Current, $oldWorkflow->getAttribute('lifecycle_status'));
        $this->assertSame(LifecycleStatus::Draft, $draftWorkflow->getAttribute('lifecycle_status'));

        $oldForm->update(['is_active' => false]);
        $draftForm->update(['is_active' => true]);
        $oldWorkflow = $oldWorkflow->fresh('formTemplate');
        $service->decorateWorkflows(collect([$oldWorkflow]));

        $this->assertSame(LifecycleStatus::Inactive, $oldWorkflow->getAttribute('lifecycle_status'));
    }

    public function test_publishing_new_form_keeps_legacy_workflow_active_while_open_request_exists(): void
    {
        [$admin, $role] = $this->admin();
        $oldForm = $this->form($admin, 1, true);
        FormField::create($this->fieldData($oldForm));
        $oldWorkflow = $this->workflow($oldForm, $admin, $role, true);

        WorkflowRequest::create([
            'request_code' => 'REQ-OLD-001',
            'form_template_id' => $oldForm->id,
            'workflow_template_id' => $oldWorkflow->id,
            'current_step_id' => $oldWorkflow->steps()->firstOrFail()->id,
            'created_by' => $admin->id,
            'status' => WorkflowRequest::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        $newForm = $this->form($admin, 2, false);
        FormField::create($this->fieldData($newForm));
        $newWorkflow = $this->workflow($newForm, $admin, $role, false);

        $this->actingAs($admin)->post(route('admin.form-templates.activate', $newForm))->assertRedirect();

        $this->assertTrue($newWorkflow->fresh()->is_active);
        $this->assertTrue($oldWorkflow->fresh()->is_active);
        $this->assertFalse($oldForm->fresh()->is_active);
    }

    public function test_legacy_workflow_auto_retires_when_last_open_request_finishes(): void
    {
        [$admin, $role] = $this->admin();
        $oldForm = $this->form($admin, 1, false);
        $oldWorkflow = $this->workflow($oldForm, $admin, $role, true);
        $newForm = $this->form($admin, 2, true);
        $this->workflow($newForm, $admin, $role, true);

        $request = WorkflowRequest::create([
            'request_code' => 'REQ-OLD-002',
            'form_template_id' => $oldForm->id,
            'workflow_template_id' => $oldWorkflow->id,
            'current_step_id' => $oldWorkflow->steps()->firstOrFail()->id,
            'created_by' => $admin->id,
            'status' => WorkflowRequest::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('manager.approvals.approve', $request), ['comment' => 'Approved'])
            ->assertRedirect();

        $this->assertSame(WorkflowRequest::STATUS_APPROVED, $request->fresh()->status);
        $this->assertFalse($oldWorkflow->fresh()->is_active);
    }

    private function admin(): array
    {
        $role = Role::create(['name' => 'Admin', 'key' => 'admin', 'is_system' => true]);
        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);

        return [$user, $role];
    }

    private function form(User $admin, int $version, bool $active): FormTemplate
    {
        return FormTemplate::create([
            'name' => 'Leave Request',
            'code' => 'LEAVE',
            'version' => $version,
            'submission_type' => 'dynamic',
            'is_active' => $active,
            'created_by' => $admin->id,
        ]);
    }

    private function workflow(FormTemplate $form, User $admin, Role $role, bool $active): WorkflowTemplate
    {
        $workflow = WorkflowTemplate::create([
            'form_template_id' => $form->id,
            'name' => 'Leave approval',
            'version' => 1,
            'is_active' => $active,
            'created_by' => $admin->id,
        ]);

        WorkflowStep::create([
            'workflow_template_id' => $workflow->id,
            'step_name' => 'Approval',
            'step_order' => 1,
            'approver_type' => WorkflowStep::APPROVER_ROLE,
            'approver_role_id' => $role->id,
        ]);

        return $workflow;
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
}
