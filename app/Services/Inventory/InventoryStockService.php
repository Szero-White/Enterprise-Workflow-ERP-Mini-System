<?php

namespace App\Services\Inventory;

use App\Enums\InventoryMovementType;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
