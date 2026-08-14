<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryStockController extends Controller
{
    public function index(Request $request): View
    {
        $query = InventoryStock::query()->with(['item.category', 'warehouse']);

        if ($warehouseId = $request->integer('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($search = trim((string) $request->input('q'))) {
            $query->whereHas('item', fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%"));
        }

        if ($request->boolean('low_stock')) {
            $query->whereHas('item', fn ($builder) => $builder
                ->whereColumn('inventory_stocks.quantity', '<=', 'items.reorder_level'));
        }

        return view('inventory.stocks.index', [
            'stocks' => $query->orderBy('warehouse_id')->orderBy('item_id')->paginate(15)->withQueryString(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'recentMovements' => InventoryMovement::with(['item', 'warehouse', 'creator'])->latest()->limit(8)->get(),
        ]);
    }
}
