<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemRequest;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
    {
        $query = Item::query()->with('category')->latest('id');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%"));
        }

        if ($categoryId = $request->integer('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        return view('inventory.items.index', [
            'items' => $query->paginate(12)->withQueryString(),
            'categories' => ItemCategory::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('inventory.items.create', [
            'categories' => ItemCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(ItemRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $item = Item::create($data);

        $this->auditLogService->log('item.created', $item, null, $item->toArray());

        return redirect()->route('inventory.items.index')->with('success', __('items.messages.item_created'));
    }

    public function edit(Item $item): View
    {
        return view('inventory.items.edit', [
            'item' => $item,
            'categories' => ItemCategory::orderBy('name')->get(),
        ]);
    }

    public function update(ItemRequest $request, Item $item): RedirectResponse
    {
        $old = $item->toArray();
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $item->update($data);

        $this->auditLogService->log('item.updated', $item, $old, $item->fresh()->toArray());

        return redirect()->route('inventory.items.index')->with('success', __('items.messages.item_updated'));
    }

    public function destroy(Item $item): RedirectResponse
    {
        if ($item->inventoryMovements()->exists() || $item->stocks()->where('quantity', '!=', 0)->exists()) {
            return back()->with('error', __('items.messages.item_delete_blocked'));
        }

        $old = $item->toArray();
        $this->auditLogService->log('item.deleted', $item, $old, null);
        $item->delete();

        return back()->with('success', __('items.messages.item_deleted'));
    }
}
