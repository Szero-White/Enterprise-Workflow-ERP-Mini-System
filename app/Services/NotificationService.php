<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\WorkflowRequest;
use App\Models\WorkflowStep;
use Illuminate\Support\Collection;

class NotificationService
{
    public function __construct(private RealtimeNotificationService $realtimeNotificationService)
    {
    }

    public function createForUser(User|int $recipient, string $title, string $message, string $type, array $data = []): Notification
    {
        $notification = Notification::create([
            'user_id' => $recipient instanceof User ? $recipient->id : $recipient,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
        ]);

        $this->realtimeNotificationService->send($notification);

        return $notification;
    }

    public function notifyCurrentApprovers(WorkflowRequest $workflowRequest, string $type = Notification::TYPE_REQUEST_SUBMITTED): void
    {
        $workflowRequest->loadMissing(['currentStep', 'creator', 'formTemplate']);

        if (! $workflowRequest->currentStep) {
            return;
        }

        $this->approverUsers($workflowRequest->currentStep)
            ->reject(fn (User $user) => $user->id === $workflowRequest->created_by)
            ->each(function (User $user) use ($workflowRequest, $type): void {
                $this->createForUser(
                    $user,
                    'Có đơn đang chờ duyệt',
                    sprintf('Đơn %s đang chờ bạn xử lý.', $workflowRequest->request_code),
                    $type,
                    $this->requestPayload($workflowRequest, 'pending_approval')
                );
            });
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
            $this->requestPayload($workflowRequest, $action)
        );
    }

    public function markAsReadForUser(Notification $notification, User $user): void
    {
        abort_if($notification->user_id !== $user->id, 403);

        $notification->markAsRead();
    }

    public function markAllAsReadForUser(User $user): void
    {
        Notification::forUser($user)
            ->unread()
            ->update(['read_at' => now()]);
    }

    private function approverUsers(WorkflowStep $step): Collection
    {
        if ($step->approver_user_id) {
            return User::whereKey($step->approver_user_id)
                ->where('is_active', true)
                ->get();
        }

        if (! $step->approver_role_id && ! $step->approver_department_id) {
            return collect();
        }

        $query = User::query()->where('is_active', true);

        $query->where(function ($query) use ($step): void {
            if ($step->approver_role_id) {
                $query->orWhere('role_id', $step->approver_role_id);
            }

            if ($step->approver_department_id) {
                $query->orWhere('department_id', $step->approver_department_id);
            }
        });

        return $query->get()->unique('id')->values();
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
