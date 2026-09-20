<?php

namespace App\Services\Workflow;

use App\Enums\LifecycleStatus;
use App\Models\FormTemplate;
use App\Models\WorkflowRequest;
use App\Models\WorkflowTemplate;
use Illuminate\Support\Collection;

class WorkflowLifecycleService
{
    /**
     * Resolve lifecycle states for a collection without N+1 sibling lookups.
     */
    public function decorateWorkflows(Collection $workflows): Collection
    {
        $workflows->each(fn (WorkflowTemplate $workflow) => $workflow->loadMissing('formTemplate'));
        $forms = $workflows->pluck('formTemplate')->filter();
        $activeVersions = $this->activeFormVersions($forms->pluck('code')->filter()->unique()->values());

        return $workflows->each(function (WorkflowTemplate $workflow) use ($activeVersions): void {
            $workflow->setAttribute('lifecycle_status', $this->workflowStatus($workflow, $activeVersions));
        });
    }

    public function decorateForms(Collection $forms): Collection
    {
        $activeVersions = $this->activeFormVersions($forms->pluck('code')->filter()->unique()->values());

        return $forms->each(function (FormTemplate $form) use ($activeVersions): void {
            $form->setAttribute('lifecycle_status', $this->formStatus($form, $activeVersions));
        });
    }

    public function workflowStatus(WorkflowTemplate $workflow, ?Collection $activeVersions = null): LifecycleStatus
    {
        $form = $workflow->formTemplate;
        if (! $form) {
            return LifecycleStatus::Inactive;
        }

        $formStatus = $this->formStatus($form, $activeVersions);

        if ($formStatus === LifecycleStatus::Current && $workflow->is_active) {
            return LifecycleStatus::Current;
        }

        if ($formStatus === LifecycleStatus::Legacy && $workflow->is_active) {
            return LifecycleStatus::Legacy;
        }

        if ($formStatus === LifecycleStatus::Draft && ! $workflow->is_active) {
            return LifecycleStatus::Draft;
        }

        if ($formStatus === LifecycleStatus::Current && ! $workflow->is_active && ! $workflow->requests()->exists()) {
            return LifecycleStatus::Draft;
        }

        return LifecycleStatus::Inactive;
    }

    public function formStatus(FormTemplate $form, ?Collection $activeVersions = null): LifecycleStatus
    {
        if ($form->is_active) {
            return LifecycleStatus::Current;
        }

        $activeVersions ??= $this->activeFormVersions(collect([$form->code]));
        $activeVersion = $activeVersions->get($form->code);

        if ($activeVersion !== null && (int) $form->version < (int) $activeVersion) {
            return LifecycleStatus::Legacy;
        }

        // A previously-used form that was explicitly deactivated is not a draft.
        if ($form->requests()->exists()) {
            return LifecycleStatus::Inactive;
        }

        return LifecycleStatus::Draft;
    }

    public function retireIfEligible(WorkflowTemplate $workflow): bool
    {
        $workflow->loadMissing('formTemplate');

        if (! $workflow->is_active || $workflow->formTemplate?->is_active) {
            return false;
        }

        $hasOpenRequests = $workflow->requests()
            ->whereIn('status', [WorkflowRequest::STATUS_PENDING, WorkflowRequest::STATUS_RETURNED])
            ->exists();

        if ($hasOpenRequests) {
            return false;
        }

        $workflow->update(['is_active' => false]);

        return true;
    }

    public function retireSupersededWorkflowsForFormFamily(FormTemplate $currentForm): int
    {
        $workflows = WorkflowTemplate::query()
            ->where('is_active', true)
            ->whereHas('formTemplate', fn ($query) => $query
                ->where('code', $currentForm->code)
                ->where('id', '!=', $currentForm->id))
            ->get();

        return $workflows->sum(fn (WorkflowTemplate $workflow): int => $this->retireIfEligible($workflow) ? 1 : 0);
    }

    private function activeFormVersions(Collection $codes): Collection
    {
        if ($codes->isEmpty()) {
            return collect();
        }

        return FormTemplate::query()
            ->whereIn('code', $codes)
            ->where('is_active', true)
            ->get(['code', 'version'])
            ->mapWithKeys(fn (FormTemplate $form) => [$form->code => (int) $form->version]);
    }
}
