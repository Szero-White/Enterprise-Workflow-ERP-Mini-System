<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\FormTemplate;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationsUiRenderingTest extends TestCase
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

        $role = Role::create([
            'name' => 'Admin',
            'key' => 'admin',
        ]);

        $this->admin = User::factory()->create([
            'department_id' => $department->id,
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_operations_workspace_pages_render_successfully(): void
    {
        $formTemplate = FormTemplate::create([
            'name' => 'UI Test Form',
            'code' => 'UI_TEST',
            'submission_type' => 'dynamic',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);
        $workflowTemplate = WorkflowTemplate::create([
            'form_template_id' => $formTemplate->id,
            'name' => 'UI Test Workflow',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);
        WorkflowStep::create([
            'workflow_template_id' => $workflowTemplate->id,
            'step_name' => 'Admin review',
            'step_order' => 1,
            'approver_type' => WorkflowStep::APPROVER_ROLE,
            'approver_role_id' => $this->admin->role_id,
        ]);

        $routes = [
            route('dashboard'),
            route('admin.users.index'),
            route('admin.roles.index'),
            route('admin.departments.index'),
            route('admin.audit-logs.index'),
            route('admin.form-templates.index'),
            route('admin.form-templates.show', $formTemplate),
            route('admin.form-templates.fields.index', $formTemplate),
            route('admin.workflow-templates.index'),
            route('admin.workflow-templates.show', $workflowTemplate),
            route('admin.workflow-templates.steps.index', $workflowTemplate),
            route('employee.requests.index'),
            route('manager.approvals.index'),
            route('manager.approvals.history'),
            route('notifications.index'),
            route('inventory.items.index'),
            route('inventory.items.create'),
            route('inventory.item-categories.index'),
            route('inventory.item-categories.create'),
            route('inventory.stocks.index'),
            route('inventory.receipts.create'),
            route('inventory.warehouses.index'),
            route('inventory.warehouses.create'),
            route('procurement.purchase-requests.index'),
            route('procurement.purchase-requests.create'),
            route('procurement.suppliers.index'),
            route('procurement.suppliers.create'),
            route('procurement.purchase-orders.index'),
            route('procurement.goods-receipts.index'),
            route('assets.index'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($this->admin)
                ->get($url)
                ->assertOk();
        }
    }
}
