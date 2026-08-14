<?php

namespace Tests\Feature\Inventory;

use App\Services\Inventory\InventoryStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\BuildsProcurementFixture;
use Tests\TestCase;

class InventoryStockServiceTest extends TestCase
{
    use RefreshDatabase;
    use BuildsProcurementFixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProcurementFixture();
    }

    public function test_service_rejects_non_positive_receipt_without_creating_stock_or_movement(): void
    {
        $this->actingAs($this->procurementUsers['admin']);

        try {
            app(InventoryStockService::class)->receive(
                actor: $this->procurementUsers['admin'],
                warehouse: $this->procurementWarehouse,
                item: $this->procurementItem,
                quantity: 0,
            );

            $this->fail('Expected non-positive inventory receipt to be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('quantity', $exception->errors());
        }

        $this->assertDatabaseCount('inventory_stocks', 0);
        $this->assertDatabaseCount('inventory_movements', 0);
    }
}
