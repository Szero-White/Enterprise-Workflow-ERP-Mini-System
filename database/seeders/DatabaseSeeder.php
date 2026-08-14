<?php

namespace Database\Seeders;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\Department;
use App\Models\InventoryStock;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Warehouse;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = collect([
            ['name' => 'Quản trị viên', 'key' => 'admin'],
            ['name' => 'Quản lý', 'key' => 'manager'],
            ['name' => 'Nhân viên', 'key' => 'employee'],
            ['name' => 'Nhân sự', 'key' => 'hr'],
            ['name' => 'Giám đốc', 'key' => 'director'],
            ['name' => 'Mua sắm', 'key' => 'procurement'],
            ['name' => 'Tài chính', 'key' => 'finance'],
            ['name' => 'Quản lý tài sản', 'key' => 'asset_manager'],
        ])->mapWithKeys(fn ($role) => [$role['key'] => Role::updateOrCreate(['key' => $role['key']], $role)]);

        $departments = collect([
            ['name' => 'Hành chính', 'code' => 'ADMIN'],
            ['name' => 'Nhân sự', 'code' => 'HR'],
            ['name' => 'Kỹ thuật', 'code' => 'ENG'],
            ['name' => 'Kế toán', 'code' => 'ACC'],
            ['name' => 'Mua sắm', 'code' => 'PROC'],
            ['name' => 'Quản lý tài sản', 'code' => 'ASSET'],
        ])->mapWithKeys(fn ($department) => [$department['code'] => Department::updateOrCreate(['code' => $department['code']], $department)]);

        User::updateOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Quản trị hệ thống',
            'password' => Hash::make('password'),
            'department_id' => $departments['ADMIN']->id,
            'role_id' => $roles['admin']->id,
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'manager@example.com'], [
            'name' => 'Quản lý nhóm',
            'password' => Hash::make('password'),
            'department_id' => $departments['ENG']->id,
            'role_id' => $roles['manager']->id,
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'employee@example.com'], [
            'name' => 'Nhân viên demo',
            'password' => Hash::make('password'),
            'department_id' => $departments['ENG']->id,
            'role_id' => $roles['employee']->id,
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'hr@example.com'], [
            'name' => 'Người duyệt nhân sự',
            'password' => Hash::make('password'),
            'department_id' => $departments['HR']->id,
            'role_id' => $roles['hr']->id,
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'director@example.com'], [
            'name' => 'Người duyệt giám đốc',
            'password' => Hash::make('password'),
            'department_id' => $departments['ADMIN']->id,
            'role_id' => $roles['director']->id,
            'is_active' => true,
        ]);


        User::updateOrCreate(['email' => 'procurement@example.com'], [
            'name' => 'Chuyên viên mua sắm', 'password' => Hash::make('password'),
            'department_id' => $departments['PROC']->id, 'role_id' => $roles['procurement']->id, 'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'finance@example.com'], [
            'name' => 'Chuyên viên tài chính', 'password' => Hash::make('password'),
            'department_id' => $departments['ACC']->id, 'role_id' => $roles['finance']->id, 'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'asset@example.com'], [
            'name' => 'Chuyên viên quản lý tài sản', 'password' => Hash::make('password'),
            'department_id' => $departments['ASSET']->id, 'role_id' => $roles['asset_manager']->id, 'is_active' => true,
        ]);

        $admin = User::where('email', 'admin@example.com')->first();

        $leaveTemplate = FormTemplate::updateOrCreate(['code' => 'LEAVE'], [
            'name' => 'Đơn xin nghỉ phép',
            'description' => 'Biểu mẫu đăng ký nghỉ phép của nhân viên.',
            'submission_type' => 'dynamic',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $fields = [
            ['label' => 'Loại nghỉ phép', 'field_key' => 'leave_type', 'field_type' => 'select', 'is_required' => true, 'options' => ['Nghỉ phép năm', 'Nghỉ ốm', 'Nghỉ không lương'], 'sort_order' => 1],
            ['label' => 'Từ ngày', 'field_key' => 'from_date', 'field_type' => 'date', 'is_required' => true, 'options' => null, 'sort_order' => 2],
            ['label' => 'Đến ngày', 'field_key' => 'to_date', 'field_type' => 'date', 'is_required' => true, 'options' => null, 'sort_order' => 3],
            ['label' => 'Lý do', 'field_key' => 'reason', 'field_type' => 'textarea', 'is_required' => true, 'options' => null, 'sort_order' => 4],
            ['label' => 'Tệp đính kèm', 'field_key' => 'attachment', 'field_type' => 'file', 'is_required' => false, 'options' => null, 'sort_order' => 5],
        ];

        foreach ($fields as $field) {
            FormField::updateOrCreate([
                'form_template_id' => $leaveTemplate->id,
                'field_key' => $field['field_key'],
            ], array_merge($field, ['form_template_id' => $leaveTemplate->id]));
        }

        $workflow = WorkflowTemplate::updateOrCreate([
            'form_template_id' => $leaveTemplate->id,
            'name' => 'Quy trình duyệt nghỉ phép',
        ], [
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        WorkflowStep::updateOrCreate([
            'workflow_template_id' => $workflow->id,
            'step_order' => 1,
        ], [
            'step_name' => 'Quản lý duyệt',
            'approver_role_id' => $roles['manager']->id,
            'approver_department_id' => null,
            'approver_user_id' => null,
        ]);

        WorkflowStep::updateOrCreate([
            'workflow_template_id' => $workflow->id,
            'step_order' => 2,
        ], [
            'step_name' => 'Nhân sự duyệt',
            'approver_role_id' => $roles['hr']->id,
            'approver_department_id' => null,
            'approver_user_id' => null,
        ]);

        WorkflowStep::updateOrCreate([
            'workflow_template_id' => $workflow->id,
            'step_order' => 3,
        ], [
            'step_name' => 'Giám đốc duyệt',
            'approver_role_id' => $roles['director']->id,
            'approver_department_id' => null,
            'approver_user_id' => null,
        ]);

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
            WorkflowStep::updateOrCreate(
                ['workflow_template_id' => $purchaseWorkflow->id, 'step_order' => $order],
                ['step_name' => $name, 'approver_role_id' => $roles[$roleKey]->id, 'approver_department_id' => null, 'approver_user_id' => null]
            );
        }

        Supplier::updateOrCreate(['code' => 'SUP-TECH'], [
            'name' => 'Công ty Công nghệ Demo', 'tax_code' => '0312345678', 'contact_name' => 'Nguyễn Minh',
            'email' => 'procurement@supplier-demo.test', 'phone' => '0900000001', 'payment_terms' => 'Net 30', 'lead_time_days' => 5,
            'address' => 'TP. Hồ Chí Minh', 'is_active' => true,
        ]);

        $electronics = ItemCategory::updateOrCreate(['code' => 'ELEC'], [
            'name' => 'Thiết bị điện tử',
            'description' => 'Nhóm vật tư điện tử và thiết bị công nghệ nội bộ.',
            'is_active' => true,
        ]);
        $office = ItemCategory::updateOrCreate(['code' => 'OFFICE'], [
            'name' => 'Thiết bị văn phòng',
            'description' => 'Vật tư và thiết bị phục vụ vận hành văn phòng.',
            'is_active' => true,
        ]);

        $items = collect([
            ['sku' => 'LAP-PRO-14', 'name' => 'Laptop Business Pro 14', 'category_id' => $electronics->id, 'unit' => 'cái', 'cost_price' => 18500000, 'reorder_level' => 8, 'is_asset_trackable' => true],
            ['sku' => 'MON-27-QHD', 'name' => 'Màn hình 27 inch QHD', 'category_id' => $electronics->id, 'unit' => 'cái', 'cost_price' => 4200000, 'reorder_level' => 12, 'is_asset_trackable' => true],
            ['sku' => 'KEY-MECH-87', 'name' => 'Bàn phím cơ 87 phím', 'category_id' => $electronics->id, 'unit' => 'cái', 'cost_price' => 850000, 'reorder_level' => 20, 'is_asset_trackable' => false],
            ['sku' => 'CHAIR-ERG-01', 'name' => 'Ghế công thái học Office Pro', 'category_id' => $office->id, 'unit' => 'cái', 'cost_price' => 2600000, 'reorder_level' => 10, 'is_asset_trackable' => true],
            ['sku' => 'HUB-USBC-12', 'name' => 'Hub USB-C 12 in 1', 'category_id' => $electronics->id, 'unit' => 'cái', 'cost_price' => 980000, 'reorder_level' => 18, 'is_asset_trackable' => false],
        ])->mapWithKeys(function (array $data) {
            $item = Item::updateOrCreate(['sku' => $data['sku']], array_merge($data, [
                'description' => 'Vật tư demo cho nền tảng quản lý kho và vận hành nội bộ.',
                'is_active' => true,
            ]));

            return [$data['sku'] => $item];
        });

        $mainWarehouse = Warehouse::updateOrCreate(['code' => 'WH-HCM'], [
            'name' => 'Kho trung tâm TP.HCM',
            'address' => 'TP. Hồ Chí Minh',
            'is_active' => true,
        ]);
        $backupWarehouse = Warehouse::updateOrCreate(['code' => 'WH-DN'], [
            'name' => 'Kho chi nhánh Đà Nẵng',
            'address' => 'Đà Nẵng',
            'is_active' => true,
        ]);

        $stockLevels = [
            'LAP-PRO-14' => [14, 5],
            'MON-27-QHD' => [28, 9],
            'KEY-MECH-87' => [45, 15],
            'CHAIR-ERG-01' => [7, 12],
            'HUB-USBC-12' => [31, 8],
        ];

        foreach ($stockLevels as $sku => [$hcmQty, $dnQty]) {
            InventoryStock::updateOrCreate(
                ['warehouse_id' => $mainWarehouse->id, 'item_id' => $items[$sku]->id],
                ['quantity' => $hcmQty]
            );
            InventoryStock::updateOrCreate(
                ['warehouse_id' => $backupWarehouse->id, 'item_id' => $items[$sku]->id],
                ['quantity' => $dnQty]
            );
        }

        foreach ([
            ['asset_code' => 'AST-DEMO-001', 'item' => 'LAP-PRO-14', 'serial' => 'SN-LAP-DEMO-001'],
            ['asset_code' => 'AST-DEMO-002', 'item' => 'LAP-PRO-14', 'serial' => 'SN-LAP-DEMO-002'],
            ['asset_code' => 'AST-DEMO-003', 'item' => 'MON-27-QHD', 'serial' => 'SN-MON-DEMO-001'],
        ] as $demoAsset) {
            Asset::updateOrCreate(['asset_code' => $demoAsset['asset_code']], [
                'item_id' => $items[$demoAsset['item']]->id,
                'goods_receipt_item_id' => null,
                'warehouse_id' => $mainWarehouse->id,
                'serial_number' => $demoAsset['serial'],
                'acquired_at' => now()->subDays(30)->toDateString(),
                'acquisition_cost' => $items[$demoAsset['item']]->cost_price,
                'status' => AssetStatus::Available,
                'condition' => AssetCondition::Good,
                'note' => 'Tài sản demo có trước khi hệ thống bắt đầu theo dõi phiếu nhận hàng.',
            ]);
        }

    }
}
