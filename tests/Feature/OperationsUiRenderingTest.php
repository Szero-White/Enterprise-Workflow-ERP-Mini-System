<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationsUiRenderingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $department = Department::create([
            'name' => 'Operations',
            'code' => 'OPS',
        ]);

        $role = Role::create([
            'name' => 'Admin',
            'key' => 'admin',
        ]);

        $this->admin = User::factory()->create([
            'department_id' => $department->id,
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_operations_workspace_pages_render_successfully(): void
    {
        $routes = [
            route('dashboard'),
            route('inventory.items.index'),
            route('inventory.items.create'),
            route('inventory.item-categories.index'),
            route('inventory.item-categories.create'),
            route('inventory.stocks.index'),
            route('inventory.receipts.create'),
            route('inventory.warehouses.index'),
            route('inventory.warehouses.create'),
            route('procurement.purchase-requests.index'),
            route('procurement.purchase-requests.create'),
            route('procurement.suppliers.index'),
            route('procurement.suppliers.create'),
            route('procurement.purchase-orders.index'),
            route('procurement.goods-receipts.index'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($this->admin)
                ->get($url)
                ->assertOk();
        }
    }
}
