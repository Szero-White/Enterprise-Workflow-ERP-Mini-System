<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryStockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $quantity = (float) $this->quantity;
        $reorderLevel = (float) $this->product->reorder_level;

        return [
            'id' => $this->id,
            'quantity' => $quantity,
            'is_low_stock' => $quantity <= $reorderLevel,
            'warehouse' => [
                'id' => $this->warehouse->id,
                'code' => $this->warehouse->code,
                'name' => $this->warehouse->name,
            ],
            'product' => [
                'id' => $this->product->id,
                'sku' => $this->product->sku,
                'name' => $this->product->name,
                'unit' => $this->product->unit,
                'reorder_level' => $reorderLevel,
            ],
        ];
    }
}
