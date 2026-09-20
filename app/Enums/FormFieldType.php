<?php

namespace App\Enums;

enum FormFieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Number = 'number';
    case Email = 'email';
    case Phone = 'tel';
    case Date = 'date';
    case Time = 'time';
    case DateTime = 'datetime';
    case Url = 'url';
    case Select = 'select';
    case Radio = 'radio';
    case Checkbox = 'checkbox';
    case File = 'file';

    public static function values(): array
    {
        return array_map(static fn (self $type): string => $type->value, self::cases());
    }

    public function label(): string
    {
        return __('ui.form_field_types.'.$this->value);
    }

    public function usesOptions(): bool
    {
        return in_array($this, [self::Select, self::Radio], true);
    }

    public function htmlInputType(): string
    {
        return $this === self::DateTime ? 'datetime-local' : $this->value;
    }
}
