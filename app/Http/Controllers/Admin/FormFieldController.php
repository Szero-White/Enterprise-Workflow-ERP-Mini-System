<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FormFieldRequest;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FormFieldController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function index(FormTemplate $formTemplate): View
    {
        $fields = $formTemplate->fields()->paginate(20);
        return view('admin.form_fields.index', compact('formTemplate', 'fields'));
    }

    public function create(FormTemplate $formTemplate): View
    {
        return view('admin.form_fields.create', compact('formTemplate'));
    }

    public function store(FormFieldRequest $request, FormTemplate $formTemplate): RedirectResponse
    {
        $data = $this->prepareData($request->validated(), $request);
        $data['form_template_id'] = $formTemplate->id;

        $field = FormField::create($data);
        $this->auditLogService->log('form_field.created', $field, null, $field->toArray());

        return redirect()->route('admin.form-templates.show', $formTemplate)->with('success', 'Đã thêm field.');
    }

    public function edit(FormTemplate $formTemplate, FormField $field): View
    {
        return view('admin.form_fields.edit', compact('formTemplate', 'field'));
    }

    public function update(FormFieldRequest $request, FormTemplate $formTemplate, FormField $field): RedirectResponse
    {
        $old = $field->toArray();
        $field->update($this->prepareData($request->validated(), $request));
        $this->auditLogService->log('form_field.updated', $field, $old, $field->fresh()->toArray());

        return redirect()->route('admin.form-templates.show', $formTemplate)->with('success', 'Đã cập nhật field.');
    }

    public function destroy(FormTemplate $formTemplate, FormField $field): RedirectResponse
    {
        $old = $field->toArray();
        $this->auditLogService->log('form_field.deleted', $field, $old, null);
        $field->delete();
        return back()->with('success', 'Đã xóa field.');
    }

    private function prepareData(array $data, FormFieldRequest $request): array
    {
        $options = null;
        if (($data['field_type'] ?? null) === 'select' && filled($request->input('options_text'))) {
            $options = collect(preg_split('/\r\n|\r|\n/', $request->input('options_text')))
                ->map(fn ($item) => trim($item))
                ->filter()
                ->values()
                ->toArray();
        }

        return [
            'label' => $data['label'],
            'field_key' => $data['field_key'],
            'field_type' => $data['field_type'],
            'is_required' => $request->boolean('is_required'),
            'options' => $options,
            'sort_order' => $data['sort_order'],
        ];
    }
}
