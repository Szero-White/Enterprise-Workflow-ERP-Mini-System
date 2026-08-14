<?php

namespace App\Enums;

enum PurchaseRequestStatus: string
{
    case PendingApproval = 'pending_approval';
    case Returned = 'returned';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Ordered = 'ordered';
    case Closed = 'closed';

    public function label(): string
    {
        return __('procurement.purchase_request.status.'.$this->value);
    }
}
