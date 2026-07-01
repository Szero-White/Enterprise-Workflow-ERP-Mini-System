<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_RETURNED = 'returned';

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

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => __('status.pending'),
            self::STATUS_APPROVED => __('status.approved'),
            self::STATUS_REJECTED => __('status.rejected'),
            self::STATUS_RETURNED => __('status.returned'),
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        $key = 'status.'.$this->status;

        return trans()->has($key) ? __($key) : $this->status;
    }

    public static function statusMeta(?string $status): array
    {
        return match ($status) {
            self::STATUS_PENDING => ['label' => __('status.pending'), 'class' => 'text-bg-warning', 'icon' => 'bi-hourglass-split'],
            self::STATUS_APPROVED => ['label' => __('status.approved'), 'class' => 'text-bg-success', 'icon' => 'bi-check-circle-fill'],
            self::STATUS_REJECTED => ['label' => __('status.rejected'), 'class' => 'text-bg-danger', 'icon' => 'bi-x-circle-fill'],
            self::STATUS_RETURNED => ['label' => __('status.returned'), 'class' => 'text-bg-info', 'icon' => 'bi-arrow-counterclockwise'],
            default => ['label' => ucfirst((string) $status), 'class' => 'text-bg-secondary', 'icon' => 'bi-circle-fill'],
        };
    }
}
