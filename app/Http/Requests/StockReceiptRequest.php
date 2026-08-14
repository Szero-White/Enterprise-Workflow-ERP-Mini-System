<?php

namespace App\Http\Requests;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $itemId = $this->integer('item_id');

            if (! $itemId) {
                return;
            }

            $item = Item::query()->find($itemId);

            if ($item?->is_asset_trackable) {
                $validator->errors()->add('item_id', __('inventory.validation.asset_tracked_manual_receipt_blocked'));
            }
        });
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => [
                'required',
                Rule::exists('warehouses', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'item_id' => [
                'required',
                Rule::exists('items', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
