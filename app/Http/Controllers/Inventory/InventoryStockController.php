<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryStockController extends Controller
{
    public function index(Request $request): View
    {
        $contextQuery = $this->applyContextFilters(InventoryStock::query(), $request);

        $stockSummary = [
            'healthy' => (clone $contextQuery)->healthy()->count(),
            'low' => (clone $contextQuery)->lowStock()->count(),
        ];

        $stocksQuery = (clone $contextQuery)->with(['item.category', 'warehouse']);

        if ($request->boolean('low_stock')) {
            $stocksQuery->lowStock();
        }

        return view('inventory.stocks.index', [
            'stocks' => $stocksQuery->latestActivity()->paginate(15)->withQueryString(),
            'stockSummary' => $stockSummary,
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'recentMovements' => InventoryMovement::with(['item', 'warehouse', 'creator'])->latest('id')->limit(8)->get(),
        ]);
    }

    private function applyContextFilters(Builder $query, Request $request): Builder
    {
        if ($warehouseId = $request->integer('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($search = trim((string) $request->input('q'))) {
            $query->whereHas('item', fn (Builder $builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%"));
        }

        return $query;
    }
}
