<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowRequest extends Model
{
    protected $table = 'requests';

    protected $fillable = [
        'request_code',
        'form_template_id',
        'workflow_template_id',
        'current_step_id',
        'created_by',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    public function formTemplate()
    {
        return $this->belongsTo(FormTemplate::class);
    }

    public function workflowTemplate()
    {
        return $this->belongsTo(WorkflowTemplate::class);
    }

    public function currentStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'current_step_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function values()
    {
        return $this->hasMany(RequestValue::class, 'request_id');
    }

    public function histories()
    {
        return $this->hasMany(ApprovalHistory::class, 'request_id')->latest();
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'request_id');
    }
}
