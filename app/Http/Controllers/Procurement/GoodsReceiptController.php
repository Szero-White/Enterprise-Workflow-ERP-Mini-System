<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\GoodsReceiptStoreRequest;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Services\Procurement\GoodsReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GoodsReceiptController extends Controller
{
    public function __construct(private GoodsReceiptService $goodsReceiptService)
    {
    }

    public function index(): View
    {
        return view('procurement.goods-receipts.index', [
            'goodsReceipts' => GoodsReceipt::with([
                'purchaseOrder.supplier',
                'warehouse',
                'receiver',
            ])->latest()->paginate(15),
        ]);
    }

    public function create(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'warehouse', 'items']);

        abort_unless(
            in_array(
                $purchaseOrder->status,
                [PurchaseOrderStatus::Issued, PurchaseOrderStatus::PartiallyReceived],
                true
            ),
            422,
            __('procurement.messages.purchase_order_not_receivable')
        );

        return view('procurement.goods-receipts.create', compact('purchaseOrder'));
    }

    public function store(
        GoodsReceiptStoreRequest $request,
        PurchaseOrder $purchaseOrder
    ): RedirectResponse {
        $receipt = $this->goodsReceiptService->receive(
            $request->user(),
            $purchaseOrder,
            $request->validated()
        );

        return redirect()
            ->route('procurement.goods-receipts.show', $receipt)
            ->with('success', __('procurement.messages.goods_receipt_created'));
    }

    public function show(GoodsReceipt $goodsReceipt): View
    {
        $goodsReceipt->load([
            'purchaseOrder.supplier',
            'warehouse',
            'receiver',
            'items.item',
        ]);

        return view('procurement.goods-receipts.show', compact('goodsReceipt'));
    }
}
