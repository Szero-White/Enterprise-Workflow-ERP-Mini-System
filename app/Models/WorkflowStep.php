<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    protected $fillable = [
        'workflow_template_id',
        'step_name',
        'step_order',
        'approver_role_id',
        'approver_department_id',
        'approver_user_id',
    ];

    public function workflowTemplate()
    {
        return $this->belongsTo(WorkflowTemplate::class);
    }

    public function approverRole()
    {
        return $this->belongsTo(Role::class, 'approver_role_id');
    }

    public function approverDepartment()
    {
        return $this->belongsTo(Department::class, 'approver_department_id');
    }

    public function approverUser()
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public function canBeApprovedBy(User $user): bool
    {
        if ($this->approver_user_id && $this->approver_user_id === $user->id) {
            return true;
        }

        if ($this->approver_role_id && $this->approver_role_id === $user->role_id) {
            return true;
        }

        if ($this->approver_department_id && $this->approver_department_id === $user->department_id) {
            return true;
        }

        return false;
    }
}
