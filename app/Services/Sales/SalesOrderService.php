<?php

namespace App\Services\Sales;

use App\Enums\SalesOrderStatus;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Inventory\InventoryStockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SalesOrderService
{
    public function __construct(
        private InventoryStockService $inventoryStockService,
        private AuditLogService $auditLogService,
    ) {
    }

    public function createDraft(User $actor, array $data): SalesOrder
    {
        return DB::transaction(function () use ($actor, $data) {
            $products = Product::query()
                ->whereIn('id', collect($data['items'])->pluck('product_id'))
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            if ($products->count() !== count($data['items'])) {
                throw ValidationException::withMessages(['items' => __('sales.validation.product_unavailable')]);
            }

            $subtotal = 0.0;
            $normalizedItems = [];

            foreach ($data['items'] as $item) {
                $product = $products->get((int) $item['product_id']);
                $quantity = (float) $item['quantity'];
                // Unit price is sourced from backend master data. The client submits only product and quantity.
                $unitPrice = (float) $product->sale_price;
                $lineTotal = round($quantity * $unitPrice, 2);
                $subtotal += $lineTotal;

                $normalizedItems[] = [
                    'product_id' => $product->id,
                    'product_sku' => $product->sku,
                    'product_name' => $product->name,
                    'unit' => $product->unit,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $discount = (float) ($data['discount_amount'] ?? 0);
            if ($discount > $subtotal) {
                throw ValidationException::withMessages(['discount_amount' => __('sales.validation.discount_exceeds_subtotal')]);
            }

            $order = SalesOrder::create([
                'order_code' => $this->generateOrderCode(),
                'customer_id' => $data['customer_id'],
                'warehouse_id' => $data['warehouse_id'],
                'created_by' => $actor->id,
                'status' => SalesOrderStatus::Draft,
                'order_date' => $data['order_date'],
                'subtotal' => round($subtotal, 2),
                'discount_amount' => round($discount, 2),
                'total_amount' => round($subtotal - $discount, 2),
                'notes' => $data['notes'] ?? null,
            ]);

            $order->items()->createMany($normalizedItems);
            $this->auditLogService->log('sales_order.created', $order, null, $order->toArray());

            return $order->fresh(['customer', 'warehouse', 'creator', 'items.product']);
        });
    }

    public function confirm(User $actor, SalesOrder $order): SalesOrder
    {
        return DB::transaction(function () use ($actor, $order) {
            $lockedOrder = SalesOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== SalesOrderStatus::Draft) {
                throw ValidationException::withMessages(['order' => __('sales.validation.only_draft_can_confirm')]);
            }

            $lockedOrder->load(['warehouse', 'items.product']);
            $old = $lockedOrder->toArray();

            foreach ($lockedOrder->items as $item) {
                $this->inventoryStockService->deductForSale(
                    $actor,
                    $lockedOrder,
                    $item->product,
                    (float) $item->quantity,
                );
            }

            $lockedOrder->update([
                'status' => SalesOrderStatus::Confirmed,
                'confirmed_at' => now(),
            ]);

            $fresh = $lockedOrder->fresh(['customer', 'warehouse', 'creator', 'items.product']);
            $this->auditLogService->log('sales_order.confirmed', $fresh, $old, $fresh->toArray());

            return $fresh;
        });
    }

    public function cancel(User $actor, SalesOrder $order): SalesOrder
    {
        return DB::transaction(function () use ($actor, $order) {
            $lockedOrder = SalesOrder::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status === SalesOrderStatus::Cancelled) {
                throw ValidationException::withMessages(['order' => __('sales.validation.already_cancelled')]);
            }

            $lockedOrder->load(['warehouse', 'items.product']);
            $old = $lockedOrder->toArray();

            if ($lockedOrder->status === SalesOrderStatus::Confirmed) {
                foreach ($lockedOrder->items as $item) {
                    $this->inventoryStockService->restoreCancelledSale(
                        $actor,
                        $lockedOrder,
                        $item->product,
                        (float) $item->quantity,
                    );
                }
            }

            $lockedOrder->update([
                'status' => SalesOrderStatus::Cancelled,
                'cancelled_at' => now(),
            ]);

            $fresh = $lockedOrder->fresh(['customer', 'warehouse', 'creator', 'items.product']);
            $this->auditLogService->log('sales_order.cancelled', $fresh, $old, $fresh->toArray());

            return $fresh;
        });
    }

    private function generateOrderCode(): string
    {
        return 'SO-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }
}
