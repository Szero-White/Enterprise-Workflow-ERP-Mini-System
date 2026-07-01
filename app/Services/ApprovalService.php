<?php

namespace App\Services;

use App\Models\ApprovalHistory;
use App\Models\Notification;
use App\Models\User;
use App\Models\WorkflowRequest;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    public function __construct(
        private AuditLogService $auditLogService,
        private NotificationService $notificationService
    )
    {
    }

    public function approve(User $actor, WorkflowRequest $workflowRequest, ?string $comment = null): WorkflowRequest
    {
        return DB::transaction(function () use ($actor, $workflowRequest, $comment) {
            $this->ensureCanAct($actor, $workflowRequest);
            $old = $workflowRequest->toArray();
            $currentStep = $workflowRequest->currentStep;

            ApprovalHistory::create([
                'request_id' => $workflowRequest->id,
                'workflow_step_id' => $currentStep?->id,
                'actor_id' => $actor->id,
                'action' => 'approve',
                'comment' => $comment,
                'acted_at' => now(),
            ]);

            $nextStep = $workflowRequest->workflowTemplate
                ->steps()
                ->where('step_order', '>', $currentStep->step_order)
                ->orderBy('step_order')
                ->first();

            if ($nextStep) {
                $workflowRequest->update([
                    'current_step_id' => $nextStep->id,
                    'status' => WorkflowRequest::STATUS_PENDING,
                ]);
            } else {
                $workflowRequest->update([
                    'current_step_id' => null,
                    'status' => WorkflowRequest::STATUS_APPROVED,
                ]);
            }

            $freshRequest = $workflowRequest->fresh(['currentStep', 'creator', 'formTemplate']);
            $this->auditLogService->log('request.approved', $workflowRequest, $old, $freshRequest->toArray());

            if ($nextStep) {
                $this->notificationService->notifyCurrentApprovers($freshRequest, Notification::TYPE_REQUEST_APPROVED);
            } else {
                $this->notificationService->notifyCreator(
                    $freshRequest,
                    'Đơn đã được duyệt',
                    sprintf('Đơn %s của bạn đã được duyệt hoàn tất.', $freshRequest->request_code),
                    Notification::TYPE_REQUEST_COMPLETED,
                    'approved'
                );
            }

            return $freshRequest;
        });
    }

    public function reject(User $actor, WorkflowRequest $workflowRequest, ?string $comment = null): WorkflowRequest
    {
        return DB::transaction(function () use ($actor, $workflowRequest, $comment) {
            $this->ensureCanAct($actor, $workflowRequest);
            $old = $workflowRequest->toArray();

            ApprovalHistory::create([
                'request_id' => $workflowRequest->id,
                'workflow_step_id' => $workflowRequest->current_step_id,
                'actor_id' => $actor->id,
                'action' => 'reject',
                'comment' => $comment,
                'acted_at' => now(),
            ]);

            $workflowRequest->update([
                'status' => WorkflowRequest::STATUS_REJECTED,
                'current_step_id' => null,
            ]);

            $freshRequest = $workflowRequest->fresh(['creator', 'formTemplate']);
            $this->auditLogService->log('request.rejected', $workflowRequest, $old, $freshRequest->toArray());
            $this->notificationService->notifyCreator(
                $freshRequest,
                'Đơn đã bị từ chối',
                sprintf('Đơn %s của bạn đã bị từ chối.', $freshRequest->request_code),
                Notification::TYPE_REQUEST_REJECTED,
                'rejected'
            );

            return $freshRequest;
        });
    }

    public function returnToEmployee(User $actor, WorkflowRequest $workflowRequest, ?string $comment = null): WorkflowRequest
    {
        return DB::transaction(function () use ($actor, $workflowRequest, $comment) {
            $this->ensureCanAct($actor, $workflowRequest);
            $old = $workflowRequest->toArray();

            ApprovalHistory::create([
                'request_id' => $workflowRequest->id,
                'workflow_step_id' => $workflowRequest->current_step_id,
                'actor_id' => $actor->id,
                'action' => 'return',
                'comment' => $comment,
                'acted_at' => now(),
            ]);

            $workflowRequest->update(['status' => WorkflowRequest::STATUS_RETURNED]);

            $freshRequest = $workflowRequest->fresh(['creator', 'formTemplate']);
            $this->auditLogService->log('request.returned', $workflowRequest, $old, $freshRequest->toArray());
            $this->notificationService->notifyCreator(
                $freshRequest,
                'Đơn đã được trả về',
                sprintf('Đơn %s của bạn đã được trả về để chỉnh sửa.', $freshRequest->request_code),
                Notification::TYPE_REQUEST_RETURNED,
                'returned'
            );

            return $freshRequest;
        });
    }

    public function ensureCanAct(User $actor, WorkflowRequest $workflowRequest): void
    {
        if ($workflowRequest->status !== WorkflowRequest::STATUS_PENDING) {
            abort(422, __('messages.request_not_pending'));
        }

        if (! $workflowRequest->currentStep || ! $workflowRequest->currentStep->canBeApprovedBy($actor)) {
            abort(403, __('messages.not_current_approver'));
        }
    }
}
