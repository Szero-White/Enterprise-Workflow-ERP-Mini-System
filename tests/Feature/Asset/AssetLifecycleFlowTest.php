<?php

namespace Tests\Feature\Asset;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Enums\InventoryMovementType;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\InventoryStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsProcurementFixture;
use Tests\TestCase;

class AssetLifecycleFlowTest extends TestCase
{
    use RefreshDatabase;
    use BuildsProcurementFixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProcurementFixture();
    }

    public function test_goods_receipt_registers_one_asset_per_trackable_unit(): void
    {
        $purchaseRequest = $this->submitPurchaseRequest(quantity: 2);
        $this->approvePurchaseRequest($purchaseRequest);
        $purchaseOrder = $this->createAndIssuePurchaseOrder($purchaseRequest, unitCost: 14_500_000);
        $orderItem = $purchaseOrder->items()->firstOrFail();

        $this->actingAs($this->procurementUsers['procurement'])
            ->post(route('procurement.goods-receipts.store', $purchaseOrder), [
                'received_at' => now()->format('Y-m-d H:i:s'),
                'lines' => [[
                    'purchase_order_item_id' => $orderItem->id,
                    'quantity' => 2,
                ]],
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('assets', 2);
        $this->assertSame(2, Asset::query()->where('item_id', $this->procurementItem->id)->count());
        $this->assertTrue(Asset::query()->get()->every(fn (Asset $asset) => $asset->status === AssetStatus::Available));
    }

    public function test_fractional_receipt_is_rejected_for_asset_tracked_item_without_stock_side_effects(): void
    {
        $purchaseRequest = $this->submitPurchaseRequest(quantity: 1.5);
        $this->approvePurchaseRequest($purchaseRequest);
        $purchaseOrder = $this->createAndIssuePurchaseOrder($purchaseRequest, unitCost: 14_500_000);
        $orderItem = $purchaseOrder->items()->firstOrFail();

        $this->actingAs($this->procurementUsers['procurement'])
            ->post(route('procurement.goods-receipts.store', $purchaseOrder), [
                'received_at' => now()->format('Y-m-d H:i:s'),
                'lines' => [[
                    'purchase_order_item_id' => $orderItem->id,
                    'quantity' => 1.5,
                ]],
            ])
            ->assertSessionHasErrors('lines');

        $this->assertDatabaseCount('goods_receipts', 0);
        $this->assertDatabaseCount('assets', 0);
        $this->assertDatabaseMissing('inventory_stocks', [
            'warehouse_id' => $this->procurementWarehouse->id,
            'item_id' => $this->procurementItem->id,
        ]);
    }

    public function test_assignment_and_return_keep_asset_and_inventory_in_sync(): void
    {
        $asset = $this->receiveOneTrackedAsset();

        $this->assertEquals(1.0, $this->stockQuantity());

        $this->actingAs($this->procurementUsers['asset_manager'])
            ->post(route('assets.assignments.store', $asset), [
                'assigned_to' => $this->procurementUsers['employee']->id,
                'assigned_at' => now()->format('Y-m-d H:i:s'),
                'purpose' => 'Trang bị làm việc',
            ])
            ->assertRedirect(route('assets.show', $asset));

        $asset->refresh();
        $this->assertSame(AssetStatus::Assigned, $asset->status);
        $this->assertNull($asset->warehouse_id);
        $this->assertEquals(0.0, $this->stockQuantity());
        $this->assertDatabaseHas('inventory_movements', [
            'item_id' => $this->procurementItem->id,
            'type' => InventoryMovementType::AssetAssignment->value,
            'quantity' => -1,
        ]);

        $assignment = AssetAssignment::query()->where('asset_id', $asset->id)->firstOrFail();

        $this->actingAs($this->procurementUsers['asset_manager'])
            ->post(route('assets.returns.store', $assignment), [
                'warehouse_id' => $this->procurementWarehouse->id,
                'returned_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'condition' => AssetCondition::Good->value,
            ])
            ->assertRedirect(route('assets.show', $asset));

        $asset->refresh();
        $this->assertSame(AssetStatus::Available, $asset->status);
        $this->assertSame(AssetCondition::Good, $asset->condition);
        $this->assertSame($this->procurementWarehouse->id, $asset->warehouse_id);
        $this->assertEquals(1.0, $this->stockQuantity());
        $this->assertDatabaseHas('inventory_movements', [
            'item_id' => $this->procurementItem->id,
            'type' => InventoryMovementType::AssetReturn->value,
            'quantity' => 1,
        ]);
    }

    public function test_asset_cannot_be_assigned_twice_without_return(): void
    {
        $asset = $this->receiveOneTrackedAsset();

        $payload = [
            'assigned_to' => $this->procurementUsers['employee']->id,
            'assigned_at' => now()->format('Y-m-d H:i:s'),
            'purpose' => 'Trang bị làm việc',
        ];

        $this->actingAs($this->procurementUsers['asset_manager'])
            ->post(route('assets.assignments.store', $asset), $payload)
            ->assertRedirect();

        $this->actingAs($this->procurementUsers['asset_manager'])
            ->post(route('assets.assignments.store', $asset->fresh()), $payload)
            ->assertSessionHasErrors('asset');

        $this->assertDatabaseCount('asset_assignments', 1);
        $this->assertEquals(0.0, $this->stockQuantity());
    }

    public function test_return_needing_maintenance_blocks_assignment_until_released(): void
    {
        $asset = $this->receiveOneTrackedAsset();

        $this->actingAs($this->procurementUsers['asset_manager'])
            ->post(route('assets.assignments.store', $asset), [
                'assigned_to' => $this->procurementUsers['employee']->id,
                'assigned_at' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect();

        $assignment = AssetAssignment::query()->firstOrFail();

        $this->actingAs($this->procurementUsers['asset_manager'])
            ->post(route('assets.returns.store', $assignment), [
                'warehouse_id' => $this->procurementWarehouse->id,
                'returned_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'condition' => AssetCondition::NeedsMaintenance->value,
            ])
            ->assertRedirect();

        $this->assertSame(AssetStatus::Maintenance, $asset->fresh()->status);

        $this->actingAs($this->procurementUsers['asset_manager'])
            ->post(route('assets.maintenance.complete', $asset->fresh()))
            ->assertRedirect();

        $this->assertSame(AssetStatus::Available, $asset->fresh()->status);
        $this->assertSame(AssetCondition::Good, $asset->fresh()->condition);
    }

    private function receiveOneTrackedAsset(): Asset
    {
        $purchaseRequest = $this->submitPurchaseRequest(quantity: 1);
        $this->approvePurchaseRequest($purchaseRequest);
        $purchaseOrder = $this->createAndIssuePurchaseOrder($purchaseRequest, unitCost: 14_500_000);
        $orderItem = $purchaseOrder->items()->firstOrFail();

        $this->actingAs($this->procurementUsers['procurement'])
            ->post(route('procurement.goods-receipts.store', $purchaseOrder), [
                'received_at' => now()->format('Y-m-d H:i:s'),
                'lines' => [[
                    'purchase_order_item_id' => $orderItem->id,
                    'quantity' => 1,
                ]],
            ])
            ->assertRedirect();

        return Asset::query()->firstOrFail();
    }

    private function stockQuantity(): float
    {
        return (float) InventoryStock::query()
            ->where('warehouse_id', $this->procurementWarehouse->id)
            ->where('item_id', $this->procurementItem->id)
            ->value('quantity');
    }
}
