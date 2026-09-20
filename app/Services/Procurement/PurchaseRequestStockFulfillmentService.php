<?php

namespace App\Services\Procurement;

use App\Enums\AssetStatus;
use App\Enums\PurchaseRequestFulfillmentRoute;
use App\Enums\PurchaseRequestStatus;
use App\Models\Asset;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\User;
use App\Services\Asset\AssetLifecycleService;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseRequestStockFulfillmentService
{
    public function __construct(
        private AssetLifecycleService $assetLifecycleService,
        private AuditLogService $auditLogService,
        private NotificationService $notificationService,
    ) {}

    public function routeAfterManagerApproval(PurchaseRequest $purchaseRequest): PurchaseRequestFulfillmentRoute
    {
        return DB::transaction(function () use ($purchaseRequest): PurchaseRequestFulfillmentRoute {
            $purchaseRequest = PurchaseRequest::query()
                ->with(['items.item'])
                ->lockForUpdate()
                ->findOrFail($purchaseRequest->id);

            if ($purchaseRequest->fulfillment_route !== PurchaseRequestFulfillmentRoute::Pending) {
                return $purchaseRequest->fulfillment_route;
            }

            $reservations = $this->lockReservableAssets($purchaseRequest);

            if ($reservations === null) {
                $this->updateRoute($purchaseRequest, PurchaseRequestFulfillmentRoute::Procurement);

                return PurchaseRequestFulfillmentRoute::Procurement;
            }

            foreach ($reservations as $lineId => $assets) {
                foreach ($assets as $asset) {
                    $asset->update([
                        'status' => AssetStatus::Reserved,
                        'reserved_for_purchase_request_item_id' => $lineId,
                    ]);
                }
            }

            $this->updateRoute($purchaseRequest, PurchaseRequestFulfillmentRoute::Stock);

            return PurchaseRequestFulfillmentRoute::Stock;
        });
    }

    /**
     * @param  array<int|string, int|string>  $assignmentsByAssetId
     */
    public function fulfillLine(
        User $actor,
        PurchaseRequest $purchaseRequest,
        PurchaseRequestItem $line,
        array $assignmentsByAssetId
    ): PurchaseRequest {
        return DB::transaction(function () use ($actor, $purchaseRequest, $line, $assignmentsByAssetId): PurchaseRequest {
            $purchaseRequest = PurchaseRequest::query()
                ->with(['workflowRequest.creator', 'items.assetAssignments'])
                ->lockForUpdate()
                ->findOrFail($purchaseRequest->id);

            $line = PurchaseRequestItem::query()
                ->with(['item', 'assetAssignments'])
                ->lockForUpdate()
                ->findOrFail($line->id);

            if ((int) $line->purchase_request_id !== (int) $purchaseRequest->id) {
                abort(404);
            }

            if (
                $purchaseRequest->fulfillment_route !== PurchaseRequestFulfillmentRoute::Stock
                || $purchaseRequest->status !== PurchaseRequestStatus::Approved
            ) {
                throw ValidationException::withMessages([
                    'purchase_request' => __('procurement.messages.stock_fulfillment_not_ready'),
                ]);
            }

            $requestedQuantity = $this->wholeQuantity($line);
            $assignedCount = $line->assetAssignments()->count();
            $remaining = $requestedQuantity - $assignedCount;

            if ($remaining <= 0) {
                throw ValidationException::withMessages([
                    'purchase_request' => __('procurement.messages.stock_fulfillment_line_completed'),
                ]);
            }

            $assets = $this->reservedAssetsForLine($line, lockForUpdate: true);

            if ($assets->count() !== $remaining) {
                throw ValidationException::withMessages([
                    'purchase_request' => __('procurement.messages.stock_fulfillment_reservation_mismatch'),
                ]);
            }

            $normalizedAssignments = collect($assignmentsByAssetId)
                ->mapWithKeys(fn ($userId, $assetId): array => [(int) $assetId => (int) $userId]);
            $reservedAssetIds = $assets->pluck('id')->map(fn ($id): int => (int) $id)->sort()->values();
            $submittedAssetIds = $normalizedAssignments->keys()->map(fn ($id): int => (int) $id)->sort()->values();

            if ($reservedAssetIds->all() !== $submittedAssetIds->all()) {
                throw ValidationException::withMessages([
                    'assignments' => __('procurement.messages.stock_fulfillment_assignments_mismatch'),
                ]);
            }

            $assignees = User::query()
                ->whereIn('id', $normalizedAssignments->values()->unique()->all())
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            if ($assignees->count() !== $normalizedAssignments->values()->unique()->count()) {
                throw ValidationException::withMessages([
                    'assignments' => __('procurement.messages.stock_fulfillment_assignee_unavailable'),
                ]);
            }

            foreach ($assets as $asset) {
                $assigneeId = $normalizedAssignments->get((int) $asset->id);

                $this->assetLifecycleService->assign($actor, $asset, [
                    'assigned_to' => $assigneeId,
                    'assigned_at' => now()->format('Y-m-d H:i:s'),
                    'purpose' => __('procurement.messages.stock_fulfillment_assignment_purpose', [
                        'code' => $purchaseRequest->workflowRequest?->request_code ?? (string) $purchaseRequest->id,
                    ]),
                    'purchase_request_item_id' => $line->id,
                ]);
            }

            if ($this->isFullyFulfilled($purchaseRequest->fresh('items'))) {
                $old = $purchaseRequest->toArray();
                $purchaseRequest->update(['status' => PurchaseRequestStatus::Closed]);

                $this->auditLogService->log(
                    'procurement.purchase_request.fulfilled_from_stock',
                    $purchaseRequest,
                    $old,
                    $purchaseRequest->fresh()->toArray()
                );

                $workflowRequest = $purchaseRequest->workflowRequest;
                if ($workflowRequest) {
                    $this->notificationService->notifyPurchaseRequestStockFulfilled($workflowRequest, $purchaseRequest);
                }
            }

            return $purchaseRequest->fresh(['workflowRequest.creator', 'items.assetAssignments.asset']);
        });
    }

    public function remainingQuantity(PurchaseRequestItem $line): int
    {
        return max(0, $this->wholeQuantity($line) - $line->assetAssignments()->count());
    }

    /** @return EloquentCollection<int, Asset> */
    public function reservedAssetsForLine(PurchaseRequestItem $line, bool $lockForUpdate = false): EloquentCollection
    {
        $query = Asset::query()
            ->with('warehouse')
            ->where('reserved_for_purchase_request_item_id', $line->id)
            ->where('status', AssetStatus::Reserved->value)
            ->orderBy('id');

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->get();
    }

    /** @return array<int, Collection<int, Asset>>|null */
    private function lockReservableAssets(PurchaseRequest $purchaseRequest): ?array
    {
        if ($purchaseRequest->items->isEmpty()) {
            return null;
        }

        $reservations = [];

        foreach ($purchaseRequest->items as $line) {
            if (! $line->item?->is_asset_trackable) {
                return null;
            }

            $quantity = $this->wholeQuantityOrNull($line);
            if ($quantity === null) {
                return null;
            }

            $assets = Asset::query()
                ->where('item_id', $line->item_id)
                ->where('status', AssetStatus::Available->value)
                ->whereNull('reserved_for_purchase_request_item_id')
                ->whereNotNull('warehouse_id')
                ->orderBy('id')
                ->limit($quantity)
                ->lockForUpdate()
                ->get();

            if ($assets->count() < $quantity) {
                return null;
            }

            $reservations[$line->id] = $assets;
        }

        return $reservations;
    }

    private function isFullyFulfilled(PurchaseRequest $purchaseRequest): bool
    {
        $purchaseRequest->loadMissing('items');

        return $purchaseRequest->items->every(
            fn (PurchaseRequestItem $line): bool => $line->assetAssignments()->count() >= $this->wholeQuantity($line)
        );
    }

    private function updateRoute(PurchaseRequest $purchaseRequest, PurchaseRequestFulfillmentRoute $route): void
    {
        $old = $purchaseRequest->toArray();
        $purchaseRequest->update(['fulfillment_route' => $route]);

        $this->auditLogService->log(
            'procurement.purchase_request.fulfillment_routed',
            $purchaseRequest,
            $old,
            $purchaseRequest->fresh()->toArray()
        );
    }

    private function wholeQuantity(PurchaseRequestItem $line): int
    {
        $quantity = $this->wholeQuantityOrNull($line);

        if ($quantity === null) {
            throw ValidationException::withMessages([
                'purchase_request' => __('procurement.messages.stock_fulfillment_requires_whole_quantity'),
            ]);
        }

        return $quantity;
    }

    private function wholeQuantityOrNull(PurchaseRequestItem $line): ?int
    {
        $quantity = (float) $line->requested_quantity;
        $rounded = (int) round($quantity);

        if ($quantity <= 0 || abs($quantity - $rounded) > 0.0001) {
            return null;
        }

        return $rounded;
    }
}
