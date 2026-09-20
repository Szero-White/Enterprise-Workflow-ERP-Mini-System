<?php

namespace App\Services;

use App\Models\FormField;
use App\Models\FormTemplate;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DynamicFieldConditionService
{
    public function isVisible(FormField $field, array $input, ?Collection $fields = null): bool
    {
        if (! $field->hasCondition()) {
            return true;
        }

        $fields ??= $field->formTemplate?->fields;
        $source = $fields?->firstWhere('field_key', $field->condition_field_key);

        if ($source?->hasCondition() && ! $this->isVisible($source, $input, $fields)) {
            return false;
        }

        $sourceValue = $input[$field->condition_field_key] ?? null;
        $sourceValue = is_scalar($sourceValue) ? (string) $sourceValue : null;

        return match ($field->condition_operator) {
            FormField::CONDITION_EQUALS => $sourceValue === (string) $field->condition_value,
            FormField::CONDITION_NOT_EQUALS => $sourceValue !== (string) $field->condition_value,
            FormField::CONDITION_FILLED => filled($sourceValue),
            FormField::CONDITION_EMPTY => blank($sourceValue),
            default => false,
        };
    }

    public function ensureTemplateConditionsValid(FormTemplate $formTemplate): void
    {
        $formTemplate->loadMissing('fields');
        $fields = $formTemplate->fields->keyBy('field_key');

        foreach ($formTemplate->fields as $field) {
            if (! $field->hasCondition()) {
                continue;
            }

            $source = $fields->get($field->condition_field_key);

            if (! $source || $source->id === $field->id) {
                $this->fail($field, __('messages.form_field_condition_source_invalid'));
            }

            if ((int) $source->sort_order >= (int) $field->sort_order) {
                $this->fail($field, __('messages.form_field_condition_source_must_precede'));
            }

            if (! in_array($field->condition_operator, FormField::CONDITION_OPERATORS, true)) {
                $this->fail($field, __('messages.form_field_condition_operator_invalid'));
            }

            if ($field->conditionRequiresValue() && blank($field->condition_value) && $field->condition_value !== '0') {
                $this->fail($field, __('messages.form_field_condition_value_required'));
            }

            if (
                $source->type()?->usesOptions()
                && $field->conditionRequiresValue()
                && ! in_array((string) $field->condition_value, $source->options ?? [], true)
            ) {
                $this->fail($field, __('messages.form_field_condition_value_not_option'));
            }
        }
    }

    private function fail(FormField $field, string $message): never
    {
        throw ValidationException::withMessages([
            'form_template' => __('messages.form_field_condition_invalid', [
                'field' => $field->label,
                'reason' => $message,
            ]),
        ]);
    }
}
