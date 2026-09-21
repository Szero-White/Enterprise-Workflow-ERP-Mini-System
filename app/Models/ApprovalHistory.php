<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalHistory extends Model
{
    public const ACTION_APPROVE = 'approve';

    public const ACTION_REJECT = 'reject';

    public const ACTION_RETURN = 'return';

    public const DECISION_ACTIONS = [
        self::ACTION_APPROVE,
        self::ACTION_REJECT,
        self::ACTION_RETURN,
    ];

    protected $fillable = ['request_id', 'workflow_step_id', 'actor_id', 'action', 'comment', 'acted_at'];

    protected function casts(): array
    {
        return [
            'acted_at' => 'datetime',
        ];
    }

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
