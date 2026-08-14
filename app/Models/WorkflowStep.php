<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    public const APPROVER_ROLE = 'role';
    public const APPROVER_DEPARTMENT = 'department';
    public const APPROVER_USER = 'user';

    protected $fillable = [
        'workflow_template_id',
        'step_name',
        'step_order',
        'approver_type',
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

    public function scopeApproverFor($query, User $user)
    {
        return $query->where(function ($approver) use ($user): void {
            $approver->where(function ($role) use ($user): void {
                $role->where('approver_type', self::APPROVER_ROLE)
                    ->where('approver_role_id', $user->role_id);
            })->orWhere(function ($department) use ($user): void {
                $department->where('approver_type', self::APPROVER_DEPARTMENT)
                    ->where('approver_department_id', $user->department_id);
            })->orWhere(function ($specificUser) use ($user): void {
                $specificUser->where('approver_type', self::APPROVER_USER)
                    ->where('approver_user_id', $user->id);
            });
        });
    }

    public function canBeApprovedBy(User $user): bool
    {
        return match ($this->approver_type) {
            self::APPROVER_USER => (int) $this->approver_user_id === (int) $user->id,
            self::APPROVER_DEPARTMENT => (int) $this->approver_department_id === (int) $user->department_id,
            self::APPROVER_ROLE => (int) $this->approver_role_id === (int) $user->role_id,
            default => false,
        };
    }

    public function approverLabel(): string
    {
        return match ($this->approver_type) {
            self::APPROVER_USER => $this->approverUser?->name ?? '-',
            self::APPROVER_DEPARTMENT => $this->approverDepartment?->name ?? '-',
            self::APPROVER_ROLE => $this->approverRole
                ? (trans()->has('ui.roles.'.$this->approverRole->key) ? __('ui.roles.'.$this->approverRole->key) : $this->approverRole->name)
                : '-',
            default => '-',
        };
    }
}
