<?php

namespace App\Services;

use App\Models\ApprovalHistory;
use App\Models\Attachment;
use App\Models\FormTemplate;
use App\Models\Notification;
use App\Models\RequestValue;
use App\Models\User;
use App\Models\WorkflowRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DynamicRequestService
{
    public function __construct(
        private AuditLogService $auditLogService,
        private NotificationService $notificationService
    )
    {
    }

    public function create(User $user, FormTemplate $formTemplate, Request $httpRequest): WorkflowRequest
    {
        return DB::transaction(function () use ($user, $formTemplate, $httpRequest) {
            $workflowTemplate = $formTemplate->activeWorkflow()->with('steps')->firstOrFail();
            $firstStep = $workflowTemplate->steps()->orderBy('step_order')->firstOrFail();

            $workflowRequest = WorkflowRequest::create([
                'request_code' => $this->generateRequestCode($formTemplate->code),
                'form_template_id' => $formTemplate->id,
                'workflow_template_id' => $workflowTemplate->id,
                'current_step_id' => $firstStep->id,
                'created_by' => $user->id,
                'status' => WorkflowRequest::STATUS_PENDING,
                'submitted_at' => now(),
            ]);

            $this->saveValuesAndFiles($workflowRequest, $formTemplate, $httpRequest, $user);

            ApprovalHistory::create([
                'request_id' => $workflowRequest->id,
                'workflow_step_id' => $firstStep->id,
                'actor_id' => $user->id,
                'action' => 'submit',
                'comment' => 'Nhân viên đã gửi đơn.',
                'acted_at' => now(),
            ]);

            $this->auditLogService->log('request.created', $workflowRequest, null, $workflowRequest->toArray());
            $this->notificationService->notifyCurrentApprovers($workflowRequest, Notification::TYPE_REQUEST_SUBMITTED);

            return $workflowRequest;
        });
    }

    public function updateReturned(User $user, WorkflowRequest $workflowRequest, Request $httpRequest): WorkflowRequest
    {
        return DB::transaction(function () use ($user, $workflowRequest, $httpRequest) {
            if ($workflowRequest->created_by !== $user->id || $workflowRequest->status !== WorkflowRequest::STATUS_RETURNED) {
                abort(403, __('messages.returned_request_owner_only'));
            }

            $old = $workflowRequest->load('values')->toArray();
            $workflowRequest->update([
                'status' => WorkflowRequest::STATUS_PENDING,
                'submitted_at' => now(),
            ]);

            $this->saveValuesAndFiles($workflowRequest, $workflowRequest->formTemplate, $httpRequest, $user, true);

            ApprovalHistory::create([
                'request_id' => $workflowRequest->id,
                'workflow_step_id' => $workflowRequest->current_step_id,
                'actor_id' => $user->id,
                'action' => 'resubmit',
                'comment' => 'Nhân viên đã gửi lại đơn bị trả về.',
                'acted_at' => now(),
            ]);

            $this->auditLogService->log('request.resubmitted', $workflowRequest, $old, $workflowRequest->fresh('values')->toArray());
            $this->notificationService->notifyCurrentApprovers($workflowRequest, Notification::TYPE_REQUEST_SUBMITTED);

            return $workflowRequest->fresh();
        });
    }

    private function saveValuesAndFiles(WorkflowRequest $workflowRequest, FormTemplate $formTemplate, Request $httpRequest, User $user, bool $replace = false): void
    {
        if ($replace) {
            $workflowRequest->values()->delete();
        }

        foreach ($formTemplate->fields as $field) {
            $value = null;

            if ($field->field_type === 'file') {
                if ($httpRequest->hasFile($field->field_key)) {
                    $file = $httpRequest->file($field->field_key);
                    $path = $file->store('request_attachments', 'public');
                    $value = $path;

                    Attachment::create([
                        'request_id' => $workflowRequest->id,
                        'form_field_id' => $field->id,
                        'original_name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'uploaded_by' => $user->id,
                    ]);
                }
            } else {
                $value = $httpRequest->input($field->field_key);
            }

            RequestValue::updateOrCreate(
                ['request_id' => $workflowRequest->id, 'form_field_id' => $field->id],
                ['field_key' => $field->field_key, 'value' => $value]
            );
        }
    }

    private function generateRequestCode(string $formCode): string
    {
        return strtoupper($formCode).'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }
}
