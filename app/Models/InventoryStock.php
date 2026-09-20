<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStock extends Model
{
    protected $fillable = ['warehouse_id', 'item_id', 'quantity'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3'];
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereHas('item', fn (Builder $builder) => $builder
            ->whereColumn('inventory_stocks.quantity', '<=', 'items.reorder_level'));
    }

    public function scopeHealthy(Builder $query): Builder
    {
        return $query->whereHas('item', fn (Builder $builder) => $builder
            ->whereColumn('inventory_stocks.quantity', '>', 'items.reorder_level'));
    }

    public function scopeLatestActivity(Builder $query): Builder
    {
        return $query
            ->orderByDesc('updated_at')
            ->orderByDesc('id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
