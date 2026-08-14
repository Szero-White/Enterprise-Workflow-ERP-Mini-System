<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case PartiallyReceived = 'partially_received';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __('procurement.purchase_order.status.'.$this->value);
    }
}
