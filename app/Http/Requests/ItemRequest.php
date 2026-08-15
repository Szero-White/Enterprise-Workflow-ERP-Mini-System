<?php

namespace App\Http\Requests;

use App\Support\Money\VndMoney;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $itemId = $this->route('item')?->id;

        return [
            'category_id' => ['nullable', 'exists:item_categories,id'],
            'sku' => ['required', 'string', 'max:80', Rule::unique('items', 'sku')->ignore($itemId)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:4000'],
            'unit' => ['required', 'string', 'max:30'],
            'cost_price' => ['required', 'integer', 'min:0', 'max:'.VndMoney::MAX_AMOUNT],
            'reorder_level' => ['required', 'decimal:0,3', 'min:0', 'max:'.VndMoney::MAX_QUANTITY],
            'is_asset_trackable' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
