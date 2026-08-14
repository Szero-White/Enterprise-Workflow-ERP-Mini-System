<?php

namespace App\Enums;

enum SalesOrderStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('sales.status.draft'),
            self::Confirmed => __('sales.status.confirmed'),
            self::Cancelled => __('sales.status.cancelled'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'text-bg-secondary',
            self::Confirmed => 'text-bg-success',
            self::Cancelled => 'text-bg-danger',
        };
    }
}
