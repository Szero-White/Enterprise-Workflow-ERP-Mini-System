<?php

namespace App\Services;

use App\Models\ApprovalHistory;
use App\Models\Notification;
use App\Models\User;
use App\Models\WorkflowRequest;
use App\Services\Workflow\WorkflowTransitionDispatcher;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    public function __construct(
        private AuditLogService $auditLogService,
        private NotificationService $notificationService,
        private WorkflowTransitionDispatcher $workflowTransitionDispatcher
    ) {
    }

    public function approve(User $actor, WorkflowRequest $workflowRequest, ?string $comment = null): WorkflowRequest
    {
        return DB::transaction(function () use ($actor, $workflowRequest, $comment) {
            $workflowRequest = $this->lockRequest($workflowRequest);
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
            $this->workflowTransitionDispatcher->dispatch($freshRequest);

            if ($nextStep) {
                $this->notificationService->notifyCurrentApprovers($freshRequest, Notification::TYPE_REQUEST_APPROVED);
            } else {
                $this->notificationService->notifyCreator(
                    $freshRequest,
                    __('messages.notification_request_approved_title'),
                    __('messages.notification_request_approved_body', ['code' => $freshRequest->request_code]),
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
            $workflowRequest = $this->lockRequest($workflowRequest);
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
            $this->workflowTransitionDispatcher->dispatch($freshRequest);
            $this->notificationService->notifyCreator(
                $freshRequest,
                __('messages.notification_request_rejected_title'),
                __('messages.notification_request_rejected_body', ['code' => $freshRequest->request_code]),
                Notification::TYPE_REQUEST_REJECTED,
                'rejected'
            );

            return $freshRequest;
        });
    }

    public function returnToEmployee(User $actor, WorkflowRequest $workflowRequest, ?string $comment = null): WorkflowRequest
    {
        return DB::transaction(function () use ($actor, $workflowRequest, $comment) {
            $workflowRequest = $this->lockRequest($workflowRequest);
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
            $this->workflowTransitionDispatcher->dispatch($freshRequest);
            $this->notificationService->notifyCreator(
                $freshRequest,
                __('messages.notification_request_returned_title'),
                __('messages.notification_request_returned_body', ['code' => $freshRequest->request_code]),
                Notification::TYPE_REQUEST_RETURNED,
                'returned'
            );

            return $freshRequest;
        });
    }

    private function lockRequest(WorkflowRequest $workflowRequest): WorkflowRequest
    {
        return WorkflowRequest::query()
            ->with(['currentStep', 'workflowTemplate.steps'])
            ->lockForUpdate()
            ->findOrFail($workflowRequest->id);
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
