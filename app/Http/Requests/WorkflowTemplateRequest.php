<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkflowTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $workflowTemplate = $this->route('workflowTemplate') ?? $this->route('workflow_template');

        $formTemplateRule = $workflowTemplate
            ? Rule::in([$workflowTemplate->form_template_id])
            : Rule::exists('form_templates', 'id');

        return [
            'form_template_id' => ['required', $formTemplateRule],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'form_template_id.in' => __('messages.workflow_form_template_immutable'),
        ];
    }
}
