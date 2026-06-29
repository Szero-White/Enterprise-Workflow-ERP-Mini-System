<?php

namespace App\Services;

use App\Models\ApprovalHistory;
use App\Models\User;
use App\Models\WorkflowRequest;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    public function __construct(private AuditLogService $auditLogService)
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

            $this->auditLogService->log('request.approved', $workflowRequest, $old, $workflowRequest->fresh()->toArray());

            return $workflowRequest->fresh();
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
            ]);

            $workflowRequest->update([
                'status' => WorkflowRequest::STATUS_REJECTED,
                'current_step_id' => null,
            ]);

            $this->auditLogService->log('request.rejected', $workflowRequest, $old, $workflowRequest->fresh()->toArray());

            return $workflowRequest->fresh();
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
            ]);

            $workflowRequest->update(['status' => WorkflowRequest::STATUS_RETURNED]);

            $this->auditLogService->log('request.returned', $workflowRequest, $old, $workflowRequest->fresh()->toArray());

            return $workflowRequest->fresh();
        });
    }

    private function ensureCanAct(User $actor, WorkflowRequest $workflowRequest): void
    {
        if ($workflowRequest->status !== WorkflowRequest::STATUS_PENDING) {
            abort(422, 'Don nay khong con o trang thai cho duyet.');
        }

        if (! $workflowRequest->currentStep || ! $workflowRequest->currentStep->canBeApprovedBy($actor)) {
            abort(403, 'Ban khong phai nguoi duyet cua buoc hien tai.');
        }
    }
}
