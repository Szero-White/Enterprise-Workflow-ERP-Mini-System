<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    public const TYPES = ['text', 'textarea', 'number', 'date', 'select', 'file'];

    protected $fillable = [
        'form_template_id',
        'label',
        'field_key',
        'field_type',
        'is_required',
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

    public function formTemplate()
    {
        return $this->belongsTo(FormTemplate::class);
    }
}
