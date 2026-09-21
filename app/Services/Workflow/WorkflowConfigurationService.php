<?php

namespace App\Services\Workflow;

use App\Enums\LifecycleStatus;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowRequest;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use App\Services\DynamicFieldConditionService;
use App\Services\Procurement\PurchaseRequestService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkflowConfigurationService
{
    public function __construct(
        private DynamicFieldConditionService $conditionService,
        private WorkflowLifecycleService $lifecycleService,
    ) {}

    public function ensureFormMutable(FormTemplate $formTemplate): void
    {
        if ($this->lifecycleService->formStatus($formTemplate) !== LifecycleStatus::Draft
            || $formTemplate->is_active
            || $formTemplate->isLocked()) {
            throw ValidationException::withMessages([
                'form_template' => __('messages.form_template_read_only'),
            ]);
        }
    }

    public function ensureWorkflowMutable(WorkflowTemplate $workflowTemplate): void
    {
        if ($this->lifecycleService->workflowStatus($workflowTemplate) !== LifecycleStatus::Draft
            || $workflowTemplate->is_active
            || $workflowTemplate->isLocked()) {
            throw ValidationException::withMessages([
                'workflow_template' => __('messages.workflow_template_read_only'),
            ]);
        }
    }

    public function ensureFormAllowsWorkflowDraft(FormTemplate $formTemplate): void
    {
        if (! in_array($this->lifecycleService->formStatus($formTemplate), [LifecycleStatus::Draft, LifecycleStatus::Current], true)) {
            throw ValidationException::withMessages([
                'form_template' => __('messages.form_template_workflow_read_only'),
            ]);
        }
    }

    public function activateForm(FormTemplate $formTemplate): FormTemplate
    {
        if ($this->lifecycleService->formStatus($formTemplate) !== LifecycleStatus::Draft || $formTemplate->is_active) {
            throw ValidationException::withMessages([
                'form_template' => __('messages.form_template_publish_draft_only'),
            ]);
        }

        return DB::transaction(function () use ($formTemplate): FormTemplate {
            $versions = FormTemplate::query()
                ->where('code', $formTemplate->code)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $formTemplate = $versions->firstWhere('id', $formTemplate->id) ?? FormTemplate::query()->findOrFail($formTemplate->id);

            if (! $formTemplate->fields()->exists()) {
                throw ValidationException::withMessages([
                    'form_template' => __('messages.form_template_requires_fields'),
                ]);
            }

            $activeWorkflow = $formTemplate->activeWorkflow()->withCount('steps')->first();
            if (! $activeWorkflow || $activeWorkflow->steps_count < 1) {
                $workflowToPublish = $formTemplate->workflows()
                    ->whereHas('steps')
                    ->orderByDesc('version')
                    ->orderByDesc('id')
                    ->first();

                if (! $workflowToPublish) {
                    throw ValidationException::withMessages([
                        'form_template' => __('messages.form_template_requires_ready_workflow'),
                    ]);
                }

                $activeWorkflow = $this->activateWorkflow($workflowToPublish);
            }

            $this->conditionService->ensureTemplateConditionsValid($formTemplate);

            FormTemplate::query()
                ->whereIn('id', $versions->pluck('id'))
                ->where('id', '!=', $formTemplate->id)
                ->update(['is_active' => false]);

            $formTemplate->update(['is_active' => true]);
            $this->lifecycleService->retireSupersededWorkflowsForFormFamily($formTemplate);

            return $formTemplate->fresh();
        });
    }

    public function deactivateForm(FormTemplate $formTemplate): FormTemplate
    {
        if ($this->lifecycleService->formStatus($formTemplate) !== LifecycleStatus::Current || ! $formTemplate->is_active) {
            throw ValidationException::withMessages([
                'form_template' => __('messages.form_template_deactivate_current_only'),
            ]);
        }

        return DB::transaction(function () use ($formTemplate): FormTemplate {
            $formTemplate->update(['is_active' => false]);
            $this->lifecycleService->retireEligibleWorkflowsForForm($formTemplate->fresh());

            return $formTemplate->fresh();
        });
    }

    public function activateWorkflow(WorkflowTemplate $workflowTemplate): WorkflowTemplate
    {
        if ($this->lifecycleService->workflowStatus($workflowTemplate) !== LifecycleStatus::Draft
            || $workflowTemplate->is_active
            || $workflowTemplate->isLocked()) {
            throw ValidationException::withMessages([
                'workflow_template' => __('messages.workflow_template_publish_draft_only'),
            ]);
        }

        return DB::transaction(function () use ($workflowTemplate): WorkflowTemplate {
            $versions = WorkflowTemplate::query()
                ->where('form_template_id', $workflowTemplate->form_template_id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $workflowTemplate = $versions->firstWhere('id', $workflowTemplate->id) ?? WorkflowTemplate::query()->findOrFail($workflowTemplate->id);

            if (! $workflowTemplate->steps()->exists()) {
                throw ValidationException::withMessages([
                    'workflow_template' => __('messages.workflow_template_requires_steps'),
                ]);
            }

            $workflowTemplate->loadMissing('formTemplate');
            if ($this->lifecycleService->formStatus($workflowTemplate->formTemplate) === LifecycleStatus::Legacy) {
                throw ValidationException::withMessages([
                    'workflow_template' => __('messages.workflow_legacy_reactivate_forbidden'),
                ]);
            }

            $this->ensurePurchaseRequestManagerGate($workflowTemplate);

            WorkflowTemplate::query()
                ->whereIn('id', $versions->pluck('id'))
                ->where('id', '!=', $workflowTemplate->id)
                ->update(['is_active' => false]);

            $workflowTemplate->update(['is_active' => true]);

            return $workflowTemplate->fresh();
        });
    }

    private function ensurePurchaseRequestManagerGate(WorkflowTemplate $workflowTemplate): void
    {
        $workflowTemplate->loadMissing(['formTemplate', 'steps.approverRole']);

        if ($workflowTemplate->formTemplate?->code !== PurchaseRequestService::FORM_CODE) {
            return;
        }

        $firstStep = $workflowTemplate->steps->sortBy('step_order')->first();

        if (
            ! $firstStep
            || $firstStep->approver_type !== WorkflowStep::APPROVER_ROLE
            || $firstStep->approverRole?->key !== 'manager'
        ) {
            throw ValidationException::withMessages([
                'workflow_template' => __('messages.purchase_request_workflow_requires_manager_first'),
            ]);
        }

        $managerRoleId = Role::query()->where('key', 'manager')->value('id');
        $hasActiveManager = $managerRoleId
            && User::query()->where('role_id', $managerRoleId)->where('is_active', true)->exists();

        if (! $hasActiveManager) {
            throw ValidationException::withMessages([
                'workflow_template' => __('messages.purchase_request_workflow_requires_active_manager'),
            ]);
        }
    }

    public function deactivateWorkflow(WorkflowTemplate $workflowTemplate): WorkflowTemplate
    {
        if ($workflowTemplate->formTemplate?->is_active) {
            throw ValidationException::withMessages([
                'workflow_template' => __('messages.workflow_deactivate_active_form_forbidden'),
            ]);
        }

        $hasOpenRequests = $workflowTemplate->requests()
            ->whereIn('status', [WorkflowRequest::STATUS_PENDING, WorkflowRequest::STATUS_RETURNED])
            ->exists();

        if ($hasOpenRequests) {
            throw ValidationException::withMessages([
                'workflow_template' => __('messages.workflow_deactivate_open_requests_forbidden'),
            ]);
        }

        $workflowTemplate->update(['is_active' => false]);

        return $workflowTemplate->fresh();
    }

    public function cloneFormVersion(FormTemplate $source, User $actor): FormTemplate
    {
        if ($this->lifecycleService->formStatus($source) !== LifecycleStatus::Current) {
            throw ValidationException::withMessages([
                'form_template' => __('messages.form_template_clone_current_only'),
            ]);
        }

        return DB::transaction(function () use ($source, $actor): FormTemplate {
            $source = FormTemplate::query()->lockForUpdate()->findOrFail($source->id);
            $source->load(['fields', 'workflows.steps']);

            $versions = FormTemplate::query()
                ->where('code', $source->code)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $nextVersion = ((int) $versions->max('version')) + 1;

            $clone = FormTemplate::create([
                'name' => $source->name,
                'code' => $source->code,
                'version' => $nextVersion,
                'description' => $source->description,
                'submission_type' => $source->submission_type,
                'is_active' => false,
                'locked_at' => null,
                'created_by' => $actor->id,
            ]);

            $source->fields->each(function (FormField $field) use ($clone): void {
                $clone->fields()->create($field->only([
                    'label', 'field_key', 'field_type', 'is_required',
                    'condition_field_key', 'condition_operator', 'condition_value',
                    'options', 'sort_order',
                ]));
            });

            $sourceWorkflow = $source->workflows->firstWhere('is_active', true) ?? $source->workflows->sortByDesc('version')->first();
            if ($sourceWorkflow) {
                $workflowClone = $clone->workflows()->create([
                    'name' => $sourceWorkflow->name,
                    'version' => 1,
                    'is_active' => false,
                    'locked_at' => null,
                    'created_by' => $actor->id,
                ]);

                $sourceWorkflow->steps->each(function (WorkflowStep $step) use ($workflowClone): void {
                    $workflowClone->steps()->create($step->only([
                        'step_name',
                        'step_order',
                        'approver_type',
                        'approver_role_id',
                        'approver_department_id',
                        'approver_user_id',
                    ]));
                });
            }

            return $clone->fresh(['fields', 'workflows.steps']);
        });
    }

    public function cloneWorkflowVersion(WorkflowTemplate $source, User $actor): WorkflowTemplate
    {
        if ($this->lifecycleService->workflowStatus($source) !== LifecycleStatus::Current) {
            throw ValidationException::withMessages([
                'workflow_template' => __('messages.workflow_template_clone_current_only'),
            ]);
        }

        return DB::transaction(function () use ($source, $actor): WorkflowTemplate {
            FormTemplate::query()->whereKey($source->form_template_id)->lockForUpdate()->firstOrFail();
            $source = WorkflowTemplate::query()->findOrFail($source->id);
            $source->load('steps');

            $versions = WorkflowTemplate::query()
                ->where('form_template_id', $source->form_template_id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $nextVersion = ((int) $versions->max('version')) + 1;

            $clone = WorkflowTemplate::create([
                'form_template_id' => $source->form_template_id,
                'name' => $source->name,
                'version' => $nextVersion,
                'is_active' => false,
                'locked_at' => null,
                'created_by' => $actor->id,
            ]);

            $source->steps->each(function (WorkflowStep $step) use ($clone): void {
                $clone->steps()->create($step->only([
                    'step_name',
                    'step_order',
                    'approver_type',
                    'approver_role_id',
                    'approver_department_id',
                    'approver_user_id',
                ]));
            });

            return $clone->fresh('steps');
        });
    }
}
