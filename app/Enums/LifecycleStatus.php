<?php

namespace App\Enums;

enum LifecycleStatus: string
{
    case Current = 'current';
    case Draft = 'draft';
    case Legacy = 'legacy';
    case Inactive = 'inactive';

    public function label(): string
    {
        return __('ui.lifecycle_'.$this->value);
    }

    public function tooltip(): string
    {
        return __('ui.lifecycle_'.$this->value.'_tooltip');
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Current => 'erp-lifecycle-badge erp-lifecycle-badge--current',
            self::Draft => 'erp-lifecycle-badge erp-lifecycle-badge--draft',
            self::Legacy => 'erp-lifecycle-badge erp-lifecycle-badge--legacy',
            self::Inactive => 'erp-lifecycle-badge erp-lifecycle-badge--inactive',
        };
    }
}
