<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
    {
        $query = Supplier::query()->latest('id');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('tax_code', 'like', "%{$search}%"));
        }

        return view('procurement.suppliers.index', [
            'suppliers' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('procurement.suppliers.create');
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $supplier = Supplier::create($data);

        $this->auditLogService->log('procurement.supplier.created', $supplier, null, $supplier->toArray());

        return redirect()
            ->route('procurement.suppliers.index')
            ->with('success', __('procurement.messages.supplier_created'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('procurement.suppliers.edit', compact('supplier'));
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $old = $supplier->toArray();
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $supplier->update($data);

        $this->auditLogService->log('procurement.supplier.updated', $supplier, $old, $supplier->fresh()->toArray());

        return redirect()
            ->route('procurement.suppliers.index')
            ->with('success', __('procurement.messages.supplier_updated'));
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->purchaseOrders()->exists()) {
            return back()->with('error', __('procurement.messages.supplier_delete_blocked'));
        }

        $old = $supplier->toArray();
        $this->auditLogService->log('procurement.supplier.deleted', $supplier, $old, null);
        $supplier->delete();

        return back()->with('success', __('procurement.messages.supplier_deleted'));
    }
}
