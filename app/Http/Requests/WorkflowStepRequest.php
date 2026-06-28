<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkflowStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'step_name' => ['required', 'string', 'max:255'],
            'step_order' => ['required', 'integer', 'min:1'],
            'approver_role_id' => ['nullable', 'exists:roles,id'],
            'approver_department_id' => ['nullable', 'exists:departments,id'],
            'approver_user_id' => ['nullable', 'exists:users,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->approver_role_id && ! $this->approver_department_id && ! $this->approver_user_id) {
                $validator->errors()->add('approver_role_id', 'Cần chọn ít nhất một điều kiện người duyệt.');
            }
        });
    }
}
