<?php

namespace Tests\Feature\Inventory;

use App\Models\InventoryStock;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsProcurementFixture;
use Tests\TestCase;

class InventoryStockListingTest extends TestCase
{
    use BuildsProcurementFixture;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProcurementFixture();
    }

    public function test_stock_page_prioritizes_recent_activity_and_shows_health_summary(): void
    {
        $olderStock = InventoryStock::create([
            'warehouse_id' => $this->procurementWarehouse->id,
            'item_id' => $this->procurementItem->id,
            'quantity' => 10,
        ]);
        $olderStock->forceFill([
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ])->saveQuietly();

        $lowStockItem = Item::create([
            'category_id' => $this->procurementItem->category_id,
            'sku' => 'MON-LOW-01',
            'name' => 'Low Stock Monitor',
            'unit' => 'cái',
            'cost_price' => 2_000_000,
            'reorder_level' => 5,
            'is_asset_trackable' => false,
            'is_active' => true,
        ]);

        $newerCreatedStock = InventoryStock::create([
            'warehouse_id' => $this->procurementWarehouse->id,
            'item_id' => $lowStockItem->id,
            'quantity' => 2,
        ]);
        $newerCreatedStock->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->saveQuietly();

        $olderStock->forceFill([
            'quantity' => 17,
            'updated_at' => now(),
        ])->saveQuietly();

        $response = $this->actingAs($this->procurementUsers['admin'])
            ->get(route('inventory.stocks.index'));

        $response
            ->assertOk()
            ->assertSeeInOrder([$this->procurementItem->sku, $lowStockItem->sku])
            ->assertSeeText('Ổn định: 1')
            ->assertSeeText('Cần nhập thêm: 1');
    }

    public function test_stock_summary_respects_context_filters_without_being_collapsed_by_low_stock_toggle(): void
    {
        InventoryStock::create([
            'warehouse_id' => $this->procurementWarehouse->id,
            'item_id' => $this->procurementItem->id,
            'quantity' => 10,
        ]);

        $response = $this->actingAs($this->procurementUsers['admin'])
            ->get(route('inventory.stocks.index', [
                'warehouse_id' => $this->procurementWarehouse->id,
                'low_stock' => 1,
            ]));

        $response
            ->assertOk()
            ->assertSeeText('Ổn định: 1')
            ->assertSeeText('Cần nhập thêm: 0');
    }
}
