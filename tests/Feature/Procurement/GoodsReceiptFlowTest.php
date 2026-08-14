<?php

namespace Tests\Feature\Procurement;

use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\GoodsReceipt;
use App\Models\InventoryStock;
use App\Models\PurchaseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsProcurementFixture;
use Tests\TestCase;

class GoodsReceiptFlowTest extends TestCase
{
    use RefreshDatabase;
    use BuildsProcurementFixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProcurementFixture();
    }

    public function test_goods_receipt_posts_inventory_and_closes_completed_purchase(): void
    {
        $purchaseRequest = $this->submitPurchaseRequest(quantity: 2);
        $this->approvePurchaseRequest($purchaseRequest);
        $purchaseOrder = $this->createAndIssuePurchaseOrder($purchaseRequest, unitCost: 14_500_000);
        $orderItem = $purchaseOrder->items()->firstOrFail();

        $this->postGoodsReceipt($purchaseOrder, $orderItem->id, 2);

        $this->assertSame(PurchaseOrderStatus::Received, $purchaseOrder->fresh()->status);
        $this->assertSame(PurchaseRequestStatus::Closed, $purchaseRequest->fresh()->status);
        $this->assertEquals(
            2.0,
            (float) InventoryStock::query()
                ->where('warehouse_id', $this->procurementWarehouse->id)
                ->where('item_id', $this->procurementItem->id)
                ->value('quantity')
        );
        $this->assertDatabaseHas('inventory_movements', [
            'item_id' => $this->procurementItem->id,
            'reference_type' => GoodsReceipt::class,
        ]);
    }

    public function test_partial_receipts_close_po_only_after_all_quantity_is_received(): void
    {
        $purchaseRequest = $this->submitPurchaseRequest(quantity: 3);
        $this->approvePurchaseRequest($purchaseRequest);
        $purchaseOrder = $this->createAndIssuePurchaseOrder($purchaseRequest, unitCost: 1_000);
        $orderItem = $purchaseOrder->items()->firstOrFail();

        $this->postGoodsReceipt($purchaseOrder, $orderItem->id, 1);
        $this->assertSame(PurchaseOrderStatus::PartiallyReceived, $purchaseOrder->fresh()->status);
        $this->assertSame(PurchaseRequestStatus::Ordered, $purchaseRequest->fresh()->status);

        $this->postGoodsReceipt($purchaseOrder, $orderItem->id, 2);
        $this->assertSame(PurchaseOrderStatus::Received, $purchaseOrder->fresh()->status);
        $this->assertSame(PurchaseRequestStatus::Closed, $purchaseRequest->fresh()->status);
        $this->assertDatabaseCount('goods_receipts', 2);
    }

    public function test_over_receipt_is_rejected_without_changing_stock(): void
    {
        $purchaseRequest = $this->submitPurchaseRequest(quantity: 1);
        $this->approvePurchaseRequest($purchaseRequest);
        $purchaseOrder = $this->createAndIssuePurchaseOrder($purchaseRequest, unitCost: 1_000);
        $orderItem = $purchaseOrder->items()->firstOrFail();

        $this->actingAs($this->procurementUsers['procurement'])
            ->post(route('procurement.goods-receipts.store', $purchaseOrder), [
                'received_at' => now()->format('Y-m-d H:i:s'),
                'lines' => [[
                    'purchase_order_item_id' => $orderItem->id,
                    'quantity' => 2,
                ]],
            ])
            ->assertSessionHasErrors('lines');

        $this->assertDatabaseCount('goods_receipts', 0);
        $this->assertDatabaseMissing('inventory_stocks', [
            'warehouse_id' => $this->procurementWarehouse->id,
            'item_id' => $this->procurementItem->id,
        ]);
    }

    public function test_goods_receipt_cannot_be_posted_before_purchase_order_issue_time(): void
    {
        $purchaseRequest = $this->submitPurchaseRequest(quantity: 1);
        $this->approvePurchaseRequest($purchaseRequest);
        $purchaseOrder = $this->createAndIssuePurchaseOrder($purchaseRequest, unitCost: 1_000);
        $orderItem = $purchaseOrder->items()->firstOrFail();

        $this->actingAs($this->procurementUsers['procurement'])
            ->post(route('procurement.goods-receipts.store', $purchaseOrder), [
                'received_at' => $purchaseOrder->ordered_at->copy()->subDay()->format('Y-m-d H:i:s'),
                'lines' => [[
                    'purchase_order_item_id' => $orderItem->id,
                    'quantity' => 1,
                ]],
            ])
            ->assertSessionHasErrors('received_at');

        $this->assertDatabaseCount('goods_receipts', 0);
        $this->assertDatabaseMissing('inventory_stocks', [
            'warehouse_id' => $this->procurementWarehouse->id,
            'item_id' => $this->procurementItem->id,
        ]);
    }

    private function postGoodsReceipt(
        PurchaseOrder $purchaseOrder,
        int $orderItemId,
        float $quantity
    ): void {
        $this->actingAs($this->procurementUsers['procurement'])
            ->post(route('procurement.goods-receipts.store', $purchaseOrder), [
                'received_at' => now()->format('Y-m-d H:i:s'),
                'lines' => [[
                    'purchase_order_item_id' => $orderItemId,
                    'quantity' => $quantity,
                ]],
            ])
            ->assertRedirect();
    }
}
