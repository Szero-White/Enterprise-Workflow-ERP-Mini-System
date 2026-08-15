<?php

namespace App\Services\Procurement;

use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WorkflowRequest;
use App\Services\AuditLogService;
use App\Support\Money\VndMoney;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PurchaseOrderService
{
    public function __construct(private AuditLogService $auditLogService) {}

    public function createDraft(
        User $actor,
        PurchaseRequest $purchaseRequest,
        Supplier $supplier,
        Warehouse $warehouse,
        array $data
    ): PurchaseOrder {
        return DB::transaction(function () use ($actor, $purchaseRequest, $supplier, $warehouse, $data) {
            $purchaseRequest = PurchaseRequest::query()
                ->with(['workflowRequest', 'items'])
                ->lockForUpdate()
                ->findOrFail($purchaseRequest->id);

            abort_unless(
                $purchaseRequest->workflowRequest->status === WorkflowRequest::STATUS_APPROVED
                    && $purchaseRequest->status === PurchaseRequestStatus::Approved,
                422,
                __('procurement.messages.purchase_request_not_approved')
            );

            abort_if(
                $purchaseRequest->activePurchaseOrder()->exists(),
                422,
                __('procurement.messages.purchase_order_exists')
            );

            $costs = collect($data['lines'])->keyBy('purchase_request_item_id');
            $expectedLineIds = $purchaseRequest->items->pluck('id')->sort()->values()->all();
            $providedLineIds = $costs->keys()->map(fn ($id) => (int) $id)->sort()->values()->all();

            if ($expectedLineIds !== $providedLineIds) {
                throw ValidationException::withMessages([
                    'lines' => __('procurement.messages.purchase_order_lines_mismatch'),
                ]);
            }

            $order = PurchaseOrder::create([
                'po_number' => 'TMP-'.uniqid(),
                'purchase_request_id' => $purchaseRequest->id,
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'created_by' => $actor->id,
                'status' => PurchaseOrderStatus::Draft,
                'expected_date' => $data['expected_date'] ?? null,
                'subtotal' => 0,
                'note' => $data['note'] ?? null,
            ]);

            $order->update([
                'po_number' => sprintf('PO-%s-%06d', now()->format('Ym'), $order->id),
            ]);

            $subtotal = 0;

            try {
                foreach ($purchaseRequest->items as $requestItem) {
                    $unitCost = VndMoney::toInteger((string) $costs->get($requestItem->id)['unit_cost']);
                    $quantity = (string) $requestItem->requested_quantity;
                    $lineTotal = VndMoney::multiplyByQuantity($unitCost, $quantity);
                    $subtotal = VndMoney::add($subtotal, $lineTotal);

                    $order->items()->create([
                        'purchase_request_item_id' => $requestItem->id,
                        'item_id' => $requestItem->item_id,
                        'item_sku' => $requestItem->item_sku,
                        'item_name' => $requestItem->item_name,
                        'unit' => $requestItem->unit,
                        'ordered_quantity' => $quantity,
                        'received_quantity' => 0,
                        'unit_cost' => $unitCost,
                        'line_total' => $lineTotal,
                    ]);
                }
            } catch (InvalidArgumentException) {
                throw ValidationException::withMessages([
                    'lines' => __('procurement.messages.money_total_too_large'),
                ]);
            }

            $order->update(['subtotal' => $subtotal]);

            $this->auditLogService->log(
                'procurement.purchase_order.created',
                $order,
                null,
                $order->fresh('items')->toArray()
            );

            return $order->fresh(['supplier', 'warehouse', 'items']);
        });
    }

    public function issue(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder) {
            $order = PurchaseOrder::query()->lockForUpdate()->findOrFail($purchaseOrder->id);

            abort_unless(
                $order->status === PurchaseOrderStatus::Draft,
                422,
                __('procurement.messages.purchase_order_not_draft')
            );

            $old = $order->toArray();

            $order->update([
                'status' => PurchaseOrderStatus::Issued,
                'ordered_at' => now(),
            ]);

            $order->purchaseRequest()->update(['status' => PurchaseRequestStatus::Ordered]);
            $this->auditLogService->log(
                'procurement.purchase_order.issued',
                $order,
                $old,
                $order->fresh()->toArray()
            );

            return $order->fresh();
        });
    }

    public function cancel(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder) {
            $order = PurchaseOrder::query()
                ->withCount('goodsReceipts')
                ->lockForUpdate()
                ->findOrFail($purchaseOrder->id);

            abort_unless(
                in_array($order->status, [PurchaseOrderStatus::Draft, PurchaseOrderStatus::Issued], true),
                422,
                __('procurement.messages.purchase_order_not_cancellable')
            );

            abort_if(
                $order->goods_receipts_count > 0,
                422,
                __('procurement.messages.purchase_order_has_receipts')
            );

            $old = $order->toArray();
            $order->update(['status' => PurchaseOrderStatus::Cancelled]);
            $order->purchaseRequest()->update(['status' => PurchaseRequestStatus::Approved]);

            $this->auditLogService->log(
                'procurement.purchase_order.cancelled',
                $order,
                $old,
                $order->fresh()->toArray()
            );

            return $order->fresh();
        });
    }
}
