<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationFilterRequest extends FormRequest
{
    public const VIEW_ALL = 'all';

    public const VIEW_UNREAD = 'unread';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'view' => ['nullable', Rule::in([self::VIEW_ALL, self::VIEW_UNREAD])],
        ];
    }

    public function viewMode(): string
    {
        return $this->validated('view') ?: self::VIEW_ALL;
    }
}
