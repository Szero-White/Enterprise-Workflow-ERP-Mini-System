<?php

namespace App\Enums;

enum AssetCondition: string
{
    case New = 'new';
    case Good = 'good';
    case NeedsMaintenance = 'needs_maintenance';

    public function label(): string
    {
        return match ($this) {
            self::New => __('assets.condition.new'),
            self::Good => __('assets.condition.good'),
            self::NeedsMaintenance => __('assets.condition.needs_maintenance'),
        };
    }
}
