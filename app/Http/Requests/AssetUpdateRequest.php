<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssetUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $assetId = $this->route('asset')?->id;

        return [
            'serial_number' => ['nullable', 'string', 'max:120', Rule::unique('assets', 'serial_number')->ignore($assetId)],
            'note' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
