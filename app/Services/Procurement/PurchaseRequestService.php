<?php

namespace App\Services\Procurement;

use App\Enums\PurchaseRequestStatus;
use App\Models\FormTemplate;
use App\Models\Item;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Models\WorkflowRequest;
use App\Services\AuditLogService;
use App\Services\DynamicRequestService;
use App\Support\Money\VndMoney;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PurchaseRequestService
{
    public const FORM_CODE = 'PURCHASE_REQUEST';

    public function __construct(
        private DynamicRequestService $dynamicRequestService,
        private AuditLogService $auditLogService
    ) {}

    public function create(User $user, array $data, Request $httpRequest): PurchaseRequest
    {
        return DB::transaction(function () use ($user, $data, $httpRequest) {
            $estimatedTotal = $this->estimatedTotal($data['items']);
            $this->mergeWorkflowValues($httpRequest, $data, $estimatedTotal);

            $template = FormTemplate::query()
                ->where('code', self::FORM_CODE)
                ->where('submission_type', 'procurement')
                ->where('is_active', true)
                ->firstOrFail();

            $workflowRequest = $this->dynamicRequestService->create(
                $user,
                $template->load('fields'),
                $httpRequest
            );

            $purchaseRequest = PurchaseRequest::create([
                'workflow_request_id' => $workflowRequest->id,
                'purpose' => $data['purpose'],
                'required_date' => $data['required_date'] ?? null,
                'estimated_total' => $estimatedTotal,
                'currency' => 'VND',
                'status' => PurchaseRequestStatus::PendingApproval,
            ]);

            $this->replaceItems($purchaseRequest, $data['items']);

            $this->auditLogService->log(
                'procurement.purchase_request.created',
                $purchaseRequest,
                null,
                $purchaseRequest->load('items')->toArray()
            );

            return $purchaseRequest->fresh(['workflowRequest', 'items.item']);
        });
    }

    public function updateReturned(
        User $user,
        PurchaseRequest $purchaseRequest,
        array $data,
        Request $httpRequest
    ): PurchaseRequest {
        return DB::transaction(function () use ($user, $purchaseRequest, $data, $httpRequest) {
            $workflowRequest = $purchaseRequest->workflowRequest;

            abort_unless(
                $workflowRequest->created_by === $user->id
                    && $workflowRequest->status === WorkflowRequest::STATUS_RETURNED,
                403
            );

            $old = $purchaseRequest->load('items')->toArray();
            $estimatedTotal = $this->estimatedTotal($data['items']);
            $this->mergeWorkflowValues($httpRequest, $data, $estimatedTotal);

            $this->dynamicRequestService->updateReturned(
                $user,
                $workflowRequest->load('formTemplate.fields'),
                $httpRequest
            );

            $purchaseRequest->update([
                'purpose' => $data['purpose'],
                'required_date' => $data['required_date'] ?? null,
                'estimated_total' => $estimatedTotal,
                'status' => PurchaseRequestStatus::PendingApproval,
            ]);

            $this->replaceItems($purchaseRequest, $data['items']);

            $this->auditLogService->log(
                'procurement.purchase_request.resubmitted',
                $purchaseRequest,
                $old,
                $purchaseRequest->fresh('items')->toArray()
            );

            return $purchaseRequest->fresh(['workflowRequest', 'items.item']);
        });
    }

    private function replaceItems(PurchaseRequest $purchaseRequest, array $lines): void
    {
        $items = $this->loadItems($lines);
        $purchaseRequest->items()->delete();

        foreach ($lines as $line) {
            /** @var Item $item */
            $item = $items->get((int) $line['item_id']);

            $purchaseRequest->items()->create([
                'item_id' => $item->id,
                'item_sku' => $item->sku,
                'item_name' => $item->name,
                'unit' => $item->unit,
                'requested_quantity' => $line['quantity'],
                'estimated_unit_cost' => $line['estimated_unit_cost'],
                'note' => $line['note'] ?? null,
            ]);
        }
    }

    /** @return Collection<int, Item> */
    private function loadItems(array $lines): Collection
    {
        $ids = collect($lines)
            ->pluck('item_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $items = Item::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        if ($items->count() !== $ids->count()) {
            throw ValidationException::withMessages([
                'items' => __('procurement.messages.purchase_request_items_unavailable'),
            ]);
        }

        return $items;
    }

    private function estimatedTotal(array $items): int
    {
        try {
            return array_reduce(
                $items,
                fn (int $sum, array $line): int => VndMoney::add(
                    $sum,
                    VndMoney::multiplyByQuantity((string) $line['estimated_unit_cost'], (string) $line['quantity'])
                ),
                0
            );
        } catch (InvalidArgumentException) {
            throw ValidationException::withMessages([
                'items' => __('procurement.messages.money_total_too_large'),
            ]);
        }
    }

    private function mergeWorkflowValues(Request $request, array $data, int $estimatedTotal): void
    {
        $request->merge([
            'purpose' => $data['purpose'],
            'required_date' => $data['required_date'] ?? null,
            'estimated_total' => $estimatedTotal,
        ]);
    }
}
