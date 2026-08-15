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
use Illuminate\Validation\ValidationException;

class InventoryStockService
{
    public function __construct(private AuditLogService $auditLogService) {}

    public function receive(
        User $actor,
        Warehouse $warehouse,
        Item $item,
        float $quantity,
        ?int $unitCost = null,
        ?string $note = null,
        ?Model $reference = null,
        InventoryMovementType $movementType = InventoryMovementType::Receipt
    ): InventoryStock {
        return DB::transaction(function () use (
            $actor,
            $warehouse,
            $item,
            $quantity,
            $unitCost,
            $note,
            $reference,
            $movementType
        ) {
            if ($quantity <= 0) {
                throw ValidationException::withMessages([
                    'quantity' => __('inventory.validation.quantity_must_be_positive'),
                ]);
            }

            $stock = $this->lockOrCreateStock($warehouse, $item);
            $oldQuantity = (float) $stock->quantity;

            $stock->update([
                'quantity' => $oldQuantity + $quantity,
            ]);

            $this->recordMovement(
                actor: $actor,
                warehouse: $warehouse,
                item: $item,
                type: $movementType,
                quantity: $quantity,
                balanceAfter: (float) $stock->quantity,
                unitCost: $unitCost,
                reference: $reference,
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

    public function issue(
        User $actor,
        Warehouse $warehouse,
        Item $item,
        float $quantity,
        ?int $unitCost = null,
        ?string $note = null,
        ?Model $reference = null,
        InventoryMovementType $movementType = InventoryMovementType::AdjustmentOut
    ): InventoryStock {
        return DB::transaction(function () use (
            $actor,
            $warehouse,
            $item,
            $quantity,
            $unitCost,
            $note,
            $reference,
            $movementType
        ) {
            $stock = InventoryStock::query()
                ->where('warehouse_id', $warehouse->id)
                ->where('item_id', $item->id)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                throw ValidationException::withMessages([
                    'asset' => __('inventory.validation.stock_not_found', [
                        'sku' => $item->sku,
                        'warehouse' => $warehouse->code,
                    ]),
                ]);
            }

            $oldQuantity = (float) $stock->quantity;

            if ($quantity <= 0 || $oldQuantity + 0.0001 < $quantity) {
                throw ValidationException::withMessages([
                    'asset' => __('inventory.validation.insufficient_stock', [
                        'sku' => $item->sku,
                        'warehouse' => $warehouse->code,
                        'available' => $oldQuantity,
                        'unit' => $item->unit,
                    ]),
                ]);
            }

            $stock->update([
                'quantity' => $oldQuantity - $quantity,
            ]);

            $this->recordMovement(
                actor: $actor,
                warehouse: $warehouse,
                item: $item,
                type: $movementType,
                quantity: -$quantity,
                balanceAfter: (float) $stock->quantity,
                unitCost: $unitCost,
                reference: $reference,
                note: $note,
            );

            $this->auditLogService->log(
                'inventory.issued',
                $stock,
                ['quantity' => $oldQuantity],
                ['quantity' => (float) $stock->quantity],
                __('inventory.audit.issued', [
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
        ?int $unitCost = null,
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
