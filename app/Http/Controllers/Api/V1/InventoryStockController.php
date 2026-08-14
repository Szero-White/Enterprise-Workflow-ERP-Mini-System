<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\InventoryStockResource;
use App\Models\InventoryStock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InventoryStockController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $stocks = InventoryStock::query()
            ->with(['warehouse', 'product'])
            ->when($request->integer('warehouse_id'), fn ($query, $warehouseId) => $query->where('warehouse_id', $warehouseId))
            ->when($request->boolean('low_stock'), fn ($query) => $query->whereHas(
                'product',
                fn ($builder) => $builder->whereColumn('inventory_stocks.quantity', '<=', 'products.reorder_level')
            ))
            ->orderBy('warehouse_id')
            ->orderBy('product_id')
            ->paginate($this->perPage($request));

        return InventoryStockResource::collection($stocks);
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 15), 1), 100);
    }
}
