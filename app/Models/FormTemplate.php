<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FormTemplate extends Model
{
    protected $fillable = [
        'name',
        'code',
        'version',
        'description',
        'submission_type',
        'is_active',
        'locked_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'is_active' => 'boolean',
            'locked_at' => 'datetime',
        ];
    }

    public function fields()
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function workflows()
    {
        return $this->hasMany(WorkflowTemplate::class);
    }

    public function requests()
    {
        return $this->hasMany(WorkflowRequest::class, 'form_template_id');
    }

    public function scopeDynamicSubmission(Builder $query): Builder
    {
        return $query->where('submission_type', 'dynamic');
    }

    public function scopeReadyForSubmission(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereHas('activeWorkflow', fn (Builder $workflow) => $workflow->whereHas('steps'));
    }

    public function activeWorkflow()
    {
        return $this->hasOne(WorkflowTemplate::class)->where('is_active', true);
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null || $this->requests()->exists();
    }

    public function displayName(): string
    {
        return sprintf('%s · v%d', $this->name, $this->version);
    }
}
