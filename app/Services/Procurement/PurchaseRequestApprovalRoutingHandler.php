<?php

namespace App\Services\Procurement;

use App\Contracts\Workflow\WorkflowApprovalRoutingHandler;
use App\Models\WorkflowRequest;
use App\Models\WorkflowStep;

class PurchaseRequestApprovalRoutingHandler implements WorkflowApprovalRoutingHandler
{
    public function __construct(private PurchaseRequestStockFulfillmentService $stockFulfillmentService) {}

    public function supports(WorkflowRequest $workflowRequest): bool
    {
        return $workflowRequest->formTemplate?->code === PurchaseRequestService::FORM_CODE;
    }

    public function shouldCompleteAfterApproval(WorkflowRequest $workflowRequest, WorkflowStep $approvedStep): bool
    {
        if (
            $approvedStep->approver_type !== WorkflowStep::APPROVER_ROLE
            || $approvedStep->approverRole?->key !== 'manager'
        ) {
            return false;
        }

        $purchaseRequest = $workflowRequest->purchaseRequest;

        if (! $purchaseRequest) {
            return false;
        }

        return $this->stockFulfillmentService->routeAfterManagerApproval($purchaseRequest)->isStockRoute();
    }
}
