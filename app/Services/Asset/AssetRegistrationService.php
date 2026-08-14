<?php

namespace App\Services\Asset;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\GoodsReceiptItem;
use App\Services\AuditLogService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AssetRegistrationService
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    /** @return Collection<int, Asset> */
    public function registerFromReceiptItem(GoodsReceiptItem $receiptItem): Collection
    {
        $receiptItem->loadMissing(['item', 'goodsReceipt.warehouse']);

        if (! $receiptItem->item->is_asset_trackable) {
            return collect();
        }

        $quantity = (float) $receiptItem->quantity;
        $roundedQuantity = (int) round($quantity);

        if ($roundedQuantity < 1 || abs($quantity - $roundedQuantity) > 0.000001) {
            throw ValidationException::withMessages([
                'lines' => __('assets.messages.asset_quantity_must_be_whole', [
                    'item' => $receiptItem->item->sku,
                ]),
            ]);
        }

        return collect(range(1, $roundedQuantity))->map(function () use ($receiptItem) {
            $asset = Asset::create([
                'asset_code' => 'TMP-'.uniqid(),
                'item_id' => $receiptItem->item_id,
                'goods_receipt_item_id' => $receiptItem->id,
                'warehouse_id' => $receiptItem->goodsReceipt->warehouse_id,
                'serial_number' => null,
                'acquired_at' => $receiptItem->goodsReceipt->received_at->toDateString(),
                'acquisition_cost' => $receiptItem->unit_cost,
                'status' => AssetStatus::Available,
                'condition' => AssetCondition::New,
                'note' => null,
            ]);

            $asset->update([
                'asset_code' => sprintf('AST-%s-%06d', now()->format('Ym'), $asset->id),
            ]);

            $this->auditLogService->log(
                'asset.registered_from_goods_receipt',
                $asset,
                null,
                $asset->fresh()->toArray(),
                __('assets.audit.registered', [
                    'asset' => $asset->asset_code,
                    'receipt' => $receiptItem->goodsReceipt->receipt_number,
                ])
            );

            return $asset->fresh(['item', 'warehouse']);
        });
    }
}
