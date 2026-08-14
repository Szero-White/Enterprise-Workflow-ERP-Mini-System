<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\PurchaseRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseOrderStoreRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\Procurement\PurchaseOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct(private PurchaseOrderService $purchaseOrderService)
    {
    }

    public function index(Request $request): View
    {
        $query = PurchaseOrder::query()
            ->with(['supplier', 'warehouse', 'purchaseRequest.workflowRequest'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        return view('procurement.purchase-orders.index', [
            'purchaseOrders' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function create(PurchaseRequest $purchaseRequest): View
    {
        $purchaseRequest->load(['workflowRequest', 'items.item', 'activePurchaseOrder']);

        abort_unless(
            $purchaseRequest->status === PurchaseRequestStatus::Approved
                && ! $purchaseRequest->activePurchaseOrder,
            422,
            __('procurement.messages.purchase_request_not_ready')
        );

        return view('procurement.purchase-orders.create', [
            'purchaseRequest' => $purchaseRequest,
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(
        PurchaseOrderStoreRequest $request,
        PurchaseRequest $purchaseRequest
    ): RedirectResponse {
        $data = $request->validated();

        $order = $this->purchaseOrderService->createDraft(
            $request->user(),
            $purchaseRequest,
            Supplier::findOrFail($data['supplier_id']),
            Warehouse::findOrFail($data['warehouse_id']),
            $data
        );

        return redirect()
            ->route('procurement.purchase-orders.show', $order)
            ->with('success', __('procurement.messages.purchase_order_created'));
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load([
            'purchaseRequest.workflowRequest',
            'supplier',
            'warehouse',
            'creator',
            'items',
            'goodsReceipts',
        ]);

        return view('procurement.purchase-orders.show', compact('purchaseOrder'));
    }

    public function issue(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->purchaseOrderService->issue($purchaseOrder);

        return back()->with('success', __('procurement.messages.purchase_order_issued'));
    }

    public function cancel(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->purchaseOrderService->cancel($purchaseOrder);

        return back()->with('success', __('procurement.messages.purchase_order_cancelled'));
    }
}
