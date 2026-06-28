<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormTemplate extends Model
{
    protected $fillable = ['name', 'code', 'description', 'is_active', 'created_by'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function fields()
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function workflows()
    {
        return $this->hasMany(WorkflowTemplate::class);
    }

    public function activeWorkflow()
    {
        return $this->hasOne(WorkflowTemplate::class)->where('is_active', true);
    }
}
