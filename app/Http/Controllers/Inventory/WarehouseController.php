<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\WarehouseRequest;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
    {
        $query = Warehouse::query()->withCount('stocks')->latest('id');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%"));
        }

        return view('inventory.warehouses.index', ['warehouses' => $query->paginate(12)->withQueryString()]);
    }

    public function create(): View
    {
        return view('inventory.warehouses.create');
    }

    public function store(WarehouseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $warehouse = Warehouse::create($data);
        $this->auditLogService->log('warehouse.created', $warehouse, null, $warehouse->toArray());

        return redirect()->route('inventory.warehouses.index')->with('success', __('inventory.messages.warehouse_created'));
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('inventory.warehouses.edit', compact('warehouse'));
    }

    public function update(WarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $old = $warehouse->toArray();
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $warehouse->update($data);
        $this->auditLogService->log('warehouse.updated', $warehouse, $old, $warehouse->fresh()->toArray());

        return redirect()->route('inventory.warehouses.index')->with('success', __('inventory.messages.warehouse_updated'));
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        if (
            $warehouse->inventoryMovements()->exists()
            || $warehouse->purchaseOrders()->exists()
            || $warehouse->goodsReceipts()->exists()
            || $warehouse->assets()->exists()
            || $warehouse->assetAssignments()->exists()
            || $warehouse->assetReturns()->exists()
            || $warehouse->stocks()->where('quantity', '!=', 0)->exists()
        ) {
            return back()->with('error', __('inventory.messages.warehouse_delete_blocked'));
        }

        $old = $warehouse->toArray();
        $this->auditLogService->log('warehouse.deleted', $warehouse, $old, null);
        $warehouse->delete();

        return back()->with('success', __('inventory.messages.warehouse_deleted'));
    }
}
