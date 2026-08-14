<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public const SYSTEM_KEYS = [
        'admin',
        'manager',
        'employee',
        'hr',
        'director',
        'procurement',
        'finance',
        'asset_manager',
    ];

    protected $fillable = ['name', 'key', 'description', 'is_system'];

    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function workflowSteps()
    {
        return $this->hasMany(WorkflowStep::class, 'approver_role_id');
    }

    public function isSystemRole(): bool
    {
        return $this->is_system || in_array($this->key, self::SYSTEM_KEYS, true);
    }
}
