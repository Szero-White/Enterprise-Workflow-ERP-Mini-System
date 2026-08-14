<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCategoryRequest;
use App\Models\ProductCategory;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
    {
        $query = ProductCategory::query()->withCount('products')->latest('id');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%"));
        }

        return view('catalog.categories.index', ['categories' => $query->paginate(12)->withQueryString()]);
    }

    public function create(): View
    {
        return view('catalog.categories.create');
    }

    public function store(ProductCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $category = ProductCategory::create($data);
        $this->auditLogService->log('product_category.created', $category, null, $category->toArray());

        return redirect()->route('catalog.categories.index')->with('success', __('catalog.messages.category_created'));
    }

    public function edit(ProductCategory $category): View
    {
        return view('catalog.categories.edit', compact('category'));
    }

    public function update(ProductCategoryRequest $request, ProductCategory $category): RedirectResponse
    {
        $old = $category->toArray();
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $category->update($data);
        $this->auditLogService->log('product_category.updated', $category, $old, $category->fresh()->toArray());

        return redirect()->route('catalog.categories.index')->with('success', __('catalog.messages.category_updated'));
    }

    public function destroy(ProductCategory $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', __('catalog.messages.category_delete_blocked'));
        }

        $old = $category->toArray();
        $this->auditLogService->log('product_category.deleted', $category, $old, null);
        $category->delete();

        return back()->with('success', __('catalog.messages.category_deleted'));
    }
}
