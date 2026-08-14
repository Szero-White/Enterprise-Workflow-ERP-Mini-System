<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowTemplate extends Model
{
    protected $fillable = [
        'form_template_id',
        'name',
        'version',
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

    public function formTemplate()
    {
        return $this->belongsTo(FormTemplate::class);
    }

    public function steps()
    {
        return $this->hasMany(WorkflowStep::class)->orderBy('step_order');
    }

    public function requests()
    {
        return $this->hasMany(WorkflowRequest::class, 'workflow_template_id');
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null || $this->requests()->exists();
    }

    public function isReadyToActivate(): bool
    {
        return $this->steps()->exists();
    }

    public function displayName(): string
    {
        return sprintf('%s · v%d', $this->name, $this->version);
    }
}
