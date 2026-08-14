<?php

namespace App\Enums;

enum AssetStatus: string
{
    case Available = 'available';
    case Assigned = 'assigned';
    case Maintenance = 'maintenance';

    public function label(): string
    {
        return match ($this) {
            self::Available => __('assets.status.available'),
            self::Assigned => __('assets.status.assigned'),
            self::Maintenance => __('assets.status.maintenance'),
        };
    }
}
