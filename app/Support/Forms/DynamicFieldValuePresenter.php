<?php

namespace App\Support\Forms;

use App\Enums\FormFieldType;
use App\Models\FormField;
use Carbon\CarbonImmutable;

class DynamicFieldValuePresenter
{
    public function display(FormField $field, mixed $value): string
    {
        $type = $field->type();

        if ($type === FormFieldType::Checkbox) {
            return (string) $value === '1' ? __('ui.yes') : __('ui.no_value');
        }

        if ($value === null || $value === '') {
            return '—';
        }

        return match ($type) {
            FormFieldType::Date => $this->formatDate((string) $value, 'd/m/Y'),
            FormFieldType::Time => $this->formatDate((string) $value, 'H:i'),
            FormFieldType::DateTime => $this->formatDate((string) $value, 'd/m/Y H:i'),
            default => (string) $value,
        };
    }

    private function formatDate(string $value, string $format): string
    {
        try {
            return CarbonImmutable::parse($value)->format($format);
        } catch (\Throwable) {
            return $value;
        }
    }
}
