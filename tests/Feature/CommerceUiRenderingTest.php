<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommerceUiRenderingTest extends TestCase
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

    public function test_admin_commerce_workspace_pages_render_successfully(): void
    {
        $routes = [
            route('dashboard'),
            route('catalog.products.index'),
            route('catalog.products.create'),
            route('catalog.categories.index'),
            route('catalog.categories.create'),
            route('crm.customers.index'),
            route('crm.customers.create'),
            route('inventory.stocks.index'),
            route('inventory.receipts.create'),
            route('inventory.warehouses.index'),
            route('inventory.warehouses.create'),
            route('sales.orders.index'),
            route('sales.orders.create'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($this->admin)
                ->get($url)
                ->assertOk();
        }
    }
}
