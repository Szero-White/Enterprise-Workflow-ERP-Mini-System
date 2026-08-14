<?php

namespace Tests\Support;

use App\Models\Department;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;

trait BuildsProcurementFixture
{
    /** @var array<string, User> */
    protected array $procurementUsers = [];

    protected Item $procurementItem;
    protected Warehouse $procurementWarehouse;
    protected Supplier $procurementSupplier;

    protected function seedProcurementFixture(): void
    {
        $department = Department::create([
            'name' => 'Operations',
            'code' => 'OPS',
        ]);

        $roles = [];
        foreach (['employee', 'manager', 'procurement', 'finance', 'director', 'asset_manager', 'admin'] as $key) {
            $roles[$key] = Role::create([
                'name' => ucfirst($key),
                'key' => $key,
            ]);

            $this->procurementUsers[$key] = User::factory()->create([
                'role_id' => $roles[$key]->id,
                'department_id' => $department->id,
                'is_active' => true,
            ]);
        }

        $template = FormTemplate::create([
            'name' => 'Purchase Request',
            'code' => 'PURCHASE_REQUEST',
            'description' => 'Procurement request',
            'submission_type' => 'procurement',
            'is_active' => true,
            'created_by' => $this->procurementUsers['admin']->id,
        ]);

        foreach ([
            ['Purpose', 'purpose', 'textarea'],
            ['Required Date', 'required_date', 'date'],
            ['Estimated Total', 'estimated_total', 'number'],
        ] as $index => $field) {
            FormField::create([
                'form_template_id' => $template->id,
                'label' => $field[0],
                'field_key' => $field[1],
                'field_type' => $field[2],
                'is_required' => $index === 0,
                'sort_order' => $index + 1,
            ]);
        }

        $workflow = WorkflowTemplate::create([
            'form_template_id' => $template->id,
            'name' => 'PR approval',
            'is_active' => true,
            'created_by' => $this->procurementUsers['admin']->id,
        ]);

        foreach (['manager', 'procurement', 'finance', 'director'] as $index => $key) {
            WorkflowStep::create([
                'workflow_template_id' => $workflow->id,
                'step_name' => ucfirst($key),
                'step_order' => $index + 1,
                'approver_type' => WorkflowStep::APPROVER_ROLE,
                'approver_role_id' => $roles[$key]->id,
            ]);
        }

        $category = ItemCategory::create([
            'name' => 'IT',
            'code' => 'IT',
            'is_active' => true,
        ]);

        $this->procurementItem = Item::create([
            'category_id' => $category->id,
            'sku' => 'LAP-01',
            'name' => 'Laptop',
            'unit' => 'cái',
            'cost_price' => 10_000_000,
            'reorder_level' => 1,
            'is_asset_trackable' => true,
            'is_active' => true,
        ]);

        $this->procurementWarehouse = Warehouse::create([
            'code' => 'WH-01',
            'name' => 'Main',
            'is_active' => true,
        ]);

        $this->procurementSupplier = Supplier::create([
            'code' => 'SUP-01',
            'name' => 'Supplier',
            'is_active' => true,
        ]);
    }

    protected function submitPurchaseRequest(
        float $quantity = 1,
        float $estimatedUnitCost = 1_000
    ): PurchaseRequest {
        $this->actingAs($this->procurementUsers['employee'])
            ->post(route('procurement.purchase-requests.store'), [
                'purpose' => 'Trang bị vật tư phục vụ vận hành',
                'required_date' => now()->addWeek()->toDateString(),
                'items' => [[
                    'item_id' => $this->procurementItem->id,
                    'quantity' => $quantity,
                    'estimated_unit_cost' => $estimatedUnitCost,
                    'note' => 'Vật tư tiêu chuẩn',
                ]],
            ])
            ->assertRedirect();

        return PurchaseRequest::query()->with('workflowRequest')->firstOrFail();
    }

    protected function approvePurchaseRequest(PurchaseRequest $purchaseRequest): void
    {
        foreach (['manager', 'procurement', 'finance', 'director'] as $role) {
            $this->actingAs($this->procurementUsers[$role])
                ->post(route('manager.approvals.approve', $purchaseRequest->workflowRequest->fresh()), [
                    'comment' => 'Approved',
                ])
                ->assertRedirect();
        }
    }

    protected function createAndIssuePurchaseOrder(
        PurchaseRequest $purchaseRequest,
        float $unitCost
    ): PurchaseOrder {
        $this->actingAs($this->procurementUsers['procurement'])
            ->post(route('procurement.purchase-orders.store', $purchaseRequest), [
                'supplier_id' => $this->procurementSupplier->id,
                'warehouse_id' => $this->procurementWarehouse->id,
                'lines' => [[
                    'purchase_request_item_id' => $purchaseRequest->items()->firstOrFail()->id,
                    'unit_cost' => $unitCost,
                ]],
            ])
            ->assertRedirect();

        $purchaseOrder = PurchaseOrder::query()->latest('id')->firstOrFail();

        $this->actingAs($this->procurementUsers['procurement'])
            ->post(route('procurement.purchase-orders.issue', $purchaseOrder))
            ->assertRedirect();

        return $purchaseOrder->fresh();
    }
}
