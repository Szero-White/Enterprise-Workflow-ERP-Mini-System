<?php

namespace App\Services\Workflow;

use App\Enums\LifecycleStatus;
use App\Models\FormTemplate;
use App\Models\WorkflowRequest;
use App\Models\WorkflowTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WorkflowLifecycleService
{
    /**
     * Resolve lifecycle states for a collection without N+1 sibling/request lookups.
     */
    public function decorateWorkflows(Collection $workflows): Collection
    {
        $workflows->each(fn (WorkflowTemplate $workflow) => $workflow->loadMissing('formTemplate'));
        $this->hydrateOpenRequestCounts($workflows);

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
        $workflow->loadMissing('formTemplate');
        $form = $workflow->formTemplate;

        if (! $form) {
            return LifecycleStatus::Inactive;
        }

        $formStatus = $this->formStatus($form, $activeVersions);

        // A workflow attached to a draft form is still draft configuration even when
        // it is selected as the workflow that will be published with that form.
        if ($formStatus === LifecycleStatus::Draft && ! $this->hasAnyRequests($workflow)) {
            return LifecycleStatus::Draft;
        }

        if ($formStatus === LifecycleStatus::Current && $workflow->is_active) {
            return LifecycleStatus::Current;
        }

        // A superseded workflow may still own in-flight requests even after a newer
        // workflow/form version becomes current. It remains legacy until those requests finish.
        if ($this->hasOpenRequests($workflow)) {
            return LifecycleStatus::Legacy;
        }

        // Current forms may have an unpublished workflow draft for the next approval version.
        if ($formStatus === LifecycleStatus::Current && ! $workflow->is_active && ! $this->hasAnyRequests($workflow)) {
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
        if ($this->formHasRequests($form)) {
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

        if ($this->hasOpenRequests($workflow, forceQuery: true)) {
            return false;
        }

        $workflow->update(['is_active' => false]);
        $workflow->setAttribute('open_requests_count', 0);

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

    public function retireEligibleWorkflowsForForm(FormTemplate $form): int
    {
        return $form->workflows()
            ->where('is_active', true)
            ->get()
            ->sum(fn (WorkflowTemplate $workflow): int => $this->retireIfEligible($workflow) ? 1 : 0);
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

    private function hydrateOpenRequestCounts(Collection $workflows): void
    {
        $missingIds = $workflows
            ->filter(fn (WorkflowTemplate $workflow) => ! array_key_exists('open_requests_count', $workflow->getAttributes()))
            ->pluck('id')
            ->filter()
            ->values();

        if ($missingIds->isEmpty()) {
            return;
        }

        $counts = WorkflowRequest::query()
            ->select('workflow_template_id', DB::raw('COUNT(*) as aggregate'))
            ->whereIn('workflow_template_id', $missingIds)
            ->whereIn('status', [WorkflowRequest::STATUS_PENDING, WorkflowRequest::STATUS_RETURNED])
            ->groupBy('workflow_template_id')
            ->pluck('aggregate', 'workflow_template_id');

        $workflows->each(function (WorkflowTemplate $workflow) use ($counts): void {
            if (! array_key_exists('open_requests_count', $workflow->getAttributes())) {
                $workflow->setAttribute('open_requests_count', (int) ($counts[$workflow->id] ?? 0));
            }
        });
    }

    private function hasOpenRequests(WorkflowTemplate $workflow, bool $forceQuery = false): bool
    {
        if (! $forceQuery && array_key_exists('open_requests_count', $workflow->getAttributes())) {
            return (int) $workflow->getAttribute('open_requests_count') > 0;
        }

        return $workflow->requests()
            ->whereIn('status', [WorkflowRequest::STATUS_PENDING, WorkflowRequest::STATUS_RETURNED])
            ->exists();
    }

    private function hasAnyRequests(WorkflowTemplate $workflow): bool
    {
        if (array_key_exists('requests_count', $workflow->getAttributes())) {
            return (int) $workflow->getAttribute('requests_count') > 0;
        }

        return $workflow->requests()->exists();
    }

    private function formHasRequests(FormTemplate $form): bool
    {
        if (array_key_exists('requests_count', $form->getAttributes())) {
            return (int) $form->getAttribute('requests_count') > 0;
        }

        return $form->requests()->exists();
    }
}
