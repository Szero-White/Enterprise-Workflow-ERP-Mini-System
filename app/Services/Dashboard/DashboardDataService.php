<?php

namespace App\Services\Dashboard;

use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\WorkflowRequest;
use Illuminate\Support\Collection;

class DashboardDataService
{
    public function businessSummary(): array
    {
        $confirmedOrders = SalesOrder::query()->where('status', SalesOrderStatus::Confirmed->value);

        return [
            'revenue' => (float) (clone $confirmedOrders)->sum('total_amount'),
            'orders' => SalesOrder::count(),
            'customers' => Customer::where('is_active', true)->count(),
            'products' => Product::where('is_active', true)->count(),
            'low_stock' => InventoryStock::query()
                ->join('products', 'products.id', '=', 'inventory_stocks.product_id')
                ->whereColumn('inventory_stocks.quantity', '<=', 'products.reorder_level')
                ->count(),
        ];
    }

    public function salesChart(int $days = 7): array
    {
        $start = now()->startOfDay()->subDays($days - 1);
        $orders = SalesOrder::query()
            ->where('status', SalesOrderStatus::Confirmed->value)
            ->whereDate('order_date', '>=', $start->toDateString())
            ->get(['order_date', 'total_amount']);

        $labels = [];
        $values = [];

        for ($offset = 0; $offset < $days; $offset++) {
            $date = $start->copy()->addDays($offset);
            $labels[] = $date->format('d/m');
            $values[] = round((float) $orders
                ->filter(fn (SalesOrder $order) => $order->order_date->isSameDay($date))
                ->sum('total_amount'), 2);
        }

        return compact('labels', 'values');
    }

    public function recentSalesOrders(int $limit = 6): Collection
    {
        return SalesOrder::with(['customer', 'warehouse'])
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function lowStockProducts(int $limit = 6): Collection
    {
        return InventoryStock::query()
            ->with(['product', 'warehouse'])
            ->join('products', 'products.id', '=', 'inventory_stocks.product_id')
            ->whereColumn('inventory_stocks.quantity', '<=', 'products.reorder_level')
            ->orderBy('inventory_stocks.quantity')
            ->select('inventory_stocks.*')
            ->limit($limit)
            ->get();
    }

    public function latestWorkflowRequests(int $limit = 6): Collection
    {
        return WorkflowRequest::with(['formTemplate', 'creator', 'currentStep'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
