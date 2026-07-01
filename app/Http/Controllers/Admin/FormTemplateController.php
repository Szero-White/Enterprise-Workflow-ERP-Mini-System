<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FormTemplateRequest;
use App\Models\FormTemplate;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FormTemplateController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function index(): View
    {
        $templates = FormTemplate::withCount('fields')->latest()->paginate(10);
        return view('admin.form_templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('admin.form_templates.create');
    }

    public function store(FormTemplateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['created_by'] = $request->user()->id;

        $template = FormTemplate::create($data);
        $this->auditLogService->log('form_template.created', $template, null, $template->toArray());

        return redirect()->route('admin.form-templates.show', $template)->with('success', __('messages.form_template_created'));
    }

    public function show(FormTemplate $formTemplate): View
    {
        $formTemplate->load('fields');
        return view('admin.form_templates.show', compact('formTemplate'));
    }

    public function edit(FormTemplate $formTemplate): View
    {
        return view('admin.form_templates.edit', compact('formTemplate'));
    }

    public function update(FormTemplateRequest $request, FormTemplate $formTemplate): RedirectResponse
    {
        $old = $formTemplate->toArray();
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $formTemplate->update($data);
        $this->auditLogService->log('form_template.updated', $formTemplate, $old, $formTemplate->fresh()->toArray());

        return redirect()->route('admin.form-templates.show', $formTemplate)->with('success', __('messages.form_template_updated'));
    }

    public function destroy(FormTemplate $formTemplate): RedirectResponse
    {
        $old = $formTemplate->toArray();
        $this->auditLogService->log('form_template.deleted', $formTemplate, $old, null);
        $formTemplate->delete();
        return redirect()->route('admin.form-templates.index')->with('success', __('messages.form_template_deleted'));
    }
}
