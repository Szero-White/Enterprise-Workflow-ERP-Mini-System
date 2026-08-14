<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
    {
        $query = Product::query()->with('category')->latest('id');

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

        return view('catalog.products.index', [
            'products' => $query->paginate(12)->withQueryString(),
            'categories' => ProductCategory::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('catalog.products.create', ['categories' => ProductCategory::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $product = Product::create($data);
        $this->auditLogService->log('product.created', $product, null, $product->toArray());

        return redirect()->route('catalog.products.index')->with('success', __('catalog.messages.product_created'));
    }

    public function edit(Product $product): View
    {
        return view('catalog.products.edit', [
            'product' => $product,
            'categories' => ProductCategory::orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $old = $product->toArray();
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $product->update($data);
        $this->auditLogService->log('product.updated', $product, $old, $product->fresh()->toArray());

        return redirect()->route('catalog.products.index')->with('success', __('catalog.messages.product_updated'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        if (
            $product->salesOrderItems()->exists()
            || $product->inventoryMovements()->exists()
            || $product->stocks()->where('quantity', '!=', 0)->exists()
        ) {
            return back()->with('error', __('catalog.messages.product_delete_blocked'));
        }

        $old = $product->toArray();
        $this->auditLogService->log('product.deleted', $product, $old, null);
        $product->delete();

        return back()->with('success', __('catalog.messages.product_deleted'));
    }
}
