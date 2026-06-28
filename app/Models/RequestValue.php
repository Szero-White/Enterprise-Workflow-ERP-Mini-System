<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestValue extends Model
{
    protected $fillable = ['request_id', 'form_field_id', 'field_key', 'value'];

    public function workflowRequest()
    {
        return $this->belongsTo(WorkflowRequest::class, 'request_id');
    }

    public function field()
    {
        return $this->belongsTo(FormField::class, 'form_field_id');
    }
}
