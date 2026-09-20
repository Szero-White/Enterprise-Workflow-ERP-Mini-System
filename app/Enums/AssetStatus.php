<?php

namespace App\Enums;

enum AssetStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Assigned = 'assigned';
    case Maintenance = 'maintenance';

    public function label(): string
    {
        return match ($this) {
            self::Available => __('assets.status.available'),
            self::Reserved => __('assets.status.reserved'),
            self::Assigned => __('assets.status.assigned'),
            self::Maintenance => __('assets.status.maintenance'),
        };
    }
}
