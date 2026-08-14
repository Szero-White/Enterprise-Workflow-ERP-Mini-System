<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supplierId = $this->route('supplier')?->id;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('suppliers', 'code')->ignore($supplierId)],
            'name' => ['required', 'string', 'max:255'],
            'tax_code' => ['nullable', 'string', 'max:50'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'lead_time_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'notes' => ['nullable', 'string', 'max:4000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
