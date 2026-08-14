<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockReceiptRequest;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\Inventory\InventoryStockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(private InventoryStockService $inventoryStockService)
    {
    }

    public function index(Request $request): View
    {
        $query = InventoryStock::query()->with(['product.category', 'warehouse']);

        if ($warehouseId = $request->integer('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($search = trim((string) $request->input('q'))) {
            $query->whereHas('product', fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%"));
        }

        if ($request->boolean('low_stock')) {
            $query->whereHas('product', fn ($builder) => $builder
                ->whereColumn('inventory_stocks.quantity', '<=', 'products.reorder_level'));
        }

        return view('inventory.stocks.index', [
            'stocks' => $query->orderBy('warehouse_id')->orderBy('product_id')->paginate(15)->withQueryString(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'recentMovements' => InventoryMovement::with(['product', 'warehouse', 'creator'])->latest()->limit(8)->get(),
        ]);
    }

    public function createReceipt(): View
    {
        return view('inventory.receipts.create', [
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeReceipt(StockReceiptRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $warehouse = Warehouse::findOrFail($data['warehouse_id']);
        $product = Product::findOrFail($data['product_id']);

        $this->inventoryStockService->receive(
            $request->user(),
            $warehouse,
            $product,
            (float) $data['quantity'],
            isset($data['unit_cost']) ? (float) $data['unit_cost'] : null,
            $data['note'] ?? null,
        );

        return redirect()->route('inventory.stocks.index')->with('success', __('inventory.messages.receipt_recorded'));
    }
}
