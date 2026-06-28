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
        $data = $request->validated();
        $data['workflow_template_id'] = $workflowTemplate->id;

        $step = WorkflowStep::create($data);
        $this->auditLogService->log('workflow_step.created', $step, null, $step->toArray());

        return redirect()->route('admin.workflow-templates.show', $workflowTemplate)->with('success', 'Đã thêm bước duyệt.');
    }

    public function edit(WorkflowTemplate $workflowTemplate, WorkflowStep $step): View
    {
        return view('admin.workflow_steps.edit', $this->viewData($workflowTemplate, $step));
    }

    public function update(WorkflowStepRequest $request, WorkflowTemplate $workflowTemplate, WorkflowStep $step): RedirectResponse
    {
        $old = $step->toArray();
        $step->update($request->validated());
        $this->auditLogService->log('workflow_step.updated', $step, $old, $step->fresh()->toArray());

        return redirect()->route('admin.workflow-templates.show', $workflowTemplate)->with('success', 'Đã cập nhật bước duyệt.');
    }

    public function destroy(WorkflowTemplate $workflowTemplate, WorkflowStep $step): RedirectResponse
    {
        $old = $step->toArray();
        $this->auditLogService->log('workflow_step.deleted', $step, $old, null);
        $step->delete();
        return back()->with('success', 'Đã xóa bước duyệt.');
    }

    private function viewData(WorkflowTemplate $workflowTemplate, ?WorkflowStep $step = null): array
    {
        return [
            'workflowTemplate' => $workflowTemplate,
            'step' => $step,
            'roles' => Role::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
        ];
    }
}
