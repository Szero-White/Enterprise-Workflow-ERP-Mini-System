<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseRequestStockFulfillmentRequest;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\User;
use App\Services\Procurement\PurchaseRequestStockFulfillmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PurchaseRequestStockFulfillmentController extends Controller
{
    public function __construct(private PurchaseRequestStockFulfillmentService $stockFulfillmentService) {}

    public function show(PurchaseRequest $purchaseRequest, PurchaseRequestItem $line): View
    {
        Gate::authorize('fulfillFromStock', $purchaseRequest);
        abort_unless((int) $line->purchase_request_id === (int) $purchaseRequest->id, 404);

        $purchaseRequest->load(['workflowRequest.creator']);
        $line->load(['item', 'assetAssignments.asset']);
        $reservedAssets = $this->stockFulfillmentService->reservedAssetsForLine($line);

        return view('procurement.purchase-requests.stock-fulfillment', [
            'purchaseRequest' => $purchaseRequest,
            'line' => $line,
            'reservedAssets' => $reservedAssets,
            'remainingQuantity' => $this->stockFulfillmentService->remainingQuantity($line),
            'assignees' => User::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->orderBy('id')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function store(
        PurchaseRequestStockFulfillmentRequest $request,
        PurchaseRequest $purchaseRequest,
        PurchaseRequestItem $line
    ): RedirectResponse {
        abort_unless((int) $line->purchase_request_id === (int) $purchaseRequest->id, 404);

        $this->stockFulfillmentService->fulfillLine(
            $request->user(),
            $purchaseRequest,
            $line,
            $request->validated('assignments')
        );

        return redirect()
            ->route('procurement.purchase-requests.show', $purchaseRequest)
            ->with('success', __('procurement.messages.stock_fulfillment_completed'));
    }
}
