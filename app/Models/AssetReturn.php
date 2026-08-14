<?php

namespace App\Models;

use App\Enums\AssetCondition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetReturn extends Model
{
    protected $fillable = [
        'asset_assignment_id',
        'received_by',
        'warehouse_id',
        'returned_at',
        'condition',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'returned_at' => 'datetime',
            'condition' => AssetCondition::class,
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AssetAssignment::class, 'asset_assignment_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
