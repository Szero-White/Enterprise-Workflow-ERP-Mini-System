<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalHistory extends Model
{
    protected $fillable = ['request_id', 'workflow_step_id', 'actor_id', 'action', 'comment'];

    public function workflowRequest()
    {
        return $this->belongsTo(WorkflowRequest::class, 'request_id');
    }

    public function step()
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
