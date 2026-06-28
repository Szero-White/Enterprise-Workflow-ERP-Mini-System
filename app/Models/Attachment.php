<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = ['request_id', 'form_field_id', 'original_name', 'path', 'mime_type', 'size', 'uploaded_by'];

    public function workflowRequest()
    {
        return $this->belongsTo(WorkflowRequest::class, 'request_id');
    }

    public function field()
    {
        return $this->belongsTo(FormField::class, 'form_field_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
