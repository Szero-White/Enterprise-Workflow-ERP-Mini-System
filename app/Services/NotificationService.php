<?php

namespace App\Services;

use App\Jobs\SendRealtimeNotification;
use App\Models\GoodsReceipt;
use App\Models\Notification;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Models\WorkflowRequest;
use App\Models\WorkflowStep;
use App\Services\Notifications\NotificationContentBuilder;
use App\Support\Notifications\NotificationContent;
use Illuminate\Support\Collection;

class NotificationService
{
    public function __construct(private NotificationContentBuilder $contentBuilder) {}

    public function createForUser(User|int $recipient, string $title, string $message, string $type, array $data = []): Notification
    {
        $notification = Notification::create([
            'user_id' => $recipient instanceof User ? $recipient->id : $recipient,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
        ]);

        if (config('services.node_notification.url')) {
            SendRealtimeNotification::dispatch($notification->id)->afterCommit();
        }

        return $notification;
    }

    public function notifyCurrentApprovers(
        WorkflowRequest $workflowRequest,
        string $type = Notification::TYPE_REQUEST_SUBMITTED,
        string $event = 'submitted',
    ): void {
        $workflowRequest->loadMissing(['currentStep', 'creator', 'formTemplate']);

        if (! $workflowRequest->currentStep) {
            return;
        }

        $content = $this->contentBuilder->pendingApproval($workflowRequest, $event);

        $this->approverUsers($workflowRequest->currentStep)
            ->reject(fn (User $user) => $user->id === $workflowRequest->created_by)
            ->each(fn (User $user) => $this->createFromContent($user, $content, $type));
    }

    public function notifyRequestApproved(WorkflowRequest $workflowRequest, User $actor): void
    {
        $this->notifyWorkflowCreator(
            $workflowRequest,
            $this->contentBuilder->approved($workflowRequest, $actor),
            Notification::TYPE_REQUEST_COMPLETED,
        );
    }

    public function notifyRequestRejected(WorkflowRequest $workflowRequest, User $actor, string $reason): void
    {
        $this->notifyWorkflowCreator(
            $workflowRequest,
            $this->contentBuilder->rejected($workflowRequest, $actor, $reason),
            Notification::TYPE_REQUEST_REJECTED,
        );
    }

    public function notifyRequestReturned(WorkflowRequest $workflowRequest, User $actor, string $reason): void
    {
        $this->notifyWorkflowCreator(
            $workflowRequest,
            $this->contentBuilder->returned($workflowRequest, $actor, $reason),
            Notification::TYPE_REQUEST_RETURNED,
        );
    }

    public function notifyPurchaseRequestReady(WorkflowRequest $workflowRequest, PurchaseRequest $purchaseRequest): void
    {
        $this->notifyRoleWithContent(
            'procurement',
            $this->contentBuilder->purchaseRequestReady($workflowRequest, $purchaseRequest),
            Notification::TYPE_PURCHASE_REQUEST_READY,
        );
    }

    public function notifyPurchaseRequestStockReady(WorkflowRequest $workflowRequest, PurchaseRequest $purchaseRequest): void
    {
        $this->notifyRoleWithContent(
            'asset_manager',
            $this->contentBuilder->purchaseRequestStockReady($workflowRequest, $purchaseRequest),
            Notification::TYPE_PURCHASE_REQUEST_STOCK_READY,
        );
    }

    public function notifyPurchaseRequestStockFulfilled(WorkflowRequest $workflowRequest, PurchaseRequest $purchaseRequest): void
    {
        $this->notifyWorkflowCreator(
            $workflowRequest,
            $this->contentBuilder->purchaseRequestStockFulfilled($workflowRequest, $purchaseRequest),
            Notification::TYPE_PURCHASE_REQUEST_STOCK_FULFILLED,
        );
    }

    public function notifyAssetsReady(GoodsReceipt $receipt, int $assetCount): void
    {
        $this->notifyRoleWithContent(
            'asset_manager',
            $this->contentBuilder->assetsReady($receipt, $assetCount),
            Notification::TYPE_ASSETS_READY,
        );
    }

    public function notifyRoleUsers(
        string $roleKey,
        string $title,
        string $message,
        string $type,
        array $data = []
    ): void {
        User::query()
            ->where('is_active', true)
            ->whereHas('role', fn ($query) => $query->where('key', $roleKey))
            ->get()
            ->each(fn (User $user) => $this->createForUser($user, $title, $message, $type, $data));
    }

    public function notifyCreator(WorkflowRequest $workflowRequest, string $title, string $message, string $type, string $action): void
    {
        $workflowRequest->loadMissing('creator');

        if (! $workflowRequest->creator) {
            return;
        }

        $this->createForUser(
            $workflowRequest->creator,
            $title,
            $message,
            $type,
            $this->requestPayload($workflowRequest, $action),
        );
    }

    public function markAsReadForUser(Notification $notification, User $user): void
    {
        abort_if($notification->user_id !== $user->id, 403);

        $notification->markAsRead();
    }

    public function markAsUnreadForUser(Notification $notification, User $user): void
    {
        abort_if($notification->user_id !== $user->id, 403);

        $notification->markAsUnread();
    }

    public function markAllAsReadForUser(User $user): void
    {
        Notification::forUser($user)
            ->unread()
            ->update(['read_at' => now()]);
    }

    private function notifyWorkflowCreator(WorkflowRequest $workflowRequest, NotificationContent $content, string $type): void
    {
        $workflowRequest->loadMissing('creator');

        if ($workflowRequest->creator) {
            $this->createFromContent($workflowRequest->creator, $content, $type);
        }
    }

    private function notifyRoleWithContent(string $roleKey, NotificationContent $content, string $type): void
    {
        User::query()
            ->where('is_active', true)
            ->whereHas('role', fn ($query) => $query->where('key', $roleKey))
            ->get()
            ->each(fn (User $user) => $this->createFromContent($user, $content, $type));
    }

    private function createFromContent(User|int $recipient, NotificationContent $content, string $type): Notification
    {
        return $this->createForUser(
            $recipient,
            $content->title,
            $content->message,
            $type,
            $content->data,
        );
    }

    private function approverUsers(WorkflowStep $step): Collection
    {
        $query = User::query()->where('is_active', true);

        match ($step->approver_type) {
            WorkflowStep::APPROVER_USER => $query->whereKey($step->approver_user_id),
            WorkflowStep::APPROVER_DEPARTMENT => $query->where('department_id', $step->approver_department_id),
            WorkflowStep::APPROVER_ROLE => $query->where('role_id', $step->approver_role_id),
            default => $query->whereRaw('1 = 0'),
        };

        return $query->get();
    }

    private function requestPayload(WorkflowRequest $workflowRequest, string $action): array
    {
        return [
            'request_id' => $workflowRequest->id,
            'request_code' => $workflowRequest->request_code,
            'status' => $workflowRequest->status,
            'action' => $action,
        ];
    }
}
