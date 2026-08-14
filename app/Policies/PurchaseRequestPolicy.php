<?php

namespace App\Policies;

use App\Models\PurchaseRequest;
use App\Models\User;
use App\Models\WorkflowRequest;

class PurchaseRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['employee', 'manager', 'procurement', 'finance', 'director', 'admin']);
    }

    public function view(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $purchaseRequest->canBeViewedBy($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['employee', 'admin']);
    }

    public function update(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $this->isOwner($user, $purchaseRequest)
            && $purchaseRequest->workflowRequest?->status === WorkflowRequest::STATUS_RETURNED;
    }

    private function isOwner(User $user, PurchaseRequest $purchaseRequest): bool
    {
        $purchaseRequest->loadMissing('workflowRequest');

        return $purchaseRequest->workflowRequest?->created_by === $user->id;
    }
}
