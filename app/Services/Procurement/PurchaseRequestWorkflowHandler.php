<?php

namespace App\Services\Procurement;

use App\Contracts\Workflow\WorkflowTransitionHandler;
use App\Enums\PurchaseRequestStatus;
use App\Models\Notification;
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
            $this->notifyProcurementReady($workflowRequest, $purchaseRequest->id);
        }
    }

    private function notifyProcurementReady(WorkflowRequest $workflowRequest, int $purchaseRequestId): void
    {
        $this->notificationService->notifyRoleUsers(
            'procurement',
            __('messages.notification_purchase_request_ready_title'),
            __('messages.notification_purchase_request_ready_body', [
                'code' => $workflowRequest->request_code,
            ]),
            Notification::TYPE_PURCHASE_REQUEST_READY,
            [
                'request_id' => $workflowRequest->id,
                'purchase_request_id' => $purchaseRequestId,
                'request_code' => $workflowRequest->request_code,
                'status' => $workflowRequest->status,
                'action' => 'create_purchase_order',
            ]
        );
    }
}
