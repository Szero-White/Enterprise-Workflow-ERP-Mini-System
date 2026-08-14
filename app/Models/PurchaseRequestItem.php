<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequestItem extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'item_id',
        'item_sku',
        'item_name',
        'unit',
        'requested_quantity',
        'estimated_unit_cost',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'requested_quantity' => 'decimal:3',
            'estimated_unit_cost' => 'decimal:2',
        ];
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
