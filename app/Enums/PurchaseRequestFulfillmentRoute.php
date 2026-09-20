<?php

namespace App\Enums;

enum PurchaseRequestFulfillmentRoute: string
{
    case Pending = 'pending';
    case Stock = 'stock';
    case Procurement = 'procurement';

    public function label(): string
    {
        return __('procurement.purchase_request.fulfillment_route.'.$this->value);
    }

    public function isStockRoute(): bool
    {
        return $this === self::Stock;
    }
}
