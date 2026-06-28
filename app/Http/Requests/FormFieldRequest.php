<?php

namespace App\Http\Requests;

use App\Models\FormField;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FormFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $template = $this->route('form_template') ?? $this->route('formTemplate');
        $field = $this->route('field');

        return [
            'label' => ['required', 'string', 'max:255'],
            'field_key' => [
                'required',
                'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/',
                Rule::unique('form_fields', 'field_key')
                    ->where('form_template_id', $template?->id)
                    ->ignore($field?->id),
            ],
            'field_type' => ['required', Rule::in(FormField::TYPES)],
            'is_required' => ['nullable', 'boolean'],
            'options_text' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
