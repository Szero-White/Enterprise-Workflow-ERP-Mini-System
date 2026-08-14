<?php

namespace Database\Seeders;

use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use Illuminate\Database\Seeder;

class WorkflowDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $roles = Role::whereIn('key', ['manager', 'hr', 'director', 'procurement', 'finance'])
            ->get()
            ->keyBy('key');

        $leaveTemplate = FormTemplate::updateOrCreate(['code' => 'LEAVE'], [
            'name' => 'Đơn xin nghỉ phép',
            'description' => 'Biểu mẫu đăng ký nghỉ phép của nhân viên.',
            'submission_type' => 'dynamic',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        foreach ([
            ['label' => 'Loại nghỉ phép', 'field_key' => 'leave_type', 'field_type' => 'select', 'is_required' => true, 'options' => ['Nghỉ phép năm', 'Nghỉ ốm', 'Nghỉ không lương'], 'sort_order' => 1],
            ['label' => 'Từ ngày', 'field_key' => 'from_date', 'field_type' => 'date', 'is_required' => true, 'options' => null, 'sort_order' => 2],
            ['label' => 'Đến ngày', 'field_key' => 'to_date', 'field_type' => 'date', 'is_required' => true, 'options' => null, 'sort_order' => 3],
            ['label' => 'Lý do', 'field_key' => 'reason', 'field_type' => 'textarea', 'is_required' => true, 'options' => null, 'sort_order' => 4],
            ['label' => 'Tệp đính kèm', 'field_key' => 'attachment', 'field_type' => 'file', 'is_required' => false, 'options' => null, 'sort_order' => 5],
        ] as $field) {
            FormField::updateOrCreate(
                ['form_template_id' => $leaveTemplate->id, 'field_key' => $field['field_key']],
                array_merge($field, ['form_template_id' => $leaveTemplate->id])
            );
        }

        $leaveWorkflow = WorkflowTemplate::updateOrCreate(
            ['form_template_id' => $leaveTemplate->id, 'name' => 'Quy trình duyệt nghỉ phép'],
            ['is_active' => true, 'created_by' => $admin->id]
        );

        foreach ([
            [1, 'Quản lý duyệt', 'manager'],
            [2, 'Nhân sự duyệt', 'hr'],
            [3, 'Giám đốc duyệt', 'director'],
        ] as [$order, $name, $roleKey]) {
            $this->upsertRoleStep($leaveWorkflow, $order, $name, $roles[$roleKey]->id);
        }

        $purchaseTemplate = FormTemplate::updateOrCreate(['code' => 'PURCHASE_REQUEST'], [
            'name' => 'Yêu cầu mua hàng',
            'description' => 'Yêu cầu mua vật tư nội bộ có danh sách vật tư cấu trúc và quy trình phê duyệt riêng.',
            'submission_type' => 'procurement',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        foreach ([
            ['label' => 'Mục đích / lý do', 'field_key' => 'purpose', 'field_type' => 'textarea', 'is_required' => true, 'sort_order' => 1],
            ['label' => 'Ngày cần hàng', 'field_key' => 'required_date', 'field_type' => 'date', 'is_required' => false, 'sort_order' => 2],
            ['label' => 'Ngân sách dự kiến', 'field_key' => 'estimated_total', 'field_type' => 'number', 'is_required' => true, 'sort_order' => 3],
        ] as $field) {
            FormField::updateOrCreate(
                ['form_template_id' => $purchaseTemplate->id, 'field_key' => $field['field_key']],
                array_merge($field, ['form_template_id' => $purchaseTemplate->id, 'options' => null])
            );
        }

        $purchaseWorkflow = WorkflowTemplate::updateOrCreate(
            ['form_template_id' => $purchaseTemplate->id, 'name' => 'Quy trình duyệt yêu cầu mua hàng'],
            ['is_active' => true, 'created_by' => $admin->id]
        );

        foreach ([
            [1, 'Quản lý bộ phận duyệt', 'manager'],
            [2, 'Mua sắm thẩm định', 'procurement'],
            [3, 'Tài chính kiểm tra ngân sách', 'finance'],
            [4, 'Giám đốc phê duyệt', 'director'],
        ] as [$order, $name, $roleKey]) {
            $this->upsertRoleStep($purchaseWorkflow, $order, $name, $roles[$roleKey]->id);
        }
    }

    private function upsertRoleStep(WorkflowTemplate $workflow, int $order, string $name, int $roleId): void
    {
        WorkflowStep::updateOrCreate(
            ['workflow_template_id' => $workflow->id, 'step_order' => $order],
            [
                'step_name' => $name,
                'approver_type' => WorkflowStep::APPROVER_ROLE,
                'approver_role_id' => $roleId,
                'approver_department_id' => null,
                'approver_user_id' => null,
            ]
        );
    }
}
