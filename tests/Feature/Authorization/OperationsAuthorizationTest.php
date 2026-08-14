<?php

namespace Tests\Feature\Authorization;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\Support\BuildsProcurementFixture;
use Tests\TestCase;

class OperationsAuthorizationTest extends TestCase
{
    use RefreshDatabase;
    use BuildsProcurementFixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProcurementFixture();
    }

    public function test_employee_cannot_view_another_employees_purchase_request(): void
    {
        $otherEmployee = $this->createPeerEmployee();
        $purchaseRequest = $this->submitPurchaseRequestAs($otherEmployee);

        $this->actingAs($this->procurementUsers['employee'])
            ->get(route('procurement.purchase-requests.show', $purchaseRequest))
            ->assertForbidden();
    }

    public function test_dashboard_only_surfaces_workflow_requests_visible_to_current_user(): void
    {
        $otherEmployee = $this->createPeerEmployee();
        $purchaseRequest = $this->submitPurchaseRequestAs($otherEmployee);
        $requestCode = $purchaseRequest->workflowRequest->request_code;

        $this->actingAs($this->procurementUsers['employee'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee($requestCode);

        $this->actingAs($this->procurementUsers['manager'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($requestCode);
    }

    public function test_inventory_access_matches_operations_roles(): void
    {
        $this->actingAs($this->procurementUsers['employee'])
            ->get(route('inventory.items.index'))
            ->assertForbidden();

        $this->actingAs($this->procurementUsers['procurement'])
            ->get(route('inventory.items.index'))
            ->assertOk();

        $this->actingAs($this->procurementUsers['asset_manager'])
            ->get(route('inventory.stocks.index'))
            ->assertOk();
    }

    public function test_asset_policy_separates_visibility_from_lifecycle_management(): void
    {
        $asset = Asset::create([
            'asset_code' => 'AST-AUTH-001',
            'item_id' => $this->procurementItem->id,
            'warehouse_id' => $this->procurementWarehouse->id,
            'acquired_at' => now()->toDateString(),
            'acquisition_cost' => 1000,
            'status' => AssetStatus::Available,
            'condition' => AssetCondition::New,
        ]);

        $this->assertTrue(Gate::forUser($this->procurementUsers['procurement'])->allows('view', $asset));
        $this->assertTrue(Gate::forUser($this->procurementUsers['procurement'])->denies('assign', $asset));
        $this->assertTrue(Gate::forUser($this->procurementUsers['asset_manager'])->allows('assign', $asset));
    }

    private function createPeerEmployee(): User
    {
        $employee = $this->procurementUsers['employee'];

        return User::factory()->create([
            'role_id' => $employee->role_id,
            'department_id' => $employee->department_id,
            'is_active' => true,
        ]);
    }

    private function submitPurchaseRequestAs(User $employee): PurchaseRequest
    {
        $this->actingAs($employee)
            ->post(route('procurement.purchase-requests.store'), [
                'purpose' => 'Yêu cầu riêng của nhân viên khác',
                'required_date' => now()->addWeek()->toDateString(),
                'items' => [[
                    'item_id' => $this->procurementItem->id,
                    'quantity' => 1,
                    'estimated_unit_cost' => 1000,
                    'note' => null,
                ]],
            ])
            ->assertRedirect();

        return PurchaseRequest::query()
            ->whereHas('workflowRequest', fn ($query) => $query->where('created_by', $employee->id))
            ->with('workflowRequest')
            ->firstOrFail();
    }
}
