<?php

namespace App\Services;

use App\Models\FormTemplate;
use Illuminate\Validation\Rule;

class DynamicFieldValidationService
{
    public function rulesFor(FormTemplate $formTemplate): array
    {
        $formTemplate->loadMissing('fields');

        $rules = [];

        foreach ($formTemplate->fields as $field) {
            $fieldRules = $field->is_required ? ['required'] : ['nullable'];

            switch ($field->field_type) {
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'file':
                    if (config('demo.enabled') && ! config('demo.uploads_enabled')) {
                        $fieldRules = ['nullable', 'prohibited'];
                        break;
                    }

                    $maxKb = config('demo.enabled')
                        ? (int) config('demo.upload_max_kb', 512)
                        : (int) config('demo.normal_upload_max_kb', 5120);

                    array_push($fieldRules, 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:'.max(1, $maxKb));
                    break;
                case 'select':
                    $this->addSelectRules($fieldRules, $field->options ?? []);
                    break;
                case 'textarea':
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
