<?php

namespace App\Services\Asset;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Enums\InventoryMovementType;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetReturn;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use App\Services\Inventory\InventoryStockService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetLifecycleService
{
    public function __construct(
        private InventoryStockService $inventoryStockService,
        private AuditLogService $auditLogService
    ) {}

    public function assign(User $actor, Asset $asset, array $data): AssetAssignment
    {
        return DB::transaction(function () use ($actor, $asset, $data) {
            $lockedAsset = Asset::query()
                ->with(['item', 'warehouse'])
                ->lockForUpdate()
                ->findOrFail($asset->id);

            if ($lockedAsset->status !== AssetStatus::Available || ! $lockedAsset->warehouse_id) {
                throw ValidationException::withMessages([
                    'asset' => __('assets.messages.asset_not_available'),
                ]);
            }

            if ($lockedAsset->assignments()->whereDoesntHave('returnRecord')->exists()) {
                throw ValidationException::withMessages([
                    'asset' => __('assets.messages.asset_already_assigned'),
                ]);
            }

            $assignedAt = Carbon::parse($data['assigned_at']);

            if ($lockedAsset->acquired_at && $assignedAt->startOfDay()->lt($lockedAsset->acquired_at->startOfDay())) {
                throw ValidationException::withMessages([
                    'assigned_at' => __('assets.messages.assignment_before_acquisition'),
                ]);
            }

            $assignee = User::query()->where('is_active', true)->findOrFail($data['assigned_to']);
            $warehouse = $lockedAsset->warehouse;

            $assignment = AssetAssignment::create([
                'asset_id' => $lockedAsset->id,
                'assigned_to' => $assignee->id,
                'assigned_by' => $actor->id,
                'source_warehouse_id' => $warehouse->id,
                'assigned_at' => $data['assigned_at'],
                'expected_return_at' => $data['expected_return_at'] ?? null,
                'purpose' => $data['purpose'] ?? null,
            ]);

            $this->inventoryStockService->issue(
                actor: $actor,
                warehouse: $warehouse,
                item: $lockedAsset->item,
                quantity: 1,
                unitCost: (int) $lockedAsset->acquisition_cost,
                note: __('assets.messages.inventory_assignment_note', [
                    'asset' => $lockedAsset->asset_code,
                    'employee' => $assignee->name,
                ]),
                reference: $assignment,
                movementType: InventoryMovementType::AssetAssignment,
            );

            $old = $lockedAsset->toArray();
            $lockedAsset->update([
                'status' => AssetStatus::Assigned,
                'warehouse_id' => null,
            ]);

            $this->auditLogService->log(
                'asset.assigned',
                $assignment,
                null,
                $assignment->fresh()->toArray(),
                __('assets.audit.assigned', [
                    'asset' => $lockedAsset->asset_code,
                    'employee' => $assignee->name,
                ])
            );

            $this->auditLogService->log('asset.status_changed', $lockedAsset, $old, $lockedAsset->fresh()->toArray());

            return $assignment->fresh(['asset.item', 'assignee', 'assigner', 'sourceWarehouse']);
        });
    }

    public function returnAsset(User $actor, AssetAssignment $assignment, array $data): AssetReturn
    {
        return DB::transaction(function () use ($actor, $assignment, $data) {
            $lockedAssignment = AssetAssignment::query()
                ->with(['asset.item', 'returnRecord'])
                ->lockForUpdate()
                ->findOrFail($assignment->id);

            if ($lockedAssignment->returnRecord) {
                throw ValidationException::withMessages([
                    'assignment' => __('assets.messages.assignment_already_returned'),
                ]);
            }

            $asset = Asset::query()->lockForUpdate()->findOrFail($lockedAssignment->asset_id);

            if ($asset->status !== AssetStatus::Assigned) {
                throw ValidationException::withMessages([
                    'asset' => __('assets.messages.asset_not_assigned'),
                ]);
            }

            $returnedAt = Carbon::parse($data['returned_at']);

            if ($lockedAssignment->assigned_at && $returnedAt->lt($lockedAssignment->assigned_at)) {
                throw ValidationException::withMessages([
                    'returned_at' => __('assets.messages.return_before_assignment'),
                ]);
            }

            $warehouse = Warehouse::query()
                ->where('is_active', true)
                ->findOrFail($data['warehouse_id']);

            $condition = AssetCondition::from($data['condition']);

            $returnRecord = AssetReturn::create([
                'asset_assignment_id' => $lockedAssignment->id,
                'received_by' => $actor->id,
                'warehouse_id' => $warehouse->id,
                'returned_at' => $data['returned_at'],
                'condition' => $condition,
                'note' => $data['note'] ?? null,
            ]);

            $this->inventoryStockService->receive(
                actor: $actor,
                warehouse: $warehouse,
                item: $lockedAssignment->asset->item,
                quantity: 1,
                unitCost: (int) $lockedAssignment->asset->acquisition_cost,
                note: __('assets.messages.inventory_return_note', [
                    'asset' => $asset->asset_code,
                ]),
                reference: $returnRecord,
                movementType: InventoryMovementType::AssetReturn,
            );

            $old = $asset->toArray();
            $asset->update([
                'warehouse_id' => $warehouse->id,
                'condition' => $condition,
                'status' => $condition === AssetCondition::NeedsMaintenance
                    ? AssetStatus::Maintenance
                    : AssetStatus::Available,
            ]);

            $this->auditLogService->log(
                'asset.returned',
                $returnRecord,
                null,
                $returnRecord->fresh()->toArray(),
                __('assets.audit.returned', [
                    'asset' => $asset->asset_code,
                    'warehouse' => $warehouse->code,
                ])
            );

            $this->auditLogService->log('asset.status_changed', $asset, $old, $asset->fresh()->toArray());

            return $returnRecord->fresh(['assignment.asset.item', 'receiver', 'warehouse']);
        });
    }

    public function releaseFromMaintenance(User $actor, Asset $asset): Asset
    {
        return DB::transaction(function () use ($asset) {
            $lockedAsset = Asset::query()->lockForUpdate()->findOrFail($asset->id);

            if ($lockedAsset->status !== AssetStatus::Maintenance || ! $lockedAsset->warehouse_id) {
                throw ValidationException::withMessages([
                    'asset' => __('assets.messages.asset_not_in_maintenance'),
                ]);
            }

            $old = $lockedAsset->toArray();
            $lockedAsset->update([
                'status' => AssetStatus::Available,
                'condition' => AssetCondition::Good,
            ]);

            $this->auditLogService->log(
                'asset.maintenance_completed',
                $lockedAsset,
                $old,
                $lockedAsset->fresh()->toArray(),
                __('assets.audit.maintenance_completed', ['asset' => $lockedAsset->asset_code])
            );

            return $lockedAsset->fresh(['item', 'warehouse']);
        });
    }
}
