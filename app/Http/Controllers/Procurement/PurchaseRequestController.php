<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseRequestStoreRequest;
use App\Models\Item;
use App\Models\PurchaseRequest;
use App\Models\WorkflowRequest;
use App\Services\Procurement\PurchaseRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseRequestController extends Controller
{
    public function __construct(private PurchaseRequestService $purchaseRequestService)
    {
    }

    public function index(Request $request): View
    {
        $query = PurchaseRequest::query()
            ->with(['workflowRequest.creator', 'items'])
            ->latest();

        if (! $request->user()->hasRole(['admin', 'procurement', 'finance', 'director', 'manager'])) {
            $query->whereHas(
                'workflowRequest',
                fn ($builder) => $builder->where('created_by', $request->user()->id)
            );
        }

        if ($search = trim((string) $request->input('q'))) {
            $query->whereHas(
                'workflowRequest',
                fn ($builder) => $builder->where('request_code', 'like', "%{$search}%")
            );
        }

        return view('procurement.purchase-requests.index', [
            'purchaseRequests' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('procurement.purchase-requests.create', [
            'items' => Item::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(PurchaseRequestStoreRequest $request): RedirectResponse
    {
        $purchaseRequest = $this->purchaseRequestService->create(
            $request->user(),
            $request->validated(),
            $request
        );

        return redirect()
            ->route('procurement.purchase-requests.show', $purchaseRequest)
            ->with('success', __('procurement.messages.purchase_request_created'));
    }

    public function show(Request $request, PurchaseRequest $purchaseRequest): View
    {
        $purchaseRequest->load([
            'workflowRequest.creator',
            'workflowRequest.currentStep',
            'workflowRequest.histories.actor',
            'items.item',
            'activePurchaseOrder',
        ]);

        $this->authorizeView($request, $purchaseRequest);

        return view('procurement.purchase-requests.show', compact('purchaseRequest'));
    }

    public function edit(Request $request, PurchaseRequest $purchaseRequest): View
    {
        $purchaseRequest->load(['workflowRequest', 'items.item']);
        $canEdit = $purchaseRequest->workflowRequest->created_by === $request->user()->id;

        abort_unless($canEdit, 403);
        abort_unless($purchaseRequest->workflowRequest->status === WorkflowRequest::STATUS_RETURNED, 403);

        return view('procurement.purchase-requests.edit', [
            'purchaseRequest' => $purchaseRequest,
            'items' => Item::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(
        PurchaseRequestStoreRequest $request,
        PurchaseRequest $purchaseRequest
    ): RedirectResponse {
        $this->purchaseRequestService->updateReturned(
            $request->user(),
            $purchaseRequest->load('workflowRequest'),
            $request->validated(),
            $request
        );

        return redirect()
            ->route('procurement.purchase-requests.show', $purchaseRequest)
            ->with('success', __('procurement.messages.purchase_request_resubmitted'));
    }

    private function authorizeView(Request $request, PurchaseRequest $purchaseRequest): void
    {
        $canView = $purchaseRequest->workflowRequest->created_by === $request->user()->id
            || $request->user()->hasRole(['admin', 'manager', 'procurement', 'finance', 'director']);

        abort_unless($canView, 403);
    }
}
