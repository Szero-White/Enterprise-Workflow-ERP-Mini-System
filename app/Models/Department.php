<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'code', 'description'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function workflowSteps()
    {
        return $this->hasMany(WorkflowStep::class, 'approver_department_id');
    }
}
