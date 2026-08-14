<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\InventoryStock;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalApiV1Test extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Item $item;
    private Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $department = Department::create(['name' => 'Admin', 'code' => 'ADMIN']);
        $role = Role::create(['name' => 'Admin', 'key' => 'admin']);
        $this->admin = User::factory()->create([
            'department_id' => $department->id,
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $category = ItemCategory::create([
            'name' => 'Thiết bị IT',
            'code' => 'IT',
            'is_active' => true,
        ]);

        $this->item = Item::create([
            'category_id' => $category->id,
            'sku' => 'API-ITEM-001',
            'name' => 'API Inventory Item',
            'unit' => 'cái',
            'cost_price' => 100000,
            'reorder_level' => 3,
            'is_active' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'code' => 'WH-API',
            'name' => 'API Warehouse',
            'is_active' => true,
        ]);

        InventoryStock::create([
            'warehouse_id' => $this->warehouse->id,
            'item_id' => $this->item->id,
            'quantity' => 2,
        ]);
    }

    public function test_guest_cannot_access_internal_api(): void
    {
        $this->getJson(route('internal-api.v1.items.index'))
            ->assertUnauthorized();
    }

    public function test_item_endpoint_returns_versioned_resource_shape(): void
    {
        $this->actingAs($this->admin)
            ->getJson(route('internal-api.v1.items.index'))
            ->assertOk()
            ->assertJsonPath('data.0.sku', 'API-ITEM-001')
            ->assertJsonPath('data.0.cost_price', 100000)
            ->assertJsonMissingPath('data.0.sale_price');
    }

    public function test_inventory_endpoint_can_filter_low_stock(): void
    {
        $this->actingAs($this->admin)
            ->getJson(route('internal-api.v1.inventory-stocks.index', ['low_stock' => 1]))
            ->assertOk()
            ->assertJsonPath('data.0.item.sku', 'API-ITEM-001')
            ->assertJsonPath('data.0.is_low_stock', true);
    }
}
