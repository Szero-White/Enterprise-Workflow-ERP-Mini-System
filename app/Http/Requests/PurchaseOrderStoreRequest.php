<?php

namespace App\Http\Requests;

use App\Support\Money\VndMoney;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseOrderStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'warehouse_id' => [
                'required',
                Rule::exists('warehouses', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'expected_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:3000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.purchase_request_item_id' => ['required', 'integer', 'distinct', 'exists:purchase_request_items,id'],
            'lines.*.unit_cost' => ['required', 'integer', 'min:0', 'max:'.VndMoney::MAX_AMOUNT],
        ];
    }
}
