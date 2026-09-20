<?php

namespace App\Http\Requests;

use App\Enums\FormFieldType;
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
            'field_type' => ['required', Rule::in(FormFieldType::values())],
            'is_required' => ['nullable', 'boolean'],
            'options_text' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'condition_enabled' => ['nullable', 'boolean'],
            'condition_field_key' => ['nullable', 'string', 'max:255'],
            'condition_operator' => ['nullable', Rule::in(FormField::CONDITION_OPERATORS)],
            'condition_value' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $this->validateSelectOptions($validator);
            $this->validateCondition($validator);
        });
    }

    private function validateSelectOptions($validator): void
    {
        $type = FormFieldType::tryFrom((string) $this->input('field_type'));

        if (! $type?->usesOptions()) {
            return;
        }

        $options = $this->parseOptions();

        if ($options->isEmpty()) {
            $validator->errors()->add('options_text', __('messages.select_options_required'));
        }
    }

    private function validateCondition($validator): void
    {
        if (! $this->boolean('condition_enabled')) {
            return;
        }

        $template = $this->route('form_template') ?? $this->route('formTemplate');
        $sourceKey = trim((string) $this->input('condition_field_key'));
        $operator = $this->input('condition_operator');
        $targetKey = trim((string) $this->input('field_key'));

        if ($sourceKey === '') {
            $validator->errors()->add('condition_field_key', __('messages.form_field_condition_source_required'));

            return;
        }

        if ($sourceKey === $targetKey) {
            $validator->errors()->add('condition_field_key', __('messages.form_field_condition_self_forbidden'));

            return;
        }

        $source = $template?->fields()->where('field_key', $sourceKey)->first();
        if (! $source) {
            $validator->errors()->add('condition_field_key', __('messages.form_field_condition_source_invalid'));

            return;
        }

        if ((int) $source->sort_order >= (int) $this->input('sort_order')) {
            $validator->errors()->add('condition_field_key', __('messages.form_field_condition_source_must_precede'));
        }

        if (! in_array($operator, FormField::CONDITION_OPERATORS, true)) {
            $validator->errors()->add('condition_operator', __('messages.form_field_condition_operator_invalid'));

            return;
        }

        if (! in_array($operator, [FormField::CONDITION_EQUALS, FormField::CONDITION_NOT_EQUALS], true)) {
            return;
        }

        $conditionValue = (string) $this->input('condition_value');
        if ($conditionValue === '') {
            $validator->errors()->add('condition_value', __('messages.form_field_condition_value_required'));

            return;
        }

        if ($source->type()?->usesOptions() && ! in_array($conditionValue, $source->options ?? [], true)) {
            $validator->errors()->add('condition_value', __('messages.form_field_condition_value_not_option'));
        }
    }

    private function parseOptions()
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->input('options_text')))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values();
    }
}
