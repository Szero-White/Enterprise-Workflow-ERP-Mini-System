<?php

namespace App\Services\Inventory;

use App\Enums\InventoryMovementType;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryStockService
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function receive(
        User $actor,
        Warehouse $warehouse,
        Product $product,
        float $quantity,
        ?float $unitCost = null,
        ?string $note = null
    ): InventoryStock {
        return DB::transaction(function () use ($actor, $warehouse, $product, $quantity, $unitCost, $note) {
            $stock = $this->lockOrCreateStock($warehouse, $product);
            $oldQuantity = (float) $stock->quantity;
            $stock->update(['quantity' => $oldQuantity + $quantity]);

            $this->recordMovement(
                actor: $actor,
                warehouse: $warehouse,
                product: $product,
                type: InventoryMovementType::Receipt,
                quantity: $quantity,
                balanceAfter: (float) $stock->quantity,
                unitCost: $unitCost,
                note: $note,
            );

            $this->auditLogService->log(
                'inventory.received',
                $stock,
                ['quantity' => $oldQuantity],
                ['quantity' => (float) $stock->quantity],
                __('inventory.audit.received', [
                    'quantity' => $quantity,
                    'unit' => $product->unit,
                    'warehouse' => $warehouse->code,
                ])
            );

            return $stock->fresh(['warehouse', 'product']);
        });
    }

    public function deductForSale(User $actor, SalesOrder $order, Product $product, float $quantity): InventoryStock
    {
        $stock = $this->lockExistingStock($order->warehouse, $product);
        $available = (float) $stock->quantity;

        if ($available < $quantity) {
            throw ValidationException::withMessages([
                'items' => __('inventory.validation.insufficient_stock', [
                    'sku' => $product->sku,
                    'available' => rtrim(rtrim(number_format($available, 3, '.', ''), '0'), '.'),
                    'unit' => $product->unit,
                    'warehouse' => $order->warehouse->code,
                ]),
            ]);
        }

        $stock->update(['quantity' => $available - $quantity]);

        $this->recordMovement(
            actor: $actor,
            warehouse: $order->warehouse,
            product: $product,
            type: InventoryMovementType::Sale,
            quantity: -$quantity,
            balanceAfter: (float) $stock->quantity,
            reference: $order,
            note: __('inventory.audit.sale_note', ['order_code' => $order->order_code]),
        );

        return $stock;
    }

    public function restoreCancelledSale(User $actor, SalesOrder $order, Product $product, float $quantity): InventoryStock
    {
        $stock = $this->lockOrCreateStock($order->warehouse, $product);
        $stock->update(['quantity' => (float) $stock->quantity + $quantity]);

        $this->recordMovement(
            actor: $actor,
            warehouse: $order->warehouse,
            product: $product,
            type: InventoryMovementType::SaleCancellation,
            quantity: $quantity,
            balanceAfter: (float) $stock->quantity,
            reference: $order,
            note: __('inventory.audit.sale_cancel_note', ['order_code' => $order->order_code]),
        );

        return $stock;
    }

    private function lockOrCreateStock(Warehouse $warehouse, Product $product): InventoryStock
    {
        InventoryStock::query()->insertOrIgnore([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return InventoryStock::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function lockExistingStock(Warehouse $warehouse, Product $product): InventoryStock
    {
        $stock = InventoryStock::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->lockForUpdate()
            ->first();

        if (! $stock) {
            throw ValidationException::withMessages([
                'items' => __('inventory.validation.stock_not_found', [
                    'sku' => $product->sku,
                    'warehouse' => $warehouse->code,
                ]),
            ]);
        }

        return $stock;
    }

    private function recordMovement(
        User $actor,
        Warehouse $warehouse,
        Product $product,
        InventoryMovementType $type,
        float $quantity,
        float $balanceAfter,
        ?float $unitCost = null,
        ?Model $reference = null,
        ?string $note = null,
    ): void {
        InventoryMovement::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'created_by' => $actor->id,
            'type' => $type,
            'quantity' => $quantity,
            'balance_after' => $balanceAfter,
            'unit_cost' => $unitCost,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'note' => $note,
        ]);
    }
}
