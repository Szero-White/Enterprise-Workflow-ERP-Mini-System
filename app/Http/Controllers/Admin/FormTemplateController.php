<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FormTemplateRequest;
use App\Models\FormTemplate;
use App\Services\AuditLogService;
use App\Services\Workflow\WorkflowConfigurationService;
use App\Services\Workflow\WorkflowLifecycleService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FormTemplateController extends Controller
{
    public function __construct(
        private AuditLogService $auditLogService,
        private WorkflowConfigurationService $configurationService,
        private WorkflowLifecycleService $lifecycleService,
    ) {}

    public function index(): View
    {
        $templates = FormTemplate::withCount(['fields', 'requests'])->latest('id')->paginate(10);
        $this->lifecycleService->decorateForms($templates->getCollection());

        return view('admin.form_templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('admin.form_templates.create');
    }

    public function store(FormTemplateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['version'] = 1;
        $data['is_active'] = false;
        $data['created_by'] = $request->user()->id;

        $template = FormTemplate::create($data);
        $this->auditLogService->log('form_template.created', $template, null, $template->toArray());

        return redirect()->route('admin.form-templates.show', $template)
            ->with('success', __('messages.form_template_created_draft'));
    }

    public function show(FormTemplate $formTemplate): View
    {
        $formTemplate->load(['fields', 'workflows.steps'])->loadCount('requests');
        $this->lifecycleService->decorateForms(collect([$formTemplate]));

        $publishWorkflow = $formTemplate->workflows
            ->first(fn ($workflow) => $workflow->is_active && $workflow->steps->isNotEmpty())
            ?? $formTemplate->workflows
                ->sortByDesc('version')
                ->first(fn ($workflow) => $workflow->steps->isNotEmpty());

        return view('admin.form_templates.show', compact('formTemplate', 'publishWorkflow'));
    }

    public function edit(FormTemplate $formTemplate): View
    {
        return view('admin.form_templates.edit', compact('formTemplate'));
    }

    public function update(FormTemplateRequest $request, FormTemplate $formTemplate): RedirectResponse
    {
        $this->ensureEditable($formTemplate);

        $old = $formTemplate->toArray();
        $data = $request->validated();
        $data['code'] = $formTemplate->code;
        unset($data['is_active']);

        $formTemplate->update($data);
        $this->auditLogService->log('form_template.updated', $formTemplate, $old, $formTemplate->fresh()->toArray());

        return redirect()->route('admin.form-templates.show', $formTemplate)
            ->with('success', __('messages.form_template_updated'));
    }

    public function destroy(FormTemplate $formTemplate): RedirectResponse
    {
        if ($formTemplate->isLocked()) {
            return back()->with('error', __('messages.form_template_delete_locked'));
        }

        if ($formTemplate->is_active) {
            return back()->with('error', __('messages.form_template_deactivate_before_delete'));
        }

        try {
            $old = $formTemplate->toArray();
            $formTemplate->delete();
            $this->auditLogService->log('form_template.deleted', $formTemplate, $old, null);
        } catch (QueryException) {
            return back()->with('error', __('messages.form_template_delete_in_use'));
        }

        return redirect()->route('admin.form-templates.index')->with('success', __('messages.form_template_deleted'));
    }

    public function activate(FormTemplate $formTemplate): RedirectResponse
    {
        $old = $formTemplate->toArray();
        $workflowBeforePublish = $formTemplate->activeWorkflow()->first();
        $template = $this->configurationService->activateForm($formTemplate);
        $this->auditLogService->log('form_template.activated', $template, $old, $template->toArray());

        $activeWorkflow = $template->activeWorkflow()->first();
        if ($activeWorkflow && (! $workflowBeforePublish || $workflowBeforePublish->id !== $activeWorkflow->id)) {
            $this->auditLogService->log(
                'workflow_template.activated',
                $activeWorkflow,
                ['is_active' => false],
                $activeWorkflow->toArray(),
                __('ui.audit_actions.workflow_template_activated')
            );
        }

        return back()->with('success', __('messages.form_template_published'));
    }

    public function deactivate(FormTemplate $formTemplate): RedirectResponse
    {
        $old = $formTemplate->toArray();
        $template = $this->configurationService->deactivateForm($formTemplate);
        $this->auditLogService->log('form_template.deactivated', $template, $old, $template->toArray());

        return back()->with('success', __('messages.form_template_deactivated'));
    }

    public function cloneVersion(FormTemplate $formTemplate): RedirectResponse
    {
        $clone = $this->configurationService->cloneFormVersion($formTemplate, request()->user());
        $this->auditLogService->log('form_template.version_cloned', $clone, null, $clone->toArray());

        return redirect()->route('admin.form-templates.show', $clone)
            ->with('success', __('messages.form_template_version_cloned', ['version' => $clone->version]));
    }

    private function ensureEditable(FormTemplate $formTemplate): void
    {
        $this->configurationService->ensureFormMutable($formTemplate);

        if ($formTemplate->is_active) {
            abort(422, __('messages.form_template_deactivate_before_edit'));
        }
    }
}
