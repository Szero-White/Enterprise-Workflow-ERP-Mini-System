<?php

namespace App\Http\Requests;

use App\Enums\AssetCondition;
use App\Models\AssetAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssetReturnStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $assignment = $this->route('assignment');

        if (! ($assignment instanceof AssetAssignment)) {
            return false;
        }

        $assignment->loadMissing('asset');

        return $this->user()?->can('receiveReturn', $assignment->asset) ?? false;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')->where(fn ($query) => $query->where('is_active', true))],
            'returned_at' => ['required', 'date'],
            'condition' => ['required', Rule::in([
                AssetCondition::Good->value,
                AssetCondition::NeedsMaintenance->value,
            ])],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
