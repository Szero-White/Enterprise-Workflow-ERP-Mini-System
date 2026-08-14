<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SalesOrder;
use App\Models\Warehouse;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\Role;
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
        ])->mapWithKeys(fn ($role) => [$role['key'] => Role::updateOrCreate(['key' => $role['key']], $role)]);

        $departments = collect([
            ['name' => 'Hành chính', 'code' => 'ADMIN'],
            ['name' => 'Nhân sự', 'code' => 'HR'],
            ['name' => 'Kỹ thuật', 'code' => 'ENG'],
            ['name' => 'Kế toán', 'code' => 'ACC'],
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

        $admin = User::where('email', 'admin@example.com')->first();

        $leaveTemplate = FormTemplate::updateOrCreate(['code' => 'LEAVE'], [
            'name' => 'Đơn xin nghỉ phép',
            'description' => 'Biểu mẫu đăng ký nghỉ phép của nhân viên.',
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

        $electronics = ProductCategory::updateOrCreate(['code' => 'ELEC'], [
            'name' => 'Thiết bị điện tử',
            'description' => 'Nhóm sản phẩm điện tử và phụ kiện công nghệ.',
            'is_active' => true,
        ]);
        $office = ProductCategory::updateOrCreate(['code' => 'OFFICE'], [
            'name' => 'Thiết bị văn phòng',
            'description' => 'Thiết bị phục vụ vận hành văn phòng và doanh nghiệp.',
            'is_active' => true,
        ]);

        $products = collect([
            ['sku' => 'LAP-PRO-14', 'name' => 'Laptop Business Pro 14', 'category_id' => $electronics->id, 'unit' => 'cái', 'cost_price' => 18500000, 'sale_price' => 22900000, 'reorder_level' => 8],
            ['sku' => 'MON-27-QHD', 'name' => 'Màn hình 27 inch QHD', 'category_id' => $electronics->id, 'unit' => 'cái', 'cost_price' => 4200000, 'sale_price' => 5690000, 'reorder_level' => 12],
            ['sku' => 'KEY-MECH-87', 'name' => 'Bàn phím cơ 87 phím', 'category_id' => $electronics->id, 'unit' => 'cái', 'cost_price' => 850000, 'sale_price' => 1290000, 'reorder_level' => 20],
            ['sku' => 'CHAIR-ERG-01', 'name' => 'Ghế công thái học Office Pro', 'category_id' => $office->id, 'unit' => 'cái', 'cost_price' => 2600000, 'sale_price' => 3490000, 'reorder_level' => 10],
            ['sku' => 'HUB-USBC-12', 'name' => 'Hub USB-C 12 in 1', 'category_id' => $electronics->id, 'unit' => 'cái', 'cost_price' => 980000, 'sale_price' => 1490000, 'reorder_level' => 18],
        ])->mapWithKeys(function (array $data) {
            $product = Product::updateOrCreate(['sku' => $data['sku']], array_merge($data, [
                'description' => 'Sản phẩm demo cho module bán hàng và quản lý tồn kho.',
                'is_active' => true,
            ]));

            return [$data['sku'] => $product];
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
                ['warehouse_id' => $mainWarehouse->id, 'product_id' => $products[$sku]->id],
                ['quantity' => $hcmQty]
            );
            InventoryStock::updateOrCreate(
                ['warehouse_id' => $backupWarehouse->id, 'product_id' => $products[$sku]->id],
                ['quantity' => $dnQty]
            );
        }

        $customers = collect([
            ['code' => 'CUS-001', 'name' => 'Công ty Minh Phát', 'company_name' => 'Công ty TNHH Minh Phát', 'email' => 'contact@minhphat.example', 'phone' => '0909000001'],
            ['code' => 'CUS-002', 'name' => 'Nguyễn Hoàng Nam', 'company_name' => null, 'email' => 'nam@example.com', 'phone' => '0909000002'],
            ['code' => 'CUS-003', 'name' => 'Công ty Sao Việt', 'company_name' => 'Công ty Cổ phần Sao Việt', 'email' => 'hello@saoviet.example', 'phone' => '0909000003'],
        ])->mapWithKeys(function (array $data) {
            $customer = Customer::updateOrCreate(['code' => $data['code']], array_merge($data, [
                'address' => 'Việt Nam',
                'is_active' => true,
            ]));

            return [$data['code'] => $customer];
        });

        $demoOrders = [
            ['code' => 'SO-DEMO-001', 'customer' => 'CUS-001', 'days_ago' => 1, 'discount' => 500000, 'items' => [['LAP-PRO-14', 2], ['HUB-USBC-12', 3]]],
            ['code' => 'SO-DEMO-002', 'customer' => 'CUS-002', 'days_ago' => 3, 'discount' => 0, 'items' => [['MON-27-QHD', 2], ['KEY-MECH-87', 2]]],
            ['code' => 'SO-DEMO-003', 'customer' => 'CUS-003', 'days_ago' => 5, 'discount' => 300000, 'items' => [['CHAIR-ERG-01', 4]]],
        ];

        foreach ($demoOrders as $demo) {
            $lineItems = collect($demo['items'])->map(function (array $item) use ($products) {
                [$sku, $quantity] = $item;
                $product = $products[$sku];
                $unitPrice = (float) $product->sale_price;

                return [
                    'product_id' => $product->id,
                    'product_sku' => $product->sku,
                    'product_name' => $product->name,
                    'unit' => $product->unit,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $unitPrice * $quantity,
                ];
            });
            $subtotal = (float) $lineItems->sum('line_total');

            $order = SalesOrder::updateOrCreate(['order_code' => $demo['code']], [
                'customer_id' => $customers[$demo['customer']]->id,
                'warehouse_id' => $mainWarehouse->id,
                'created_by' => $admin->id,
                'status' => SalesOrderStatus::Confirmed,
                'order_date' => now()->subDays($demo['days_ago'])->toDateString(),
                'subtotal' => $subtotal,
                'discount_amount' => $demo['discount'],
                'total_amount' => $subtotal - $demo['discount'],
                'notes' => 'Đơn hàng demo phục vụ portfolio.',
                'confirmed_at' => now()->subDays($demo['days_ago']),
            ]);

            $order->items()->delete();
            $order->items()->createMany($lineItems->all());
        }

    }
}
