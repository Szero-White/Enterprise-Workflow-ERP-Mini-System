<?php

namespace App\Support\Notifications;

use App\Enums\AssetStatus;
use App\Models\ApprovalHistory;
use App\Models\GoodsReceipt;
use App\Models\Notification;
use App\Models\User;
use App\Models\WorkflowRequest;
use App\Services\Notifications\NotificationContentBuilder;

class NotificationPresenter
{
    /** @var array<int, NotificationContent> */
    private array $displayCache = [];

    public function __construct(private NotificationContentBuilder $contentBuilder) {}

    public function display(Notification $notification): NotificationContent
    {
        if (isset($this->displayCache[$notification->id])) {
            return $this->displayCache[$notification->id];
        }

        $stored = new NotificationContent(
            title: $notification->title,
            message: $notification->message,
            data: $notification->data ?? [],
        );

        if (data_get($notification->data, 'form_name') || data_get($notification->data, 'details')) {
            return $this->displayCache[$notification->id] = $stored;
        }

        return $this->displayCache[$notification->id] = $this->legacyContent($notification) ?? $stored;
    }

    public function actionUrl(Notification $notification, User $user): ?string
    {
        $data = $this->display($notification)->data + ($notification->data ?? []);
        $destination = data_get($data, 'destination');
        $action = data_get($data, 'action');

        if ($destination === 'assets' || $action === 'review_ready_assets') {
            return $user->hasRole(['asset_manager', 'admin'])
                ? route('assets.index', ['status' => AssetStatus::Available->value])
                : null;
        }

        if (
            data_get($data, 'purchase_request_id')
            && in_array($action, ['create_purchase_order', 'fulfill_from_stock'], true)
        ) {
            return $user->hasRole(['procurement', 'asset_manager', 'admin'])
                ? route('procurement.purchase-requests.show', data_get($data, 'purchase_request_id'))
                : null;
        }

        if (! data_get($data, 'request_id')) {
            return null;
        }

        if ($destination === 'approval' || $action === 'pending_approval') {
            return $user->hasRole(['manager', 'hr', 'procurement', 'finance', 'director', 'admin'])
                ? route('manager.approvals.show', data_get($data, 'request_id'))
                : null;
        }

        if (
            $destination === 'employee_request'
            || (int) data_get($data, 'requester_id') === $user->id
            || in_array($action, ['approved', 'rejected', 'returned', 'stock_fulfilled'], true)
        ) {
            return $user->hasRole(['employee', 'admin'])
                ? route('employee.requests.show', data_get($data, 'request_id'))
                : null;
        }

        return null;
    }

    public function actionLabel(Notification $notification): string
    {
        $data = $this->display($notification)->data + ($notification->data ?? []);
        $action = (string) data_get($data, 'action');
        $key = 'ui.notification_actions.'.$action;

        return trans()->has($key) ? __($key) : __('ui.open_related_request');
    }

    private function legacyContent(Notification $notification): ?NotificationContent
    {
        if ($notification->type === Notification::TYPE_ASSETS_READY) {
            $receiptId = data_get($notification->data, 'goods_receipt_id');
            $receipt = $receiptId ? GoodsReceipt::with(['purchaseOrder', 'warehouse'])->find($receiptId) : null;

            return $receipt
                ? $this->contentBuilder->assetsReady($receipt, (int) data_get($notification->data, 'asset_count', 0))
                : null;
        }

        $requestId = data_get($notification->data, 'request_id');
        if (! $requestId) {
            return null;
        }

        $workflowRequest = WorkflowRequest::query()
            ->with([
                'creator',
                'formTemplate.fields',
                'values.field',
                'purchaseRequest.items',
            ])
            ->find($requestId);

        if (! $workflowRequest) {
            return null;
        }

        return match ($notification->type) {
            Notification::TYPE_REQUEST_SUBMITTED => $this->contentBuilder->pendingApproval($workflowRequest, 'submitted'),
            Notification::TYPE_REQUEST_APPROVED => $this->contentBuilder->pendingApproval($workflowRequest, 'forwarded'),
            Notification::TYPE_REQUEST_COMPLETED => $this->contentBuilder->approved(
                $workflowRequest,
                $this->historyActor($workflowRequest, 'approve') ?? $workflowRequest->creator,
            ),
            Notification::TYPE_REQUEST_REJECTED => $this->contentBuilder->rejected(
                $workflowRequest,
                $this->historyActor($workflowRequest, 'reject') ?? $workflowRequest->creator,
                $this->historyComment($workflowRequest, 'reject'),
            ),
            Notification::TYPE_REQUEST_RETURNED => $this->contentBuilder->returned(
                $workflowRequest,
                $this->historyActor($workflowRequest, 'return') ?? $workflowRequest->creator,
                $this->historyComment($workflowRequest, 'return'),
            ),
            Notification::TYPE_PURCHASE_REQUEST_READY => $workflowRequest->purchaseRequest
                ? $this->contentBuilder->purchaseRequestReady($workflowRequest, $workflowRequest->purchaseRequest)
                : null,
            Notification::TYPE_PURCHASE_REQUEST_STOCK_READY => $workflowRequest->purchaseRequest
                ? $this->contentBuilder->purchaseRequestStockReady($workflowRequest, $workflowRequest->purchaseRequest)
                : null,
            Notification::TYPE_PURCHASE_REQUEST_STOCK_FULFILLED => $workflowRequest->purchaseRequest
                ? $this->contentBuilder->purchaseRequestStockFulfilled($workflowRequest, $workflowRequest->purchaseRequest)
                : null,
            default => null,
        };
    }

    private function historyActor(WorkflowRequest $workflowRequest, string $action): ?User
    {
        return ApprovalHistory::query()
            ->with('actor')
            ->where('request_id', $workflowRequest->id)
            ->where('action', $action)
            ->latest('id')
            ->first()?->actor;
    }

    private function historyComment(WorkflowRequest $workflowRequest, string $action): string
    {
        return (string) (ApprovalHistory::query()
            ->where('request_id', $workflowRequest->id)
            ->where('action', $action)
            ->latest('id')
            ->value('comment') ?: '—');
    }
}
