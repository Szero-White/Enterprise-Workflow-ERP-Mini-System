<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseRequestStoreRequest;
use App\Models\Item;
use App\Models\PurchaseRequest;
use App\Services\Procurement\PurchaseRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PurchaseRequestController extends Controller
{
    public function __construct(private PurchaseRequestService $purchaseRequestService)
    {
    }

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', PurchaseRequest::class);

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
        Gate::authorize('create', PurchaseRequest::class);

        return view('procurement.purchase-requests.create', [
            'items' => Item::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(PurchaseRequestStoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', PurchaseRequest::class);

        $purchaseRequest = $this->purchaseRequestService->create(
            $request->user(),
            $request->validated(),
            $request
        );

        return redirect()
            ->route('procurement.purchase-requests.show', $purchaseRequest)
            ->with('success', __('procurement.messages.purchase_request_created'));
    }

    public function show(PurchaseRequest $purchaseRequest): View
    {
        $purchaseRequest->load([
            'workflowRequest.creator',
            'workflowRequest.currentStep',
            'workflowRequest.histories.actor',
            'items.item',
            'activePurchaseOrder',
        ]);

        Gate::authorize('view', $purchaseRequest);

        return view('procurement.purchase-requests.show', compact('purchaseRequest'));
    }

    public function edit(PurchaseRequest $purchaseRequest): View
    {
        $purchaseRequest->load(['workflowRequest', 'items.item']);
        Gate::authorize('update', $purchaseRequest);

        return view('procurement.purchase-requests.edit', [
            'purchaseRequest' => $purchaseRequest,
            'items' => Item::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(
        PurchaseRequestStoreRequest $request,
        PurchaseRequest $purchaseRequest
    ): RedirectResponse {
        Gate::authorize('update', $purchaseRequest);

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
}