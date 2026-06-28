<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('role')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:50', Rule::unique('roles', 'key')->ignore($id)],
            'description' => ['nullable', 'string'],
        ];
    }
}
