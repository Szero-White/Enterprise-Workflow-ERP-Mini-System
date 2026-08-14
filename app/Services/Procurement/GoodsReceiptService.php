<?php

namespace App\Services\Procurement;

use App\Enums\InventoryMovementType;
use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Services\Asset\AssetRegistrationService;
use App\Services\AuditLogService;
use App\Services\Inventory\InventoryStockService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoodsReceiptService
{
    public function __construct(
        private InventoryStockService $inventoryStockService,
        private AssetRegistrationService $assetRegistrationService,
        private AuditLogService $auditLogService
    ) {
    }

    public function receive(User $actor, PurchaseOrder $purchaseOrder, array $data): GoodsReceipt
    {
        return DB::transaction(function () use ($actor, $purchaseOrder, $data) {
            $order = PurchaseOrder::query()
                ->with(['items.item', 'warehouse', 'purchaseRequest'])
                ->lockForUpdate()
                ->findOrFail($purchaseOrder->id);

            abort_unless(
                in_array($order->status, [PurchaseOrderStatus::Issued, PurchaseOrderStatus::PartiallyReceived], true),
                422,
                __('procurement.messages.purchase_order_not_receivable')
            );

            $receivedAt = Carbon::parse($data['received_at']);

            if ($order->ordered_at && $receivedAt->lt($order->ordered_at)) {
                throw ValidationException::withMessages([
                    'received_at' => __('procurement.messages.goods_receipt_before_issue'),
                ]);
            }

            $requestedQuantities = collect($data['lines'])->mapWithKeys(
                fn (array $line) => [(int) $line['purchase_order_item_id'] => (float) ($line['quantity'] ?? 0)]
            );

            if ($requestedQuantities->filter(fn (float $quantity) => $quantity > 0)->isEmpty()) {
                throw ValidationException::withMessages([
                    'lines' => __('procurement.messages.receipt_requires_quantity'),
                ]);
            }

            $orderItemIds = $order->items->pluck('id')->map(fn ($id) => (int) $id);
            $unknownLineIds = $requestedQuantities->keys()->diff($orderItemIds);

            if ($unknownLineIds->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'lines' => __('procurement.messages.receipt_lines_mismatch'),
                ]);
            }

            $receipt = GoodsReceipt::create([
                'receipt_number' => 'TMP-'.uniqid(),
                'purchase_order_id' => $order->id,
                'warehouse_id' => $order->warehouse_id,
                'received_by' => $actor->id,
                'received_at' => $data['received_at'],
                'supplier_reference' => $data['supplier_reference'] ?? null,
                'note' => $data['note'] ?? null,
            ]);

            $receipt->update([
                'receipt_number' => sprintf('GR-%s-%06d', now()->format('Ym'), $receipt->id),
            ]);

            foreach ($order->items as $orderItem) {
                $quantity = (float) $requestedQuantities->get($orderItem->id, 0);

                if ($quantity <= 0) {
                    continue;
                }

                $outstanding = $orderItem->outstanding_quantity;

                if ($quantity > $outstanding + 0.0001) {
                    throw ValidationException::withMessages([
                        'lines' => __('procurement.messages.receipt_exceeds_outstanding', [
                            'item' => $orderItem->item_sku,
                            'outstanding' => $outstanding,
                        ]),
                    ]);
                }

                $receiptItem = $receipt->items()->create([
                    'purchase_order_item_id' => $orderItem->id,
                    'item_id' => $orderItem->item_id,
                    'quantity' => $quantity,
                    'unit_cost' => $orderItem->unit_cost,
                ]);

                $this->inventoryStockService->receive(
                    actor: $actor,
                    warehouse: $order->warehouse,
                    item: $orderItem->item,
                    quantity: $quantity,
                    unitCost: (float) $orderItem->unit_cost,
                    note: __('procurement.messages.inventory_receipt_note', [
                        'receipt' => $receipt->receipt_number,
                        'po' => $order->po_number,
                    ]),
                    reference: $receipt,
                    movementType: InventoryMovementType::PurchaseReceipt,
                );

                $this->assetRegistrationService->registerFromReceiptItem($receiptItem);

                $orderItem->update([
                    'received_quantity' => (float) $orderItem->received_quantity + $quantity,
                ]);
            }

            $allReceived = $order->items()->get()->every(
                fn ($line) => (float) $line->received_quantity >= (float) $line->ordered_quantity - 0.0001
            );

            $order->update([
                'status' => $allReceived
                    ? PurchaseOrderStatus::Received
                    : PurchaseOrderStatus::PartiallyReceived,
            ]);

            $order->purchaseRequest()->update([
                'status' => $allReceived
                    ? PurchaseRequestStatus::Closed
                    : PurchaseRequestStatus::Ordered,
            ]);

            $this->auditLogService->log(
                'procurement.goods_receipt.posted',
                $receipt,
                null,
                $receipt->fresh('items')->toArray()
            );

            return $receipt->fresh([
                'purchaseOrder.supplier',
                'warehouse',
                'receiver',
                'items.item',
                'items.assets',
            ]);
        });
    }
}
