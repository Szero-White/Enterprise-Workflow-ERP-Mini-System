<?php

namespace Tests\Feature;

use App\Enums\InventoryMovementType;
use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\Department;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesInventoryFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employee;
    private Product $product;
    private Customer $customer;
    private Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $department = Department::create(['name' => 'Sales', 'code' => 'SALES']);
        $adminRole = Role::create(['name' => 'Admin', 'key' => 'admin']);
        $employeeRole = Role::create(['name' => 'Employee', 'key' => 'employee']);

        $this->admin = User::factory()->create([
            'department_id' => $department->id,
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);
        $this->employee = User::factory()->create([
            'department_id' => $department->id,
            'role_id' => $employeeRole->id,
            'is_active' => true,
        ]);

        $category = ProductCategory::create(['name' => 'Laptop', 'code' => 'LAPTOP', 'is_active' => true]);
        $this->product = Product::create([
            'category_id' => $category->id,
            'sku' => 'SKU-001',
            'name' => 'Business Laptop',
            'unit' => 'cái',
            'cost_price' => 10000000,
            'sale_price' => 15000000,
            'reorder_level' => 2,
            'is_active' => true,
        ]);
        $this->customer = Customer::create(['code' => 'CUS-001', 'name' => 'Customer A', 'is_active' => true]);
        $this->warehouse = Warehouse::create(['code' => 'WH-01', 'name' => 'Main Warehouse', 'is_active' => true]);
        InventoryStock::create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
        ]);
    }

    public function test_admin_can_create_draft_order_then_confirm_and_stock_is_deducted(): void
    {
        $response = $this->actingAs($this->admin)->post(route('sales.orders.store'), [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'discount_amount' => 500000,
            'items' => [[
                'product_id' => $this->product->id,
                'quantity' => 2,
            ]],
        ]);

        $order = SalesOrder::firstOrFail();
        $response->assertRedirect(route('sales.orders.show', $order));
        $this->assertSame(SalesOrderStatus::Draft, $order->status);
        $this->assertSame('29500000.00', $order->total_amount);

        $this->actingAs($this->admin)
            ->post(route('sales.orders.confirm', $order))
            ->assertRedirect(route('sales.orders.show', $order));

        $this->assertSame(SalesOrderStatus::Confirmed, $order->fresh()->status);
        $this->assertSame('8.000', InventoryStock::firstOrFail()->quantity);
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => InventoryMovementType::Sale->value,
            'quantity' => -2,
        ]);
    }

    public function test_confirm_is_rejected_when_stock_is_insufficient_without_partial_deduction(): void
    {
        $order = $this->createDraftOrder(11);

        $response = $this->actingAs($this->admin)->post(route('sales.orders.confirm', $order));

        $response->assertSessionHasErrors('items');
        $this->assertSame(SalesOrderStatus::Draft, $order->fresh()->status);
        $this->assertSame('10.000', InventoryStock::firstOrFail()->quantity);
        $this->assertDatabaseMissing('inventory_movements', ['type' => InventoryMovementType::Sale->value]);
    }

    public function test_cancelling_confirmed_order_restores_stock(): void
    {
        $order = $this->createDraftOrder(3);
        $this->actingAs($this->admin)->post(route('sales.orders.confirm', $order))->assertRedirect();
        $this->assertSame('7.000', InventoryStock::firstOrFail()->quantity);

        $this->actingAs($this->admin)->post(route('sales.orders.cancel', $order))->assertRedirect();

        $this->assertSame(SalesOrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame('10.000', InventoryStock::firstOrFail()->quantity);
        $this->assertDatabaseHas('inventory_movements', [
            'type' => InventoryMovementType::SaleCancellation->value,
            'quantity' => 3,
        ]);
    }

    public function test_employee_cannot_access_sales_management(): void
    {
        $this->actingAs($this->employee)
            ->get(route('sales.orders.index'))
            ->assertForbidden();
    }

    public function test_catalog_price_is_recalculated_on_server_even_if_client_sends_another_price(): void
    {
        $this->actingAs($this->admin)->post(route('sales.orders.store'), [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'discount_amount' => 0,
            'items' => [[
                'product_id' => $this->product->id,
                'quantity' => 1,
                'unit_price' => 1,
            ]],
        ])->assertRedirect();

        $order = SalesOrder::with('items')->firstOrFail();
        $this->assertSame('15000000.00', $order->items->first()->unit_price);
        $this->assertSame('15000000.00', $order->total_amount);
    }

    private function createDraftOrder(float $quantity): SalesOrder
    {
        $this->actingAs($this->admin)->post(route('sales.orders.store'), [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'discount_amount' => 0,
            'items' => [[
                'product_id' => $this->product->id,
                'quantity' => $quantity,
            ]],
        ])->assertRedirect();

        return SalesOrder::latest('id')->firstOrFail();
    }
}
