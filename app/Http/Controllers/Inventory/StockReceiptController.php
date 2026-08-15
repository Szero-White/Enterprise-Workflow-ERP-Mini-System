<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockReceiptRequest;
use App\Models\Item;
use App\Models\Warehouse;
use App\Services\Inventory\InventoryStockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StockReceiptController extends Controller
{
    public function __construct(private InventoryStockService $inventoryStockService) {}

    public function create(): View
    {
        return view('inventory.receipts.create', [
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'items' => Item::where('is_active', true)->where('is_asset_trackable', false)->orderBy('name')->get(),
        ]);
    }

    public function store(StockReceiptRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $warehouse = Warehouse::findOrFail($data['warehouse_id']);
        $item = Item::findOrFail($data['item_id']);

        $this->inventoryStockService->receive(
            $request->user(),
            $warehouse,
            $item,
            (float) $data['quantity'],
            isset($data['unit_cost']) ? (int) $data['unit_cost'] : null,
            $data['note'] ?? null,
        );

        return redirect()->route('inventory.stocks.index')
            ->with('success', __('inventory.messages.receipt_recorded'));
    }
}
