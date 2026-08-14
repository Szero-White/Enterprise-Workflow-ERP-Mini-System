<?php

namespace App\Services\Workflow;

use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkflowConfigurationService
{
    public function ensureFormMutable(FormTemplate $formTemplate): void
    {
        if ($formTemplate->isLocked()) {
            throw ValidationException::withMessages([
                'form_template' => __('messages.form_template_locked'),
            ]);
        }
    }

    public function ensureWorkflowMutable(WorkflowTemplate $workflowTemplate): void
    {
        if ($workflowTemplate->isLocked()) {
            throw ValidationException::withMessages([
                'workflow_template' => __('messages.workflow_template_locked'),
            ]);
        }
    }

    public function activateForm(FormTemplate $formTemplate): FormTemplate
    {
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
                throw ValidationException::withMessages([
                    'form_template' => __('messages.form_template_requires_active_workflow'),
                ]);
            }

            FormTemplate::query()
                ->whereIn('id', $versions->pluck('id'))
                ->where('id', '!=', $formTemplate->id)
                ->update(['is_active' => false]);

            $formTemplate->update(['is_active' => true]);

            return $formTemplate->fresh();
        });
    }

    public function deactivateForm(FormTemplate $formTemplate): FormTemplate
    {
        $formTemplate->update(['is_active' => false]);

        return $formTemplate->fresh();
    }

    public function activateWorkflow(WorkflowTemplate $workflowTemplate): WorkflowTemplate
    {
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

            WorkflowTemplate::query()
                ->whereIn('id', $versions->pluck('id'))
                ->where('id', '!=', $workflowTemplate->id)
                ->update(['is_active' => false]);

            $workflowTemplate->update(['is_active' => true]);

            return $workflowTemplate->fresh();
        });
    }

    public function deactivateWorkflow(WorkflowTemplate $workflowTemplate): WorkflowTemplate
    {
        if ($workflowTemplate->formTemplate?->is_active) {
            throw ValidationException::withMessages([
                'workflow_template' => __('messages.workflow_deactivate_active_form_forbidden'),
            ]);
        }

        $workflowTemplate->update(['is_active' => false]);

        return $workflowTemplate->fresh();
    }

    public function cloneFormVersion(FormTemplate $source, User $actor): FormTemplate
    {
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
                    'label', 'field_key', 'field_type', 'is_required', 'options', 'sort_order',
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
