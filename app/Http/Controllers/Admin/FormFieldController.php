<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FormFieldRequest;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Services\AuditLogService;
use App\Services\DynamicFieldConditionService;
use App\Services\Workflow\WorkflowConfigurationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FormFieldController extends Controller
{
    public function __construct(
        private AuditLogService $auditLogService,
        private WorkflowConfigurationService $configurationService,
        private DynamicFieldConditionService $conditionService,
    ) {}

    public function index(FormTemplate $formTemplate): View
    {
        $fields = $formTemplate->fields()->paginate(20);

        return view('admin.form_fields.index', compact('formTemplate', 'fields'));
    }

    public function create(FormTemplate $formTemplate): View
    {
        $conditionFields = $formTemplate->fields()->get();

        return view('admin.form_fields.create', compact('formTemplate', 'conditionFields'));
    }

    public function store(FormFieldRequest $request, FormTemplate $formTemplate): RedirectResponse
    {
        if ($response = $this->guardConfiguration($formTemplate)) {
            return $response;
        }

        $data = $this->prepareData($request->validated(), $request);
        $data['form_template_id'] = $formTemplate->id;

        $field = FormField::create($data);
        $this->auditLogService->log('form_field.created', $field, null, $field->toArray());

        return redirect()->route('admin.form-templates.show', $formTemplate)->with('success', __('messages.form_field_created'));
    }

    public function edit(FormTemplate $formTemplate, FormField $field): View
    {
        $conditionFields = $formTemplate->fields()->whereKeyNot($field->id)->get();

        return view('admin.form_fields.edit', compact('formTemplate', 'field', 'conditionFields'));
    }

    public function update(FormFieldRequest $request, FormTemplate $formTemplate, FormField $field): RedirectResponse
    {
        if ($response = $this->guardConfiguration($formTemplate)) {
            return $response;
        }

        $old = $field->toArray();
        $data = $this->prepareData($request->validated(), $request);

        DB::transaction(function () use ($field, $formTemplate, $data): void {
            $oldKey = $field->field_key;
            $field->update($data);

            if ($oldKey !== $field->field_key) {
                $formTemplate->fields()
                    ->where('condition_field_key', $oldKey)
                    ->update(['condition_field_key' => $field->field_key]);
            }

            $this->conditionService->ensureTemplateConditionsValid($formTemplate->fresh('fields'));
        });

        $this->auditLogService->log('form_field.updated', $field, $old, $field->fresh()->toArray());

        return redirect()->route('admin.form-templates.show', $formTemplate)->with('success', __('messages.form_field_updated'));
    }

    public function destroy(FormTemplate $formTemplate, FormField $field): RedirectResponse
    {
        if ($response = $this->guardConfiguration($formTemplate)) {
            return $response;
        }

        if ($field->requestValues()->exists()) {
            return back()->with('error', __('messages.form_field_delete_in_use'));
        }

        if ($formTemplate->fields()->where('condition_field_key', $field->field_key)->exists()) {
            return back()->with('error', __('messages.form_field_delete_condition_source'));
        }

        try {
            $old = $field->toArray();
            $field->delete();
            $this->auditLogService->log('form_field.deleted', $field, $old, null);
        } catch (QueryException) {
            return back()->with('error', __('messages.form_field_delete_in_use'));
        }

        return back()->with('success', __('messages.form_field_deleted'));
    }

    private function guardConfiguration(FormTemplate $formTemplate): ?RedirectResponse
    {
        if ($formTemplate->isLocked()) {
            return back()->with('error', __('messages.form_template_locked'));
        }

        if ($formTemplate->is_active) {
            return back()->with('error', __('messages.form_template_deactivate_before_edit'));
        }

        return null;
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

        $conditionEnabled = $request->boolean('condition_enabled');
        $conditionOperator = $conditionEnabled ? $data['condition_operator'] : null;
        $conditionRequiresValue = in_array(
            $conditionOperator,
            [FormField::CONDITION_EQUALS, FormField::CONDITION_NOT_EQUALS],
            true
        );

        return [
            'label' => $data['label'],
            'field_key' => $data['field_key'],
            'field_type' => $data['field_type'],
            'is_required' => $request->boolean('is_required'),
            'condition_field_key' => $conditionEnabled ? $data['condition_field_key'] : null,
            'condition_operator' => $conditionOperator,
            'condition_value' => $conditionRequiresValue ? $data['condition_value'] : null,
            'options' => $options,
            'sort_order' => $data['sort_order'],
        ];
    }
}
