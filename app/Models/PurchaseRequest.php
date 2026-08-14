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

    public function activePurchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class)
            ->where('status', '!=', PurchaseOrderStatus::Cancelled->value)
            ->latestOfMany();
    }
}
