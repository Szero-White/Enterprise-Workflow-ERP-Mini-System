<?php

namespace App\Services\Dashboard;

use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WorkflowRequest;
use Illuminate\Support\Collection;

class DashboardDataService
{
    public function inventorySummary(): array
    {
        return [
            'active_items' => Product::where('is_active', true)->count(),
            'active_warehouses' => Warehouse::where('is_active', true)->count(),
            'stock_positions' => InventoryStock::count(),
            'low_stock' => InventoryStock::query()
                ->join('products', 'products.id', '=', 'inventory_stocks.product_id')
                ->whereColumn('inventory_stocks.quantity', '<=', 'products.reorder_level')
                ->count(),
        ];
    }

    public function lowStockProducts(int $limit = 6): Collection
    {
        return InventoryStock::query()
            ->with(['product', 'warehouse'])
            ->join('products', 'products.id', '=', 'inventory_stocks.product_id')
            ->whereColumn('inventory_stocks.quantity', '<=', 'products.reorder_level')
            ->orderBy('inventory_stocks.quantity')
            ->select('inventory_stocks.*')
            ->limit($limit)
            ->get();
    }

    public function recentInventoryMovements(int $limit = 6): Collection
    {
        return InventoryMovement::with(['product', 'warehouse', 'creator'])
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
