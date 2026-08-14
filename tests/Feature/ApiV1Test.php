<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use RefreshDatabase;

    public function test_basic_auth_api_lists_items_and_rejects_guest(): void
    {
        [$admin] = $this->user('admin');
        $category = ItemCategory::create(['name' => 'IT', 'code' => 'IT', 'is_active' => true]);
        Item::create(['category_id' => $category->id, 'sku' => 'API-001', 'name' => 'API Item', 'unit' => 'cái', 'cost_price' => 1000, 'reorder_level' => 1, 'is_active' => true]);

        $this->getJson('/api/v1/items')->assertUnauthorized();

        $this->withHeaders($this->basic($admin->email, 'password'))
            ->getJson('/api/v1/items')
            ->assertOk()
            ->assertJsonPath('data.0.sku', 'API-001');
    }

    public function test_employee_can_create_purchase_request_via_api(): void
    {
        [$employee, $department] = $this->user('employee');
        [$manager] = $this->user('manager', $department);
        $category = ItemCategory::create(['name' => 'IT', 'code' => 'IT', 'is_active' => true]);
        $item = Item::create(['category_id' => $category->id, 'sku' => 'LAP-API', 'name' => 'Laptop API', 'unit' => 'cái', 'cost_price' => 1000, 'reorder_level' => 1, 'is_active' => true]);

        $template = FormTemplate::create([
            'name' => 'Yêu cầu mua hàng',
            'code' => 'PURCHASE_REQUEST',
            'submission_type' => 'procurement',
            'is_active' => true,
            'created_by' => $employee->id,
        ]);
        foreach ([
            ['purpose', 'Mục đích', 'textarea', true, 1],
            ['required_date', 'Ngày cần hàng', 'date', false, 2],
            ['estimated_total', 'Ngân sách dự kiến', 'number', true, 3],
        ] as [$key, $label, $type, $required, $order]) {
            FormField::create(['form_template_id' => $template->id, 'label' => $label, 'field_key' => $key, 'field_type' => $type, 'is_required' => $required, 'sort_order' => $order]);
        }

        $workflow = WorkflowTemplate::create(['form_template_id' => $template->id, 'name' => 'PR approval', 'is_active' => true, 'created_by' => $employee->id]);
        WorkflowStep::create([
            'workflow_template_id' => $workflow->id,
            'step_name' => 'Manager',
            'step_order' => 1,
            'approver_type' => WorkflowStep::APPROVER_ROLE,
            'approver_role_id' => $manager->role_id,
        ]);

        $payload = [
            'purpose' => 'Mua laptop cho nhân viên mới',
            'required_date' => now()->addWeek()->toDateString(),
            'items' => [[
                'item_id' => $item->id,
                'quantity' => 2,
                'estimated_unit_cost' => 18000000,
                'note' => 'Business laptop',
            ]],
        ];

        $this->withHeaders($this->basic($employee->email, 'password'))
            ->postJson('/api/v1/purchase-requests', $payload)
            ->assertCreated()
            ->assertJsonPath('data.purpose', 'Mua laptop cho nhân viên mới')
            ->assertJsonPath('data.items.0.sku', 'LAP-API');
    }

    private function user(string $roleKey, ?Department $department = null): array
    {
        $department ??= Department::firstOrCreate(['code' => strtoupper($roleKey)], ['name' => ucfirst($roleKey)]);
        $role = Role::firstOrCreate(['key' => $roleKey], ['name' => ucfirst($roleKey)]);
        $user = User::factory()->create(['email' => $roleKey.uniqid().'@example.com', 'department_id' => $department->id, 'role_id' => $role->id, 'is_active' => true]);

        return [$user, $department];
    }

    private function basic(string $email, string $password): array
    {
        return ['Authorization' => 'Basic '.base64_encode($email.':'.$password)];
    }
}
