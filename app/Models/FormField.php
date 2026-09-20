<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    public const TYPES = ['text', 'textarea', 'number', 'date', 'select', 'file'];

    public const CONDITION_EQUALS = 'equals';

    public const CONDITION_NOT_EQUALS = 'not_equals';

    public const CONDITION_FILLED = 'filled';

    public const CONDITION_EMPTY = 'empty';

    public const CONDITION_OPERATORS = [
        self::CONDITION_EQUALS,
        self::CONDITION_NOT_EQUALS,
        self::CONDITION_FILLED,
        self::CONDITION_EMPTY,
    ];

    protected $fillable = [
        'form_template_id',
        'label',
        'field_key',
        'field_type',
        'is_required',
        'condition_field_key',
        'condition_operator',
        'condition_value',
        'options',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'options' => 'array',
        ];
    }

    public function hasCondition(): bool
    {
        return filled($this->condition_field_key) && filled($this->condition_operator);
    }

    public function conditionRequiresValue(): bool
    {
        return in_array($this->condition_operator, [self::CONDITION_EQUALS, self::CONDITION_NOT_EQUALS], true);
    }

    public function formTemplate()
    {
        return $this->belongsTo(FormTemplate::class);
    }

    public function requestValues()
    {
        return $this->hasMany(RequestValue::class);
    }
}
