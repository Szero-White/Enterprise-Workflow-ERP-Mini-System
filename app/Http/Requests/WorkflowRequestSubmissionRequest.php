<?php

namespace App\Http\Requests;

use App\Models\FormTemplate;
use App\Models\WorkflowRequest;
use App\Services\DynamicFieldValidationService;
use Illuminate\Foundation\Http\FormRequest;

class WorkflowRequestSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $formTemplate = $this->resolveFormTemplate();

        if (! $formTemplate) {
            return [];
        }

        return app(DynamicFieldValidationService::class)->rulesFor($formTemplate);
    }

    public function attributes(): array
    {
        $formTemplate = $this->resolveFormTemplate();

        if (! $formTemplate) {
            return [];
        }

        $formTemplate->loadMissing('fields');

        return $formTemplate->fields
            ->pluck('label', 'field_key')
            ->all();
    }

    private function resolveFormTemplate(): ?FormTemplate
    {
        $formTemplate = $this->route('formTemplate');

        if ($formTemplate instanceof FormTemplate) {
            return $formTemplate;
        }

        $workflowRequest = $this->route('workflowRequest');

        if ($workflowRequest instanceof WorkflowRequest) {
            $workflowRequest->loadMissing('formTemplate.fields');

            return $workflowRequest->formTemplate;
        }

        return null;
    }
}
