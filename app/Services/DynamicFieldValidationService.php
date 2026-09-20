<?php

namespace App\Services;

use App\Enums\FormFieldType;
use App\Models\FormTemplate;
use Illuminate\Validation\Rule;

class DynamicFieldValidationService
{
    public function __construct(private DynamicFieldConditionService $conditionService) {}

    public function rulesFor(FormTemplate $formTemplate, array $input = []): array
    {
        $formTemplate->loadMissing('fields');

        $rules = [];

        foreach ($formTemplate->fields as $field) {
            if (! $this->conditionService->isVisible($field, $input, $formTemplate->fields)) {
                $rules[$field->field_key] = ['exclude'];

                continue;
            }

            $fieldRules = $field->is_required ? ['required'] : ['nullable'];

            switch (FormFieldType::tryFrom($field->field_type)) {
                case FormFieldType::Number:
                    $fieldRules[] = 'numeric';
                    break;
                case FormFieldType::Date:
                    $fieldRules[] = 'date_format:Y-m-d';
                    break;
                case FormFieldType::Time:
                    $fieldRules[] = 'date_format:H:i';
                    break;
                case FormFieldType::DateTime:
                    $fieldRules[] = 'date_format:Y-m-d\TH:i';
                    break;
                case FormFieldType::Email:
                    array_push($fieldRules, 'string', 'email', 'max:255');
                    break;
                case FormFieldType::Phone:
                    array_push($fieldRules, 'string', 'max:30');
                    break;
                case FormFieldType::Url:
                    array_push($fieldRules, 'string', 'url:http,https', 'max:2048');
                    break;
                case FormFieldType::Checkbox:
                    $fieldRules = $field->is_required ? ['accepted'] : ['nullable', 'boolean'];
                    break;
                case FormFieldType::File:
                    if (config('demo.enabled') && ! config('demo.uploads_enabled')) {
                        $fieldRules = ['nullable', 'prohibited'];
                        break;
                    }

                    $maxKb = config('demo.enabled')
                        ? (int) config('demo.upload_max_kb', 512)
                        : (int) config('demo.normal_upload_max_kb', 5120);

                    array_push($fieldRules, 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:'.max(1, $maxKb));
                    break;
                case FormFieldType::Select:
                case FormFieldType::Radio:
                    $this->addSelectRules($fieldRules, $field->options ?? []);
                    break;
                case FormFieldType::Textarea:
                    array_push($fieldRules, 'string', 'max:5000');
                    break;
                default:
                    array_push($fieldRules, 'string', 'max:255');
            }

            $rules[$field->field_key] = $fieldRules;
        }

        return $rules;
    }

    private function addSelectRules(array &$fieldRules, array $options): void
    {
        $fieldRules[] = 'string';
        $fieldRules[] = Rule::in($options);
    }
}
