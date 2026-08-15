<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'sku' => $this->item_sku,
            'name' => $this->item_name,
            'unit' => $this->unit,
            'quantity' => (float) $this->requested_quantity,
            'estimated_unit_cost' => (int) $this->estimated_unit_cost,
            'note' => $this->note,
        ];
    }
}
