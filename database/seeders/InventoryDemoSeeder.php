<?php

namespace Database\Seeders;

use App\Models\InventoryStock;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class InventoryDemoSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::updateOrCreate(['code' => 'SUP-TECH'], [
            'name' => 'Công ty Công nghệ Demo',
            'tax_code' => '0312345678',
            'contact_name' => 'Nguyễn Minh',
            'email' => 'procurement@supplier-demo.test',
            'phone' => '0900000001',
            'payment_terms' => 'Net 30',
            'lead_time_days' => 5,
            'address' => 'TP. Hồ Chí Minh',
            'is_active' => true,
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

        foreach ([
            'LAP-PRO-14' => [14, 5],
            'MON-27-QHD' => [28, 9],
            'KEY-MECH-87' => [45, 15],
            'CHAIR-ERG-01' => [7, 12],
            'HUB-USBC-12' => [31, 8],
        ] as $sku => [$hcmQty, $dnQty]) {
            InventoryStock::updateOrCreate(
                ['warehouse_id' => $mainWarehouse->id, 'item_id' => $items[$sku]->id],
                ['quantity' => $hcmQty]
            );
            InventoryStock::updateOrCreate(
                ['warehouse_id' => $backupWarehouse->id, 'item_id' => $items[$sku]->id],
                ['quantity' => $dnQty]
            );
        }
    }
}
