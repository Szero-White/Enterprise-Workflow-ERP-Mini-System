<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseRequestStoreRequest;
use App\Http\Resources\Api\V1\PurchaseRequestResource;
use App\Models\PurchaseRequest;
use App\Services\Procurement\PurchaseRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class PurchaseRequestController extends Controller
{
    public function __construct(private PurchaseRequestService $purchaseRequestService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', PurchaseRequest::class);

        $purchaseRequests = PurchaseRequest::query()
            ->with(['workflowRequest.creator', 'items'])
            ->visibleTo($request->user())
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest()
            ->paginate(min(max($request->integer('per_page', 15), 1), 100));

        return PurchaseRequestResource::collection($purchaseRequests);
    }

    public function show(PurchaseRequest $purchaseRequest): PurchaseRequestResource
    {
        $purchaseRequest->load(['workflowRequest.creator', 'workflowRequest.currentStep', 'items']);
        Gate::authorize('view', $purchaseRequest);

        return new PurchaseRequestResource($purchaseRequest);
    }

    public function store(PurchaseRequestStoreRequest $request): JsonResponse
    {
        $purchaseRequest = $this->purchaseRequestService->create(
            $request->user(),
            $request->validated(),
            $request
        );

        return (new PurchaseRequestResource($purchaseRequest->load(['workflowRequest.creator', 'items'])))
            ->response()
            ->setStatusCode(201);
    }
}
