<?php

namespace App\Enums;

enum InventoryMovementType: string
{
    case Receipt = 'receipt';
    case PurchaseReceipt = 'purchase_receipt';
    case AdjustmentIn = 'adjustment_in';
    case AdjustmentOut = 'adjustment_out';

    public function label(): string
    {
        return match ($this) {
            self::Receipt => __('inventory.movement.receipt'),
            self::PurchaseReceipt => __('inventory.movement.purchase_receipt'),
            self::AdjustmentIn => __('inventory.movement.adjustment_in'),
            self::AdjustmentOut => __('inventory.movement.adjustment_out'),
        };
    }
}
