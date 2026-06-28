<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkflowTemplateRequest;
use App\Models\FormTemplate;
use App\Models\WorkflowTemplate;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WorkflowTemplateController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function index(): View
    {
        $workflows = WorkflowTemplate::with(['formTemplate'])->withCount('steps')->latest()->paginate(10);
        return view('admin.workflow_templates.index', compact('workflows'));
    }

    public function create(): View
    {
        return view('admin.workflow_templates.create', [
            'formTemplates' => FormTemplate::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(WorkflowTemplateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['created_by'] = $request->user()->id;

        $workflow = WorkflowTemplate::create($data);
        $this->auditLogService->log('workflow_template.created', $workflow, null, $workflow->toArray());

        return redirect()->route('admin.workflow-templates.show', $workflow)->with('success', 'Đã tạo workflow.');
    }

    public function show(WorkflowTemplate $workflowTemplate): View
    {
        $workflowTemplate->load(['formTemplate', 'steps.approverRole', 'steps.approverDepartment', 'steps.approverUser']);
        return view('admin.workflow_templates.show', compact('workflowTemplate'));
    }

    public function edit(WorkflowTemplate $workflowTemplate): View
    {
        return view('admin.workflow_templates.edit', [
            'workflowTemplate' => $workflowTemplate,
            'formTemplates' => FormTemplate::orderBy('name')->get(),
        ]);
    }

    public function update(WorkflowTemplateRequest $request, WorkflowTemplate $workflowTemplate): RedirectResponse
    {
        $old = $workflowTemplate->toArray();
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $workflowTemplate->update($data);
        $this->auditLogService->log('workflow_template.updated', $workflowTemplate, $old, $workflowTemplate->fresh()->toArray());

        return redirect()->route('admin.workflow-templates.show', $workflowTemplate)->with('success', 'Đã cập nhật workflow.');
    }

    public function destroy(WorkflowTemplate $workflowTemplate): RedirectResponse
    {
        $old = $workflowTemplate->toArray();
        $this->auditLogService->log('workflow_template.deleted', $workflowTemplate, $old, null);
        $workflowTemplate->delete();
        return redirect()->route('admin.workflow-templates.index')->with('success', 'Đã xóa workflow.');
    }
}
