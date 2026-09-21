<?php

namespace App\Policies;

use App\Enums\LifecycleStatus;
use App\Models\User;
use App\Models\WorkflowTemplate;
use App\Services\Workflow\WorkflowLifecycleService;

class WorkflowTemplatePolicy
{
    public function __construct(private WorkflowLifecycleService $lifecycleService) {}

    public function update(User $user, WorkflowTemplate $workflowTemplate): bool
    {
        return $this->isAdmin($user)
            && $this->status($workflowTemplate) === LifecycleStatus::Draft
            && ! $workflowTemplate->is_active
            && ! $workflowTemplate->isLocked();
    }

    public function manageSteps(User $user, WorkflowTemplate $workflowTemplate): bool
    {
        return $this->update($user, $workflowTemplate);
    }

    public function activate(User $user, WorkflowTemplate $workflowTemplate): bool
    {
        return $this->isAdmin($user)
            && $this->status($workflowTemplate) === LifecycleStatus::Draft
            && ! $workflowTemplate->is_active
            && ! $workflowTemplate->isLocked();
    }

    public function deactivate(User $user, WorkflowTemplate $workflowTemplate): bool
    {
        $workflowTemplate->loadMissing('formTemplate');

        return $this->isAdmin($user)
            && $this->status($workflowTemplate) === LifecycleStatus::Draft
            && $workflowTemplate->is_active
            && ! $workflowTemplate->formTemplate?->is_active
            && ! $workflowTemplate->isLocked();
    }

    public function cloneVersion(User $user, WorkflowTemplate $workflowTemplate): bool
    {
        return $this->isAdmin($user)
            && $this->status($workflowTemplate) === LifecycleStatus::Current;
    }

    public function delete(User $user, WorkflowTemplate $workflowTemplate): bool
    {
        return $this->update($user, $workflowTemplate);
    }

    private function status(WorkflowTemplate $workflowTemplate): LifecycleStatus
    {
        $status = $workflowTemplate->getAttribute('lifecycle_status');

        return $status instanceof LifecycleStatus
            ? $status
            : $this->lifecycleService->workflowStatus($workflowTemplate);
    }

    private function isAdmin(User $user): bool
    {
        return $user->is_active && $user->hasRole('admin');
    }
}
