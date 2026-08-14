<?php

namespace App\Services;

use App\Models\ApprovalHistory;
use App\Models\Attachment;
use App\Models\FormTemplate;
use App\Models\Notification;
use App\Models\RequestValue;
use App\Models\User;
use App\Models\WorkflowRequest;
use App\Models\WorkflowTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DynamicRequestService
{
    public function __construct(
        private AuditLogService $auditLogService,
        private NotificationService $notificationService
    ) {
    }

    public function create(User $user, FormTemplate $formTemplate, Request $httpRequest): WorkflowRequest
    {
        return DB::transaction(function () use ($user, $formTemplate, $httpRequest) {
            $formTemplate = FormTemplate::query()
                ->with('fields')
                ->lockForUpdate()
                ->findOrFail($formTemplate->id);

            if (! $formTemplate->is_active) {
                throw ValidationException::withMessages([
                    'form_template' => __('messages.form_template_not_available'),
                ]);
            }

            $workflowTemplate = WorkflowTemplate::query()
                ->where('form_template_id', $formTemplate->id)
                ->where('is_active', true)
                ->with('steps')
                ->lockForUpdate()
                ->first();

            $firstStep = $workflowTemplate?->steps->sortBy('step_order')->first();
            if (! $workflowTemplate || ! $firstStep) {
                throw ValidationException::withMessages([
                    'form_template' => __('messages.form_template_not_ready'),
                ]);
            }

            $workflowRequest = WorkflowRequest::create([
                'request_code' => $this->generateRequestCode($formTemplate->code),
                'form_template_id' => $formTemplate->id,
                'workflow_template_id' => $workflowTemplate->id,
                'current_step_id' => $firstStep->id,
                'created_by' => $user->id,
                'status' => WorkflowRequest::STATUS_PENDING,
                'submitted_at' => now(),
            ]);

            $this->lockConfiguration($formTemplate, $workflowTemplate);
            $this->saveValuesAndFiles($workflowRequest, $formTemplate, $httpRequest, $user);

            ApprovalHistory::create([
                'request_id' => $workflowRequest->id,
                'workflow_step_id' => $firstStep->id,
                'actor_id' => $user->id,
                'action' => 'submit',
                'comment' => __('messages.request_history_submitted'),
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
            $workflowRequest = WorkflowRequest::query()
                ->with('formTemplate.fields')
                ->lockForUpdate()
                ->findOrFail($workflowRequest->id);

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
                'comment' => __('messages.request_history_resubmitted'),
                'acted_at' => now(),
            ]);

            $this->auditLogService->log('request.resubmitted', $workflowRequest, $old, $workflowRequest->fresh('values')->toArray());
            $this->notificationService->notifyCurrentApprovers($workflowRequest, Notification::TYPE_REQUEST_SUBMITTED);

            return $workflowRequest->fresh();
        });
    }

    private function lockConfiguration(FormTemplate $formTemplate, WorkflowTemplate $workflowTemplate): void
    {
        $now = now();

        if ($formTemplate->locked_at === null) {
            $formTemplate->forceFill(['locked_at' => $now])->save();
        }

        if ($workflowTemplate->locked_at === null) {
            $workflowTemplate->forceFill(['locked_at' => $now])->save();
        }
    }

    private function saveValuesAndFiles(
        WorkflowRequest $workflowRequest,
        FormTemplate $formTemplate,
        Request $httpRequest,
        User $user,
        bool $replace = false
    ): void {
        foreach ($formTemplate->fields as $field) {
            $value = $httpRequest->input($field->field_key);

            if ($field->field_type === 'file') {
                $existingValue = $workflowRequest->values()
                    ->where('form_field_id', $field->id)
                    ->value('value');
                $value = $existingValue;

                if ($httpRequest->hasFile($field->field_key)) {
                    if ($replace) {
                        $this->deleteAttachmentsForField($workflowRequest, $field->id);
                    }

                    $file = $httpRequest->file($field->field_key);
                    $path = $file->store('request_attachments', 'local');
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
            }

            RequestValue::updateOrCreate(
                ['request_id' => $workflowRequest->id, 'form_field_id' => $field->id],
                ['field_key' => $field->field_key, 'value' => $value]
            );
        }
    }

    private function deleteAttachmentsForField(WorkflowRequest $workflowRequest, int $formFieldId): void
    {
        $attachments = $workflowRequest->attachments()
            ->where('form_field_id', $formFieldId)
            ->get();

        foreach ($attachments as $attachment) {
            Storage::disk('local')->delete($attachment->path);
            $attachment->delete();
        }
    }

    private function generateRequestCode(string $formCode): string
    {
        return strtoupper($formCode).'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }
}
