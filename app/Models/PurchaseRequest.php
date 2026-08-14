<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'workflow_request_id',
        'purpose',
        'required_date',
        'estimated_total',
        'currency',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'required_date' => 'date',
            'estimated_total' => 'decimal:2',
            'status' => PurchaseRequestStatus::class,
        ];
    }

    public function workflowRequest(): BelongsTo
    {
        return $this->belongsTo(WorkflowRequest::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function canBeViewedBy(User $user): bool
    {
        $this->loadMissing('workflowRequest.currentStep');
        $workflowRequest = $this->workflowRequest;

        if (! $workflowRequest) {
            return false;
        }

        if ($workflowRequest->canBeViewedOperationallyBy($user)) {
            return true;
        }

        return $user->hasRole('procurement')
            && $workflowRequest->status === WorkflowRequest::STATUS_APPROVED;
    }

    public function scopeVisibleTo($query, User $user)
    {
        return $query->where(function ($builder) use ($user): void {
            $builder->whereHas('workflowRequest', fn ($workflow) => $workflow->visibleTo($user));

            if ($user->hasRole('procurement')) {
                $builder->orWhereHas(
                    'workflowRequest',
                    fn ($workflow) => $workflow->where('status', WorkflowRequest::STATUS_APPROVED)
                );
            }
        });
    }

    public function activePurchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class)
            ->where('status', '!=', PurchaseOrderStatus::Cancelled->value)
            ->latestOfMany();
    }
}
