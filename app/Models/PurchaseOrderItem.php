<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'purchase_request_item_id',
        'item_id',
        'item_sku',
        'item_name',
        'unit',
        'ordered_quantity',
        'received_quantity',
        'unit_cost',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'ordered_quantity' => 'decimal:3',
            'received_quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function purchaseRequestItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequestItem::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function getOutstandingQuantityAttribute(): float
    {
        return max(0, (float) $this->ordered_quantity - (float) $this->received_quantity);
    }
}
