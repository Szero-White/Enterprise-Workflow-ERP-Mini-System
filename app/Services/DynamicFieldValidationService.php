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
                    array_push($fieldRules, 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120');
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
