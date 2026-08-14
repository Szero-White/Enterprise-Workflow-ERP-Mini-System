<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('item_category')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('item_categories', 'code')->ignore($categoryId)],
            'description' => ['nullable', 'string', 'max:4000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
