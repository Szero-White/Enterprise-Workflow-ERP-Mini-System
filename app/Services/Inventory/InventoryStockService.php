<?php

namespace App\Services\Inventory;

use App\Enums\InventoryMovementType;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Item;
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
        Item $item,
        float $quantity,
        ?float $unitCost = null,
        ?string $note = null
    ): InventoryStock {
        return DB::transaction(function () use ($actor, $warehouse, $item, $quantity, $unitCost, $note) {
            $stock = $this->lockOrCreateStock($warehouse, $item);
            $oldQuantity = (float) $stock->quantity;
            $stock->update(['quantity' => $oldQuantity + $quantity]);

            $this->recordMovement(
                actor: $actor,
                warehouse: $warehouse,
                item: $item,
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
                    'unit' => $item->unit,
                    'warehouse' => $warehouse->code,
                ])
            );

            return $stock->fresh(['warehouse', 'item']);
        });
    }

    private function lockOrCreateStock(Warehouse $warehouse, Item $item): InventoryStock
    {
        InventoryStock::query()->insertOrIgnore([
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
            'quantity' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return InventoryStock::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('item_id', $item->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function recordMovement(
        User $actor,
        Warehouse $warehouse,
        Item $item,
        InventoryMovementType $type,
        float $quantity,
        float $balanceAfter,
        ?float $unitCost = null,
        ?Model $reference = null,
        ?string $note = null,
    ): void {
        InventoryMovement::create([
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
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
