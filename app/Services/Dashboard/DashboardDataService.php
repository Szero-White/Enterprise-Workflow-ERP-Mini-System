<?php

namespace App\Services\Dashboard;

use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\WorkflowRequest;
use Illuminate\Support\Collection;

class DashboardDataService
{
    public function inventorySummary(): array
    {
        return [
            'active_items' => Item::where('is_active', true)->count(),
            'active_warehouses' => Warehouse::where('is_active', true)->count(),
            'stock_positions' => InventoryStock::count(),
            'low_stock' => InventoryStock::query()
                ->join('items', 'items.id', '=', 'inventory_stocks.item_id')
                ->whereColumn('inventory_stocks.quantity', '<=', 'items.reorder_level')
                ->count(),
        ];
    }

    public function lowStockItems(int $limit = 6): Collection
    {
        return InventoryStock::query()
            ->with(['item', 'warehouse'])
            ->join('items', 'items.id', '=', 'inventory_stocks.item_id')
            ->whereColumn('inventory_stocks.quantity', '<=', 'items.reorder_level')
            ->orderBy('inventory_stocks.quantity')
            ->select('inventory_stocks.*')
            ->limit($limit)
            ->get();
    }

    public function recentInventoryMovements(int $limit = 6): Collection
    {
        return InventoryMovement::with(['item', 'warehouse', 'creator'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function latestWorkflowRequests(int $limit = 6): Collection
    {
        return WorkflowRequest::with(['formTemplate', 'creator', 'currentStep'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
