<?php

namespace Database\Seeders;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\Item;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class AssetDemoSeeder extends Seeder
{
    public function run(): void
    {
        $items = Item::whereIn('sku', ['LAP-PRO-14', 'MON-27-QHD'])->get()->keyBy('sku');
        $warehouse = Warehouse::where('code', 'WH-HCM')->firstOrFail();

        foreach ([
            ['asset_code' => 'AST-DEMO-001', 'item' => 'LAP-PRO-14', 'serial' => 'SN-LAP-DEMO-001'],
            ['asset_code' => 'AST-DEMO-002', 'item' => 'LAP-PRO-14', 'serial' => 'SN-LAP-DEMO-002'],
            ['asset_code' => 'AST-DEMO-003', 'item' => 'MON-27-QHD', 'serial' => 'SN-MON-DEMO-001'],
        ] as $demoAsset) {
            $item = $items[$demoAsset['item']];

            Asset::updateOrCreate(['asset_code' => $demoAsset['asset_code']], [
                'item_id' => $item->id,
                'goods_receipt_item_id' => null,
                'warehouse_id' => $warehouse->id,
                'serial_number' => $demoAsset['serial'],
                'acquired_at' => now()->subDays(30)->toDateString(),
                'acquisition_cost' => $item->cost_price,
                'status' => AssetStatus::Available,
                'condition' => AssetCondition::Good,
                'note' => 'Tài sản demo có trước khi hệ thống bắt đầu theo dõi phiếu nhận hàng.',
            ]);
        }
    }
}
