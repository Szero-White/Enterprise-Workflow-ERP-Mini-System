<?php

namespace App\Http\Requests;

use App\Models\WorkflowRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequestFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(array_keys(WorkflowRequest::statuses()))],
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'to_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from_date'],
        ];
    }

    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'keyword' => trim((string) ($validated['keyword'] ?? '')),
            'status' => $validated['status'] ?? null,
            'from_date' => $validated['from_date'] ?? null,
            'to_date' => $validated['to_date'] ?? null,
        ];
    }
}
