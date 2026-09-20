<?php

namespace Tests\Feature\Workflow;

use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\RequestValue;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use App\Services\DynamicFieldConditionService;
use App\Services\DynamicFieldValidationService;
use App\Services\DynamicRequestService;
use App\Services\Workflow\WorkflowConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DynamicConditionalFieldTest extends TestCase
{
    use RefreshDatabase;

    public function test_conditional_required_field_is_enforced_only_when_condition_matches(): void
    {
        [$form] = $this->createConditionalLeaveForm();
        $service = app(DynamicFieldValidationService::class);

        $normalInput = ['leave_type' => 'Nghỉ phép năm'];
        $normalValidator = Validator::make($normalInput, $service->rulesFor($form, $normalInput));
        $this->assertFalse($normalValidator->fails());

        $otherInput = ['leave_type' => 'Khác'];
        $otherValidator = Validator::make($otherInput, $service->rulesFor($form, $otherInput));
        $this->assertTrue($otherValidator->fails());
        $this->assertTrue($otherValidator->errors()->has('leave_reason'));

        $completeInput = ['leave_type' => 'Khác', 'leave_reason' => 'Giải quyết việc gia đình đột xuất'];
        $completeValidator = Validator::make($completeInput, $service->rulesFor($form, $completeInput));
        $this->assertFalse($completeValidator->fails());
    }

    public function test_hidden_conditional_value_is_not_persisted_even_if_client_submits_it(): void
    {
        [$form, $employee] = $this->createConditionalLeaveForm(withWorkflow: true);

        $request = Request::create('/requests', 'POST', [
            'leave_type' => 'Nghỉ phép năm',
            'leave_reason' => 'Giá trị giả từ client',
        ]);

        $workflowRequest = app(DynamicRequestService::class)->create($employee, $form, $request);

        $reason = RequestValue::query()
            ->where('request_id', $workflowRequest->id)
            ->where('field_key', 'leave_reason')
            ->firstOrFail();

        $this->assertNull($reason->value);
    }

    public function test_clone_form_version_preserves_generic_field_condition(): void
    {
        [$form, $admin] = $this->createConditionalLeaveForm(withWorkflow: true, userRoleKey: 'admin');

        $clone = app(WorkflowConfigurationService::class)->cloneFormVersion($form, $admin);
        $reason = $clone->fields->firstWhere('field_key', 'leave_reason');

        $this->assertNotNull($reason);
        $this->assertSame('leave_type', $reason->condition_field_key);
        $this->assertSame(FormField::CONDITION_EQUALS, $reason->condition_operator);
        $this->assertSame('Khác', $reason->condition_value);
        $this->assertTrue($reason->is_required);
    }

    public function test_template_validation_rejects_condition_that_depends_on_a_later_field(): void
    {
        [$form] = $this->createConditionalLeaveForm();
        $source = $form->fields()->where('field_key', 'leave_type')->firstOrFail();
        $source->update(['sort_order' => 10]);

        $this->expectException(ValidationException::class);
        app(DynamicFieldConditionService::class)->ensureTemplateConditionsValid($form->fresh('fields'));
    }

    public function test_dynamic_request_ui_exposes_condition_metadata_for_frontend_visibility(): void
    {
        [$form, $employee] = $this->createConditionalLeaveForm(withWorkflow: true);

        $this->actingAs($employee)
            ->get(route('employee.requests.create', $form))
            ->assertOk()
            ->assertSee('data-dynamic-form', false)
            ->assertSee('data-condition-field-key="leave_type"', false)
            ->assertSee('data-condition-operator="equals"', false)
            ->assertSee('data-condition-value="Khác"', false);
    }

    private function createConditionalLeaveForm(
        bool $withWorkflow = false,
        string $userRoleKey = 'employee'
    ): array {
        $role = Role::create([
            'name' => ucfirst($userRoleKey),
            'key' => $userRoleKey,
            'is_system' => true,
        ]);
        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);

        $form = FormTemplate::create([
            'name' => 'Đơn xin nghỉ phép',
            'code' => 'LEAVE_CONDITIONAL',
            'version' => 1,
            'submission_type' => 'dynamic',
            'is_active' => $withWorkflow,
            'created_by' => $user->id,
        ]);

        FormField::create([
            'form_template_id' => $form->id,
            'label' => 'Loại nghỉ phép',
            'field_key' => 'leave_type',
            'field_type' => 'select',
            'is_required' => true,
            'options' => ['Nghỉ phép năm', 'Nghỉ ốm', 'Nghỉ không lương', 'Khác'],
            'sort_order' => 1,
        ]);

        FormField::create([
            'form_template_id' => $form->id,
            'label' => 'Lý do khác',
            'field_key' => 'leave_reason',
            'field_type' => 'textarea',
            'is_required' => true,
            'condition_field_key' => 'leave_type',
            'condition_operator' => FormField::CONDITION_EQUALS,
            'condition_value' => 'Khác',
            'sort_order' => 2,
        ]);

        if ($withWorkflow) {
            $approverRole = Role::query()->firstOrCreate(
                ['key' => 'approver'],
                ['name' => 'Approver', 'is_system' => true]
            );
            User::factory()->create(['role_id' => $approverRole->id, 'is_active' => true]);

            $workflow = WorkflowTemplate::create([
                'form_template_id' => $form->id,
                'name' => 'Leave approval',
                'version' => 1,
                'is_active' => true,
                'created_by' => $user->id,
            ]);

            WorkflowStep::create([
                'workflow_template_id' => $workflow->id,
                'step_name' => 'Approval',
                'step_order' => 1,
                'approver_type' => WorkflowStep::APPROVER_ROLE,
                'approver_role_id' => $approverRole->id,
            ]);
        }

        return [$form, $user];
    }
}
