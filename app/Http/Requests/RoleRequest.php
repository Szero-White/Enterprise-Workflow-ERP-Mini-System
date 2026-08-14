<?php

namespace App\Http\Requests;

use App\Models\Role;
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
        /** @var Role|null $role */
        $role = $this->route('role');

        $keyRules = [
            'required',
            'string',
            'max:50',
            'regex:/^[a-z][a-z0-9_]*$/',
            Rule::unique('roles', 'key')->ignore($role?->id),
        ];

        if ($role?->isSystemRole()) {
            $keyRules[] = Rule::in([$role->key]);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'key' => $keyRules,
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
