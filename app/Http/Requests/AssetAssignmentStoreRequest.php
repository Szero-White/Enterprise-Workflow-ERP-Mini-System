<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssetAssignmentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assigned_to' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'assigned_at' => ['required', 'date'],
            'expected_return_at' => ['nullable', 'date', 'after_or_equal:assigned_at'],
            'purpose' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
