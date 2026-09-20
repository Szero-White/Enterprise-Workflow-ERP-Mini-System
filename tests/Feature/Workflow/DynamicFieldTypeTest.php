<?php

namespace Tests\Feature\Workflow;

use App\Enums\FormFieldType;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Services\DynamicFieldValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DynamicFieldTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_supported_field_types_include_common_business_inputs(): void
    {
        $this->assertSame([
            'text',
            'textarea',
            'number',
            'email',
            'tel',
            'date',
            'time',
            'datetime',
            'url',
            'select',
            'radio',
            'checkbox',
            'file',
        ], FormFieldType::values());
    }

    public function test_dynamic_validation_accepts_valid_common_field_values(): void
    {
        $form = $this->createFormWithCommonFields();
        $input = [
            'work_email' => 'employee@example.com',
            'phone' => '+84 912 345 678',
            'work_date' => '2026-09-22',
            'start_time' => '18:30',
            'meeting_at' => '2026-09-22T18:30',
            'reference_url' => 'https://example.com/reference',
            'confirmed' => '1',
        ];

        $validator = Validator::make($input, app(DynamicFieldValidationService::class)->rulesFor($form, $input));

        $this->assertFalse($validator->fails(), $validator->errors()->toJson());
    }

    public function test_dynamic_validation_rejects_invalid_email_time_datetime_and_url(): void
    {
        $form = $this->createFormWithCommonFields();
        $input = [
            'work_email' => 'not-an-email',
            'phone' => '0900000000',
            'work_date' => '2026-09-22',
            'start_time' => '25:90',
            'meeting_at' => '2026/09/22 18:30',
            'reference_url' => 'not-a-url',
            'confirmed' => '1',
        ];

        $validator = Validator::make($input, app(DynamicFieldValidationService::class)->rulesFor($form, $input));

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('work_email'));
        $this->assertTrue($validator->errors()->has('start_time'));
        $this->assertTrue($validator->errors()->has('meeting_at'));
        $this->assertTrue($validator->errors()->has('reference_url'));
    }

    private function createFormWithCommonFields(): FormTemplate
    {
        $form = FormTemplate::create([
            'name' => 'Common fields',
            'code' => 'COMMON_FIELDS',
            'version' => 1,
            'submission_type' => 'dynamic',
            'is_active' => false,
        ]);

        $types = [
            'work_email' => FormFieldType::Email,
            'phone' => FormFieldType::Phone,
            'work_date' => FormFieldType::Date,
            'start_time' => FormFieldType::Time,
            'meeting_at' => FormFieldType::DateTime,
            'reference_url' => FormFieldType::Url,
            'confirmed' => FormFieldType::Checkbox,
        ];

        $sortOrder = 1;
        foreach ($types as $key => $type) {
            FormField::create([
                'form_template_id' => $form->id,
                'label' => $key,
                'field_key' => $key,
                'field_type' => $type->value,
                'is_required' => true,
                'sort_order' => $sortOrder++,
            ]);
        }

        return $form->fresh('fields');
    }
}
