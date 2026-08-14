<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_code' => $this->workflowRequest?->request_code,
            'purpose' => $this->purpose,
            'required_date' => $this->required_date?->toDateString(),
            'estimated_total' => (float) $this->estimated_total,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'workflow_status' => $this->workflowRequest?->status,
            'created_by' => $this->whenLoaded('workflowRequest', fn () => $this->workflowRequest?->creator ? [
                'id' => $this->workflowRequest->creator->id,
                'name' => $this->workflowRequest->creator->name,
            ] : null),
            'items' => PurchaseRequestItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
