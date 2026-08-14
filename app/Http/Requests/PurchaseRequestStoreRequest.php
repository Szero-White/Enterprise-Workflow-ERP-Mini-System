<?php

namespace App\Http\Requests;

use App\Models\PurchaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $purchaseRequest = $this->route('purchaseRequest');

        if ($purchaseRequest instanceof PurchaseRequest) {
            return $this->user()?->can('update', $purchaseRequest) ?? false;
        }

        return $this->user()?->can('create', PurchaseRequest::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'purpose' => ['required', 'string', 'max:3000'],
            'required_date' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.item_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('items', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.estimated_unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
