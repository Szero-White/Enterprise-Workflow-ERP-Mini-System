<?php

namespace App\Enums;

enum InventoryMovementType: string
{
    case Receipt = 'receipt';
    case Sale = 'sale';
    case SaleCancellation = 'sale_cancellation';
    case AdjustmentIn = 'adjustment_in';
    case AdjustmentOut = 'adjustment_out';

    public function label(): string
    {
        return match ($this) {
            self::Receipt => __('inventory.movement.receipt'),
            self::Sale => __('inventory.movement.sale'),
            self::SaleCancellation => __('inventory.movement.sale_cancellation'),
            self::AdjustmentIn => __('inventory.movement.adjustment_in'),
            self::AdjustmentOut => __('inventory.movement.adjustment_out'),
        };
    }
}
