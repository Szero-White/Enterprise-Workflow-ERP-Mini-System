<?php

namespace Tests\Feature\Procurement;

use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\Notification;
use App\Models\PurchaseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsProcurementFixture;
use Tests\TestCase;

class PurchaseRequestOrderFlowTest extends TestCase
{
    use BuildsProcurementFixture;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProcurementFixture();
    }

    public function test_po_cannot_be_created_before_purchase_request_is_approved(): void
    {
        $purchaseRequest = $this->submitPurchaseRequest();

        $this->actingAs($this->procurementUsers['procurement'])
            ->post(route('procurement.purchase-orders.store', $purchaseRequest), [
                'supplier_id' => $this->procurementSupplier->id,
                'warehouse_id' => $this->procurementWarehouse->id,
                'lines' => [[
                    'purchase_request_item_id' => $purchaseRequest->items()->firstOrFail()->id,
                    'unit_cost' => 1_000,
                ]],
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public function test_workflow_approval_unlocks_purchase_order_creation(): void
    {
        $purchaseRequest = $this->submitPurchaseRequest(quantity: 2, estimatedUnitCost: 15_000_000);
        $this->approvePurchaseRequest($purchaseRequest);

        $this->assertSame(PurchaseRequestStatus::Approved, $purchaseRequest->fresh()->status);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->procurementUsers['procurement']->id,
            'type' => Notification::TYPE_PURCHASE_REQUEST_READY,
        ]);

        $procurementNotification = Notification::query()
            ->where('user_id', $this->procurementUsers['procurement']->id)
            ->where('type', Notification::TYPE_PURCHASE_REQUEST_READY)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(
            $purchaseRequest->id,
            (int) data_get($procurementNotification->data, 'purchase_request_id')
        );

        $purchaseOrder = $this->createAndIssuePurchaseOrder(
            $purchaseRequest,
            unitCost: 14_500_000
        );

        $orderItem = $purchaseOrder->items()->firstOrFail();

        $this->assertSame(PurchaseOrderStatus::Issued, $purchaseOrder->status);
        $this->assertSame(PurchaseRequestStatus::Ordered, $purchaseRequest->fresh()->status);
        $this->assertSame('LAP-01', $orderItem->item_sku);
        $this->assertSame('Laptop', $orderItem->item_name);
        $this->assertSame($purchaseRequest->items()->firstOrFail()->id, $orderItem->purchase_request_item_id);
    }

    public function test_requester_can_see_return_reason_and_approval_progress(): void
    {
        $purchaseRequest = $this->submitPurchaseRequest();
        $comment = 'Vui lòng bổ sung đơn giá dự kiến trước khi gửi lại.';

        $this->actingAs($this->procurementUsers['manager'])
            ->post(route('manager.approvals.return', $purchaseRequest->workflowRequest), [
                'comment' => $comment,
            ])
            ->assertRedirect();

        $notification = Notification::query()
            ->where('user_id', $this->procurementUsers['employee']->id)
            ->where('type', Notification::TYPE_REQUEST_RETURNED)
            ->latest('id')
            ->firstOrFail();

        $this->assertStringContainsString($comment, $notification->message);
        $this->assertSame($this->procurementUsers['employee']->name, data_get($notification->data, 'requester_name'));
        $this->assertSame('employee_request', data_get($notification->data, 'destination'));

        $this->actingAs($this->procurementUsers['employee'])
            ->get(route('procurement.purchase-requests.show', $purchaseRequest))
            ->assertOk()
            ->assertSee(__('ui.approval_progress'))
            ->assertSee(__('ui.return_reason'))
            ->assertSee($comment);
    }

    public function test_cancelled_po_can_be_replaced_while_preserving_history(): void
    {
        $purchaseRequest = $this->submitPurchaseRequest();
        $this->approvePurchaseRequest($purchaseRequest);
        $firstOrder = $this->createAndIssuePurchaseOrder($purchaseRequest, unitCost: 1_000);

        $this->actingAs($this->procurementUsers['procurement'])
            ->post(route('procurement.purchase-orders.cancel', $firstOrder))
            ->assertRedirect();

        $this->assertSame(PurchaseOrderStatus::Cancelled, $firstOrder->fresh()->status);
        $this->assertSame(PurchaseRequestStatus::Approved, $purchaseRequest->fresh()->status);

        $this->actingAs($this->procurementUsers['procurement'])
            ->post(route('procurement.purchase-orders.store', $purchaseRequest), [
                'supplier_id' => $this->procurementSupplier->id,
                'warehouse_id' => $this->procurementWarehouse->id,
                'lines' => [[
                    'purchase_request_item_id' => $purchaseRequest->items()->firstOrFail()->id,
                    'unit_cost' => 1_100,
                ]],
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('purchase_orders', 2);
        $this->assertSame(
            PurchaseOrderStatus::Draft,
            PurchaseOrder::query()->latest('id')->firstOrFail()->status
        );
    }
}
