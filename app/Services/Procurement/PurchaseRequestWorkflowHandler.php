<?php

namespace App\Services\Procurement;

use App\Contracts\Workflow\WorkflowTransitionHandler;
use App\Enums\PurchaseRequestStatus;
use App\Models\WorkflowRequest;

class PurchaseRequestWorkflowHandler implements WorkflowTransitionHandler
{
    public function supports(WorkflowRequest $workflowRequest): bool
    {
        return $workflowRequest->formTemplate?->code === PurchaseRequestService::FORM_CODE;
    }

    public function handle(WorkflowRequest $workflowRequest): void
    {
        $purchaseRequest = $workflowRequest->purchaseRequest;

        if (! $purchaseRequest) {
            return;
        }

        $status = match ($workflowRequest->status) {
            WorkflowRequest::STATUS_APPROVED => PurchaseRequestStatus::Approved,
            WorkflowRequest::STATUS_REJECTED => PurchaseRequestStatus::Rejected,
            WorkflowRequest::STATUS_RETURNED => PurchaseRequestStatus::Returned,
            default => PurchaseRequestStatus::PendingApproval,
        };

        $purchaseRequest->update(['status' => $status]);
    }
}
