<?php

namespace Tests\Feature;

use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\Department;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Role;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalApiV1Test extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Product $product;
    private Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $department = Department::create(['name' => 'Operations', 'code' => 'OPS']);
        $role = Role::create(['name' => 'Admin', 'key' => 'admin']);
        $this->admin = User::factory()->create([
            'department_id' => $department->id,
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'sku' => 'API-SKU-01',
            'name' => 'API Product',
            'unit' => 'cái',
            'cost_price' => 100000,
            'sale_price' => 150000,
            'reorder_level' => 5,
            'is_active' => true,
        ]);
        $this->warehouse = Warehouse::create([
            'code' => 'API-WH',
            'name' => 'API Warehouse',
            'is_active' => true,
        ]);
        InventoryStock::create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);
    }

    public function test_guest_cannot_access_internal_api(): void
    {
        $this->getJson(route('internal-api.v1.products.index'))
            ->assertUnauthorized();
    }

    public function test_product_endpoint_returns_versioned_resource_shape(): void
    {
        $this->actingAs($this->admin)
            ->getJson(route('internal-api.v1.products.index'))
            ->assertOk()
            ->assertJsonPath('data.0.sku', 'API-SKU-01')
            ->assertJsonPath('data.0.sale_price', 150000);
    }

    public function test_inventory_endpoint_can_filter_low_stock(): void
    {
        $this->actingAs($this->admin)
            ->getJson(route('internal-api.v1.inventory-stocks.index', ['low_stock' => 1]))
            ->assertOk()
            ->assertJsonPath('data.0.is_low_stock', true)
            ->assertJsonPath('data.0.warehouse.code', 'API-WH');
    }

    public function test_sales_order_show_uses_product_snapshot_fields(): void
    {
        $customer = Customer::create(['code' => 'API-CUS', 'name' => 'API Customer', 'is_active' => true]);
        $order = SalesOrder::create([
            'order_code' => 'SO-API-001',
            'customer_id' => $customer->id,
            'warehouse_id' => $this->warehouse->id,
            'created_by' => $this->admin->id,
            'status' => SalesOrderStatus::Draft,
            'order_date' => now()->toDateString(),
            'subtotal' => 150000,
            'discount_amount' => 0,
            'total_amount' => 150000,
        ]);
        $order->items()->create([
            'product_id' => $this->product->id,
            'product_sku' => 'API-SKU-01',
            'product_name' => 'API Product',
            'unit' => 'cái',
            'quantity' => 1,
            'unit_price' => 150000,
            'line_total' => 150000,
        ]);

        $this->product->update(['name' => 'Renamed Product']);

        $this->actingAs($this->admin)
            ->getJson(route('internal-api.v1.sales-orders.show', $order))
            ->assertOk()
            ->assertJsonPath('data.items.0.name', 'API Product');
    }
}
