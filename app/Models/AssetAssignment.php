<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AssetAssignment extends Model
{
    protected $fillable = [
        'asset_id',
        'assigned_to',
        'assigned_by',
        'source_warehouse_id',
        'assigned_at',
        'expected_return_at',
        'purpose',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'expected_return_at' => 'date',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function returnRecord(): HasOne
    {
        return $this->hasOne(AssetReturn::class);
    }
}
