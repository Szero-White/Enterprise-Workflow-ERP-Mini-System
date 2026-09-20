<?php

namespace App\Services\Procurement;

use App\Contracts\Workflow\WorkflowTransitionHandler;
use App\Enums\PurchaseRequestFulfillmentRoute;
use App\Enums\PurchaseRequestStatus;
use App\Models\WorkflowRequest;
use App\Services\NotificationService;

class PurchaseRequestWorkflowHandler implements WorkflowTransitionHandler
{
    public function __construct(private NotificationService $notificationService) {}

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

        if ($status === PurchaseRequestStatus::Approved) {
            if ($purchaseRequest->fulfillment_route === PurchaseRequestFulfillmentRoute::Stock) {
                $this->notificationService->notifyPurchaseRequestStockReady($workflowRequest, $purchaseRequest);
            } else {
                if ($purchaseRequest->fulfillment_route === PurchaseRequestFulfillmentRoute::Pending) {
                    $purchaseRequest->update(['fulfillment_route' => PurchaseRequestFulfillmentRoute::Procurement]);
                }

                $this->notificationService->notifyPurchaseRequestReady($workflowRequest, $purchaseRequest);
            }
        }
    }
}
