<?php

namespace App\Models;

use App\Support\Money\VndMoney;
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
            'estimated_unit_cost' => 'integer',
        ];
    }

    public function getEstimatedLineTotalAttribute(): int
    {
        return VndMoney::multiplyByQuantity($this->estimated_unit_cost, (string) $this->requested_quantity);
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
