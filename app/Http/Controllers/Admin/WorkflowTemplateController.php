<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LifecycleStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\WorkflowTemplateRequest;
use App\Models\FormTemplate;
use App\Models\WorkflowTemplate;
use App\Services\AuditLogService;
use App\Services\Workflow\WorkflowConfigurationService;
use App\Services\Workflow\WorkflowLifecycleService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class WorkflowTemplateController extends Controller
{
    public function __construct(
        private AuditLogService $auditLogService,
        private WorkflowConfigurationService $configurationService,
        private WorkflowLifecycleService $lifecycleService,
    ) {}

    public function index(): View
    {
        $allWorkflows = WorkflowTemplate::with('formTemplate')
            ->withCount(['steps', 'requests'])
            ->latest('id')
            ->get();

        $this->lifecycleService->decorateWorkflows($allWorkflows);

        $statusCounts = collect(LifecycleStatus::cases())->mapWithKeys(
            fn (LifecycleStatus $status) => [$status->value => $allWorkflows->where('lifecycle_status', $status)->count()]
        );

        $selectedStatus = request()->string('status')->toString();
        if (! in_array($selectedStatus, array_column(LifecycleStatus::cases(), 'value'), true)) {
            $selectedStatus = '';
        }

        $filtered = $selectedStatus === ''
            ? $allWorkflows
            : $allWorkflows->where('lifecycle_status', LifecycleStatus::from($selectedStatus))->values();

        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $workflows = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('admin.workflow_templates.index', compact('workflows', 'statusCounts', 'selectedStatus'));
    }

    public function create(): View
    {
        return view('admin.workflow_templates.create', [
            'formTemplates' => $this->configurableFormTemplates(),
        ]);
    }

    public function store(WorkflowTemplateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $formTemplate = FormTemplate::query()->findOrFail($data['form_template_id']);

        Gate::authorize('createWorkflow', $formTemplate);

        $workflow = DB::transaction(function () use ($request, $data, $formTemplate): WorkflowTemplate {
            $lockedForm = FormTemplate::query()->whereKey($formTemplate->id)->lockForUpdate()->firstOrFail();
            $this->configurationService->ensureFormAllowsWorkflowDraft($lockedForm);

            $versions = WorkflowTemplate::query()
                ->where('form_template_id', $lockedForm->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $data['version'] = ((int) $versions->max('version')) + 1;
            $data['is_active'] = false;
            $data['created_by'] = $request->user()->id;

            return WorkflowTemplate::create($data);
        });

        $this->auditLogService->log('workflow_template.created', $workflow, null, $workflow->toArray());

        return redirect()->route('admin.workflow-templates.show', $workflow)
            ->with('success', __('messages.workflow_template_created_draft'));
    }

    public function show(WorkflowTemplate $workflowTemplate): View
    {
        $workflowTemplate->load(['formTemplate', 'steps.approverRole', 'steps.approverDepartment', 'steps.approverUser'])
            ->loadCount('requests');
        $this->lifecycleService->decorateWorkflows(collect([$workflowTemplate]));

        return view('admin.workflow_templates.show', compact('workflowTemplate'));
    }

    public function edit(WorkflowTemplate $workflowTemplate): View
    {
        Gate::authorize('update', $workflowTemplate);
        $workflowTemplate->loadMissing('formTemplate');

        return view('admin.workflow_templates.edit', [
            'workflowTemplate' => $workflowTemplate,
            'formTemplates' => collect([$workflowTemplate->formTemplate])->filter(),
        ]);
    }

    public function update(WorkflowTemplateRequest $request, WorkflowTemplate $workflowTemplate): RedirectResponse
    {
        Gate::authorize('update', $workflowTemplate);
        $this->configurationService->ensureWorkflowMutable($workflowTemplate);

        $old = $workflowTemplate->toArray();
        $data = $request->validated();
        $data['form_template_id'] = $workflowTemplate->form_template_id;
        unset($data['is_active']);

        $workflowTemplate->update($data);
        $this->auditLogService->log('workflow_template.updated', $workflowTemplate, $old, $workflowTemplate->fresh()->toArray());

        return redirect()->route('admin.workflow-templates.show', $workflowTemplate)->with('success', __('messages.workflow_template_updated'));
    }

    public function destroy(WorkflowTemplate $workflowTemplate): RedirectResponse
    {
        Gate::authorize('delete', $workflowTemplate);
        $this->configurationService->ensureWorkflowMutable($workflowTemplate);

        try {
            $old = $workflowTemplate->toArray();
            $workflowTemplate->delete();
            $this->auditLogService->log('workflow_template.deleted', $workflowTemplate, $old, null);
        } catch (QueryException) {
            return back()->with('error', __('messages.workflow_template_delete_in_use'));
        }

        return redirect()->route('admin.workflow-templates.index')->with('success', __('messages.workflow_template_deleted'));
    }

    public function activate(WorkflowTemplate $workflowTemplate): RedirectResponse
    {
        Gate::authorize('activate', $workflowTemplate);

        $old = $workflowTemplate->toArray();
        $workflow = $this->configurationService->activateWorkflow($workflowTemplate);
        $this->auditLogService->log('workflow_template.activated', $workflow, $old, $workflow->toArray());

        return back()->with('success', __('messages.workflow_template_activated'));
    }

    public function deactivate(WorkflowTemplate $workflowTemplate): RedirectResponse
    {
        Gate::authorize('deactivate', $workflowTemplate);

        $old = $workflowTemplate->toArray();
        $workflow = $this->configurationService->deactivateWorkflow($workflowTemplate);
        $this->auditLogService->log('workflow_template.deactivated', $workflow, $old, $workflow->toArray());

        return back()->with('success', __('messages.workflow_template_deactivated'));
    }

    public function cloneVersion(WorkflowTemplate $workflowTemplate): RedirectResponse
    {
        Gate::authorize('cloneVersion', $workflowTemplate);

        $clone = $this->configurationService->cloneWorkflowVersion($workflowTemplate, request()->user());
        $this->auditLogService->log('workflow_template.version_cloned', $clone, null, $clone->toArray());

        return redirect()->route('admin.workflow-templates.show', $clone)
            ->with('success', __('messages.workflow_template_version_cloned', ['version' => $clone->version]));
    }

    private function configurableFormTemplates(): Collection
    {
        $forms = FormTemplate::query()
            ->withCount('requests')
            ->orderBy('name')
            ->orderByDesc('version')
            ->get();

        $this->lifecycleService->decorateForms($forms);

        return $forms
            ->filter(fn (FormTemplate $form) => in_array(
                $form->getAttribute('lifecycle_status'),
                [LifecycleStatus::Draft, LifecycleStatus::Current],
                true
            ))
            ->values();
    }
}
