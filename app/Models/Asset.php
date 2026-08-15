<?php

namespace App\Models;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Asset extends Model
{
    protected $fillable = [
        'asset_code',
        'item_id',
        'goods_receipt_item_id',
        'warehouse_id',
        'serial_number',
        'acquired_at',
        'acquisition_cost',
        'status',
        'condition',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'acquired_at' => 'date',
            'acquisition_cost' => 'integer',
            'status' => AssetStatus::class,
            'condition' => AssetCondition::class,
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function sourceReceiptItem(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptItem::class, 'goods_receipt_item_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(AssetAssignment::class)
            ->whereDoesntHave('returnRecord')
            ->latestOfMany();
    }
}
