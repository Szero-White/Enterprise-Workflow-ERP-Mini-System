<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApprovalActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $actionMethod = $this->route()?->getActionMethod();
        $commentRule = in_array($actionMethod, ['reject', 'returnToEmployee'], true) ? 'required' : 'nullable';

        return [
            'comment' => [$commentRule, 'string', 'max:1000'],
        ];
    }
}
