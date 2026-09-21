<?php

namespace App\Http\Requests;

use App\Models\ApprovalHistory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApprovalFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:120'],
            'requester' => ['nullable', 'string', 'max:120'],
            'form_template_id' => ['nullable', 'integer', 'exists:form_templates,id'],
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'to_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'action' => ['nullable', Rule::in(ApprovalHistory::DECISION_ACTIONS)],
        ];
    }

    public function filters(): array
    {
        return $this->validated();
    }
}
