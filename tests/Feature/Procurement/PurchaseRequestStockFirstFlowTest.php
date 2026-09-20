<?php

namespace Tests\Feature\Procurement;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Enums\PurchaseRequestFulfillmentRoute;
use App\Enums\PurchaseRequestStatus;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\FormTemplate;
use App\Models\InventoryStock;
use App\Models\Notification;
use App\Models\WorkflowRequest;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsProcurementFixture;
use Tests\TestCase;

class PurchaseRequestStockFirstFlowTest extends TestCase
{
    use BuildsProcurementFixture;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProcurementFixture();
    }

    public function test_manager_approval_routes_fully_available_tracked_assets_to_stock_and_skips_purchase_approvals(): void
    {
        $asset = $this->createAvailableAsset();
        $purchaseRequest = $this->submitPurchaseRequest();
        $line = $purchaseRequest->items()->firstOrFail();

        $this->actingAs($this->procurementUsers['manager'])
            ->post(route('manager.approvals.approve', $purchaseRequest->workflowRequest), [
                'comment' => 'Nhu cầu hợp lệ',
            ])
            ->assertRedirect();

        $purchaseRequest->refresh();
        $workflowRequest = $purchaseRequest->workflowRequest->fresh();

        $this->assertSame(WorkflowRequest::STATUS_APPROVED, $workflowRequest->status);
        $this->assertNull($workflowRequest->current_step_id);
        $this->assertSame(PurchaseRequestStatus::Approved, $purchaseRequest->status);
        $this->assertSame(PurchaseRequestFulfillmentRoute::Stock, $purchaseRequest->fulfillment_route);

        $asset->refresh();
        $this->assertSame(AssetStatus::Reserved, $asset->status);
        $this->assertSame($line->id, $asset->reserved_for_purchase_request_item_id);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->procurementUsers['asset_manager']->id,
            'type' => Notification::TYPE_PURCHASE_REQUEST_STOCK_READY,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->procurementUsers['procurement']->id,
            'type' => Notification::TYPE_PURCHASE_REQUEST_READY,
        ]);

        $this->actingAs($this->procurementUsers['employee'])
            ->get(route('procurement.purchase-requests.show', $purchaseRequest))
            ->assertOk()
            ->assertSee(__('ui.approval_skipped_stock'));
    }

    public function test_asset_manager_can_assign_reserved_assets_to_selected_users_and_close_the_request(): void
    {
        $asset = $this->createAvailableAsset();
        $purchaseRequest = $this->submitPurchaseRequest();
        $line = $purchaseRequest->items()->firstOrFail();

        $this->actingAs($this->procurementUsers['manager'])
            ->post(route('manager.approvals.approve', $purchaseRequest->workflowRequest), [
                'comment' => 'Nhu cầu hợp lệ',
            ])
            ->assertRedirect();

        $this->actingAs($this->procurementUsers['asset_manager'])
            ->get(route('procurement.purchase-requests.stock-fulfillment.show', [$purchaseRequest, $line]))
            ->assertOk()
            ->assertSee($asset->asset_code)
            ->assertSee($this->procurementUsers['employee']->email);

        $this->actingAs($this->procurementUsers['asset_manager'])
            ->post(route('procurement.purchase-requests.stock-fulfillment.store', [$purchaseRequest, $line]), [
                'assignments' => [
                    $asset->id => $this->procurementUsers['employee']->id,
                ],
            ])
            ->assertRedirect(route('procurement.purchase-requests.show', $purchaseRequest));

        $this->assertSame(PurchaseRequestStatus::Closed, $purchaseRequest->fresh()->status);
        $this->assertSame(AssetStatus::Assigned, $asset->fresh()->status);
        $this->assertNull($asset->fresh()->warehouse_id);
        $this->assertNull($asset->fresh()->reserved_for_purchase_request_item_id);

        $assignment = AssetAssignment::query()->where('asset_id', $asset->id)->firstOrFail();
        $this->assertSame($line->id, $assignment->purchase_request_item_id);
        $this->assertSame($this->procurementUsers['employee']->id, $assignment->assigned_to);
        $this->assertEquals(0.0, $this->stockQuantity());

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->procurementUsers['employee']->id,
            'type' => Notification::TYPE_PURCHASE_REQUEST_STOCK_FULFILLED,
        ]);

        $this->actingAs($this->procurementUsers['procurement'])
            ->get(route('procurement.purchase-orders.create', $purchaseRequest->fresh()))
            ->assertStatus(422);
    }

    public function test_missing_stock_keeps_request_in_purchase_approval_flow(): void
    {
        $purchaseRequest = $this->submitPurchaseRequest();

        $this->actingAs($this->procurementUsers['manager'])
            ->post(route('manager.approvals.approve', $purchaseRequest->workflowRequest), [
                'comment' => 'Nhu cầu hợp lệ',
            ])
            ->assertRedirect();

        $purchaseRequest->refresh();
        $workflowRequest = $purchaseRequest->workflowRequest->fresh('currentStep.approverRole');

        $this->assertSame(PurchaseRequestFulfillmentRoute::Procurement, $purchaseRequest->fulfillment_route);
        $this->assertSame(PurchaseRequestStatus::PendingApproval, $purchaseRequest->status);
        $this->assertSame(WorkflowRequest::STATUS_PENDING, $workflowRequest->status);
        $this->assertSame('procurement', $workflowRequest->currentStep?->approverRole?->key);
        $this->assertDatabaseCount('assets', 0);
    }

    public function test_non_trackable_stock_does_not_bypass_procurement_until_stock_issue_exists(): void
    {
        $this->procurementItem->update(['is_asset_trackable' => false]);
        InventoryStock::create([
            'warehouse_id' => $this->procurementWarehouse->id,
            'item_id' => $this->procurementItem->id,
            'quantity' => 20,
        ]);

        $purchaseRequest = $this->submitPurchaseRequest(quantity: 5);

        $this->actingAs($this->procurementUsers['manager'])
            ->post(route('manager.approvals.approve', $purchaseRequest->workflowRequest), [
                'comment' => 'Nhu cầu hợp lệ',
            ])
            ->assertRedirect();

        $this->assertSame(
            PurchaseRequestFulfillmentRoute::Procurement,
            $purchaseRequest->fresh()->fulfillment_route
        );
        $this->assertEquals(20.0, $this->stockQuantity());
    }

    public function test_purchase_request_workflow_cannot_be_activated_without_manager_as_first_step(): void
    {
        $purchaseRequestForm = FormTemplate::query()
            ->where('code', 'PURCHASE_REQUEST')
            ->firstOrFail();

        $workflow = WorkflowTemplate::create([
            'form_template_id' => $purchaseRequestForm->id,
            'name' => 'Invalid PR flow',
            'version' => 2,
            'is_active' => false,
            'created_by' => $this->procurementUsers['admin']->id,
        ]);

        WorkflowStep::create([
            'workflow_template_id' => $workflow->id,
            'step_name' => 'Finance first',
            'step_order' => 1,
            'approver_type' => WorkflowStep::APPROVER_ROLE,
            'approver_role_id' => $this->procurementUsers['finance']->role_id,
        ]);

        $this->actingAs($this->procurementUsers['admin'])
            ->post(route('admin.workflow-templates.activate', $workflow))
            ->assertSessionHasErrors('workflow_template');

        $this->assertFalse($workflow->fresh()->is_active);
    }

    public function test_purchase_request_workflow_cannot_be_activated_without_an_active_manager(): void
    {
        $purchaseRequestForm = FormTemplate::query()
            ->where('code', 'PURCHASE_REQUEST')
            ->firstOrFail();

        $workflow = WorkflowTemplate::create([
            'form_template_id' => $purchaseRequestForm->id,
            'name' => 'PR flow v2',
            'version' => 2,
            'is_active' => false,
            'created_by' => $this->procurementUsers['admin']->id,
        ]);

        WorkflowStep::create([
            'workflow_template_id' => $workflow->id,
            'step_name' => 'Manager first',
            'step_order' => 1,
            'approver_type' => WorkflowStep::APPROVER_ROLE,
            'approver_role_id' => $this->procurementUsers['manager']->role_id,
        ]);

        $this->procurementUsers['manager']->forceFill(['is_active' => false])->save();

        $this->actingAs($this->procurementUsers['admin'])
            ->post(route('admin.workflow-templates.activate', $workflow))
            ->assertSessionHasErrors('workflow_template');

        $this->assertFalse($workflow->fresh()->is_active);
    }

    private function createAvailableAsset(): Asset
    {
        InventoryStock::create([
            'warehouse_id' => $this->procurementWarehouse->id,
            'item_id' => $this->procurementItem->id,
            'quantity' => 1,
        ]);

        return Asset::create([
            'asset_code' => 'AST-STOCK-001',
            'item_id' => $this->procurementItem->id,
            'warehouse_id' => $this->procurementWarehouse->id,
            'acquired_at' => now()->subMonth()->toDateString(),
            'acquisition_cost' => 10_000_000,
            'status' => AssetStatus::Available,
            'condition' => AssetCondition::Good,
        ]);
    }

    private function stockQuantity(): float
    {
        return (float) InventoryStock::query()
            ->where('warehouse_id', $this->procurementWarehouse->id)
            ->where('item_id', $this->procurementItem->id)
            ->value('quantity');
    }
}
