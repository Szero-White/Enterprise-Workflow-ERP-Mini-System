<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FormTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $formTemplate = $this->route('formTemplate') ?? $this->route('form_template');

        $codeRules = ['required', 'string', 'max:50', 'regex:/^[A-Z][A-Z0-9_]*$/'];
        $codeRules[] = $formTemplate
            ? Rule::in([$formTemplate->code])
            : Rule::unique('form_templates', 'code');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => $codeRules,
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.in' => __('messages.form_template_code_immutable'),
        ];
    }
}
