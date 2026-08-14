<?php

namespace App\Http\Requests;

use App\Models\WorkflowStep;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkflowStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $type = $this->input('approver_type');

        $this->merge([
            'approver_role_id' => $type === WorkflowStep::APPROVER_ROLE ? $this->input('approver_role_id') : null,
            'approver_department_id' => $type === WorkflowStep::APPROVER_DEPARTMENT ? $this->input('approver_department_id') : null,
            'approver_user_id' => $type === WorkflowStep::APPROVER_USER ? $this->input('approver_user_id') : null,
        ]);
    }

    public function rules(): array
    {
        $workflowTemplate = $this->route('workflowTemplate') ?? $this->route('workflow_template');
        $step = $this->route('step');

        return [
            'step_name' => ['required', 'string', 'max:255'],
            'step_order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('workflow_steps', 'step_order')
                    ->where('workflow_template_id', $workflowTemplate?->id)
                    ->ignore($step?->id),
            ],
            'approver_type' => ['required', Rule::in([
                WorkflowStep::APPROVER_ROLE,
                WorkflowStep::APPROVER_DEPARTMENT,
                WorkflowStep::APPROVER_USER,
            ])],
            'approver_role_id' => [
                Rule::requiredIf($this->input('approver_type') === WorkflowStep::APPROVER_ROLE),
                'nullable',
                'exists:roles,id',
            ],
            'approver_department_id' => [
                Rule::requiredIf($this->input('approver_type') === WorkflowStep::APPROVER_DEPARTMENT),
                'nullable',
                'exists:departments,id',
            ],
            'approver_user_id' => [
                Rule::requiredIf($this->input('approver_type') === WorkflowStep::APPROVER_USER),
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
        ];
    }
}
