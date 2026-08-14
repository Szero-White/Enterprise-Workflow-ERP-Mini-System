<?php

namespace App\Http\Requests;

use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssetUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $asset = $this->route('asset');

        return $asset instanceof Asset
            && ($this->user()?->can('update', $asset) ?? false);
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
