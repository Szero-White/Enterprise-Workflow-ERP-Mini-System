<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoodsReceiptStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'received_at' => ['required', 'date'],
            'supplier_reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:3000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.purchase_order_item_id' => ['required', 'integer', 'distinct', 'exists:purchase_order_items,id'],
            'lines.*.quantity' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
