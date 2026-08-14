<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkflowStepRequest;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use App\Services\AuditLogService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WorkflowStepController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function index(WorkflowTemplate $workflowTemplate): View
    {
        $steps = $workflowTemplate->steps()->with(['approverRole', 'approverDepartment', 'approverUser'])->paginate(20);

        return view('admin.workflow_steps.index', compact('workflowTemplate', 'steps'));
    }

    public function create(WorkflowTemplate $workflowTemplate): View
    {
        return view('admin.workflow_steps.create', $this->viewData($workflowTemplate));
    }

    public function store(WorkflowStepRequest $request, WorkflowTemplate $workflowTemplate): RedirectResponse
    {
        if ($response = $this->guardConfiguration($workflowTemplate)) {
            return $response;
        }

        $data = $request->validated();
        $data['workflow_template_id'] = $workflowTemplate->id;

        $step = WorkflowStep::create($data);
        $this->auditLogService->log('workflow_step.created', $step, null, $step->toArray());

        return redirect()->route('admin.workflow-templates.show', $workflowTemplate)->with('success', __('messages.workflow_step_created'));
    }

    public function edit(WorkflowTemplate $workflowTemplate, WorkflowStep $step): View
    {
        return view('admin.workflow_steps.edit', $this->viewData($workflowTemplate, $step));
    }

    public function update(WorkflowStepRequest $request, WorkflowTemplate $workflowTemplate, WorkflowStep $step): RedirectResponse
    {
        if ($response = $this->guardConfiguration($workflowTemplate)) {
            return $response;
        }

        $old = $step->toArray();
        $step->update($request->validated());
        $this->auditLogService->log('workflow_step.updated', $step, $old, $step->fresh()->toArray());

        return redirect()->route('admin.workflow-templates.show', $workflowTemplate)->with('success', __('messages.workflow_step_updated'));
    }

    public function destroy(WorkflowTemplate $workflowTemplate, WorkflowStep $step): RedirectResponse
    {
        if ($response = $this->guardConfiguration($workflowTemplate)) {
            return $response;
        }

        try {
            $old = $step->toArray();
            $step->delete();
            $this->auditLogService->log('workflow_step.deleted', $step, $old, null);
        } catch (QueryException) {
            return back()->with('error', __('messages.workflow_step_delete_in_use'));
        }

        return back()->with('success', __('messages.workflow_step_deleted'));
    }

    private function guardConfiguration(WorkflowTemplate $workflowTemplate): ?RedirectResponse
    {
        if ($workflowTemplate->isLocked()) {
            return back()->with('error', __('messages.workflow_template_locked'));
        }

        if ($workflowTemplate->is_active) {
            return back()->with('error', __('messages.workflow_template_deactivate_before_edit'));
        }

        return null;
    }

    private function viewData(WorkflowTemplate $workflowTemplate, ?WorkflowStep $step = null): array
    {
        return [
            'workflowTemplate' => $workflowTemplate,
            'step' => $step,
            'roles' => Role::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'users' => User::query()
                ->where('is_active', true)
                ->when($step?->approver_user_id, fn ($query) => $query->orWhereKey($step->approver_user_id))
                ->orderBy('name')
                ->get(),
        ];
    }
}
