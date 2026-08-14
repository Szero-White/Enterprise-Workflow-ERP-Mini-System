<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkflowRequest;

class WorkflowRequestPolicy
{
    public function view(User $user, WorkflowRequest $workflowRequest): bool
    {
        return $workflowRequest->created_by === $user->id || $user->hasRole('admin');
    }

    public function update(User $user, WorkflowRequest $workflowRequest): bool
    {
        return $workflowRequest->created_by === $user->id
            && $workflowRequest->status === WorkflowRequest::STATUS_RETURNED;
    }

    public function review(User $user, WorkflowRequest $workflowRequest): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        $workflowRequest->loadMissing('currentStep');

        if (
            $workflowRequest->status === WorkflowRequest::STATUS_PENDING
            && $workflowRequest->currentStep?->canBeApprovedBy($user)
        ) {
            return true;
        }

        return $workflowRequest->histories()->where('actor_id', $user->id)->exists();
    }
}
