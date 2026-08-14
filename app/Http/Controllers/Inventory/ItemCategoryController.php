<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemCategoryRequest;
use App\Models\ItemCategory;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemCategoryController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
    {
        $query = ItemCategory::query()->withCount('items')->latest('id');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%"));
        }

        return view('inventory.item-categories.index', [
            'categories' => $query->paginate(12)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('inventory.item-categories.create');
    }

    public function store(ItemCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $category = ItemCategory::create($data);

        $this->auditLogService->log('item_category.created', $category, null, $category->toArray());

        return redirect()->route('inventory.item-categories.index')
            ->with('success', __('items.messages.category_created'));
    }

    public function edit(ItemCategory $itemCategory): View
    {
        return view('inventory.item-categories.edit', ['category' => $itemCategory]);
    }

    public function update(ItemCategoryRequest $request, ItemCategory $itemCategory): RedirectResponse
    {
        $old = $itemCategory->toArray();
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $itemCategory->update($data);

        $this->auditLogService->log('item_category.updated', $itemCategory, $old, $itemCategory->fresh()->toArray());

        return redirect()->route('inventory.item-categories.index')
            ->with('success', __('items.messages.category_updated'));
    }

    public function destroy(ItemCategory $itemCategory): RedirectResponse
    {
        if ($itemCategory->items()->exists()) {
            return back()->with('error', __('items.messages.category_delete_blocked'));
        }

        $old = $itemCategory->toArray();
        $this->auditLogService->log('item_category.deleted', $itemCategory, $old, null);
        $itemCategory->delete();

        return back()->with('success', __('items.messages.category_deleted'));
    }
}
