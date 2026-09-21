<?php

namespace App\Policies;

use App\Enums\LifecycleStatus;
use App\Models\FormTemplate;
use App\Models\User;
use App\Services\Workflow\WorkflowLifecycleService;

class FormTemplatePolicy
{
    public function __construct(private WorkflowLifecycleService $lifecycleService) {}

    public function update(User $user, FormTemplate $formTemplate): bool
    {
        return $this->isAdmin($user)
            && $this->status($formTemplate) === LifecycleStatus::Draft
            && ! $formTemplate->is_active
            && ! $formTemplate->isLocked();
    }

    public function manageFields(User $user, FormTemplate $formTemplate): bool
    {
        return $this->update($user, $formTemplate);
    }

    public function publish(User $user, FormTemplate $formTemplate): bool
    {
        return $this->isAdmin($user)
            && $this->status($formTemplate) === LifecycleStatus::Draft
            && ! $formTemplate->is_active;
    }

    public function deactivate(User $user, FormTemplate $formTemplate): bool
    {
        return $this->isAdmin($user)
            && $this->status($formTemplate) === LifecycleStatus::Current
            && $formTemplate->is_active;
    }

    public function cloneVersion(User $user, FormTemplate $formTemplate): bool
    {
        return $this->isAdmin($user)
            && $this->status($formTemplate) === LifecycleStatus::Current;
    }

    public function delete(User $user, FormTemplate $formTemplate): bool
    {
        return $this->update($user, $formTemplate);
    }

    public function createWorkflow(User $user, FormTemplate $formTemplate): bool
    {
        return $this->isAdmin($user)
            && in_array($this->status($formTemplate), [LifecycleStatus::Draft, LifecycleStatus::Current], true);
    }

    private function status(FormTemplate $formTemplate): LifecycleStatus
    {
        $status = $formTemplate->getAttribute('lifecycle_status');

        return $status instanceof LifecycleStatus
            ? $status
            : $this->lifecycleService->formStatus($formTemplate);
    }

    private function isAdmin(User $user): bool
    {
        return $user->is_active && $user->hasRole('admin');
    }
}
