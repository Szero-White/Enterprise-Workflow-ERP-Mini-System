<?php

namespace App\Services\Dashboard;

use App\Enums\AssetStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\Asset;
use App\Models\Department;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WorkflowRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DashboardDataService
{
    public function workflowSummary(User $user): array
    {
        if ($user->hasRole('admin')) {
            return [
                'total_users' => User::count(),
                'departments' => Department::count(),
                'total_requests' => WorkflowRequest::count(),
                'pending_requests' => WorkflowRequest::where('status', WorkflowRequest::STATUS_PENDING)->count(),
            ];
        }

        return [
            'my_requests' => WorkflowRequest::where('created_by', $user->id)->count(),
            'pending_for_me' => $this->pendingApprovalQuery($user)->count(),
            'my_approved' => WorkflowRequest::where('created_by', $user->id)
                ->where('status', WorkflowRequest::STATUS_APPROVED)
                ->count(),
            'my_returned' => WorkflowRequest::where('created_by', $user->id)
                ->where('status', WorkflowRequest::STATUS_RETURNED)
                ->count(),
        ];
    }

    public function inventorySummary(): array
    {
        return [
            'active_items' => Item::where('is_active', true)->count(),
            'active_warehouses' => Warehouse::where('is_active', true)->count(),
            'stock_positions' => InventoryStock::count(),
            'low_stock' => InventoryStock::query()
                ->join('items', 'items.id', '=', 'inventory_stocks.item_id')
                ->whereColumn('inventory_stocks.quantity', '<=', 'items.reorder_level')
                ->count(),
        ];
    }

    public function procurementSummary(): array
    {
        return [
            'pending_approval' => PurchaseRequest::where('status', PurchaseRequestStatus::PendingApproval->value)->count(),
            'ready_for_order' => PurchaseRequest::query()
                ->where('status', PurchaseRequestStatus::Approved->value)
                ->whereDoesntHave('purchaseOrders', fn (Builder $order) => $order
                    ->where('status', '!=', PurchaseOrderStatus::Cancelled->value))
                ->count(),
            'open_purchase_orders' => PurchaseOrder::whereIn('status', [
                PurchaseOrderStatus::Draft->value,
                PurchaseOrderStatus::Issued->value,
                PurchaseOrderStatus::PartiallyReceived->value,
            ])->count(),
            'awaiting_receipt' => PurchaseOrder::whereIn('status', [
                PurchaseOrderStatus::Issued->value,
                PurchaseOrderStatus::PartiallyReceived->value,
            ])->count(),
        ];
    }

    public function assetSummary(): array
    {
        return [
            'total_assets' => Asset::count(),
            'available_assets' => Asset::where('status', AssetStatus::Available->value)->count(),
            'assigned_assets' => Asset::where('status', AssetStatus::Assigned->value)->count(),
            'maintenance_assets' => Asset::where('status', AssetStatus::Maintenance->value)->count(),
        ];
    }

    public function lowStockItems(int $limit = 6): Collection
    {
        return InventoryStock::query()
            ->with(['item', 'warehouse'])
            ->join('items', 'items.id', '=', 'inventory_stocks.item_id')
            ->whereColumn('inventory_stocks.quantity', '<=', 'items.reorder_level')
            ->orderBy('inventory_stocks.quantity')
            ->select('inventory_stocks.*')
            ->limit($limit)
            ->get();
    }

    public function recentInventoryMovements(int $limit = 6): Collection
    {
        return InventoryMovement::with(['item', 'warehouse', 'creator'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function latestWorkflowRequests(User $user, int $limit = 6): Collection
    {
        return $this->visibleWorkflowRequests($user)
            ->with(['formTemplate', 'creator', 'currentStep'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    private function visibleWorkflowRequests(User $user): Builder
    {
        $query = WorkflowRequest::query();

        if ($user->hasRole('admin')) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($user) {
            $builder->where('created_by', $user->id)
                ->orWhereHas('histories', fn (Builder $history) => $history->where('actor_id', $user->id))
                ->orWhere(function (Builder $currentApproval) use ($user) {
                    $currentApproval
                        ->where('status', WorkflowRequest::STATUS_PENDING)
                        ->whereHas('currentStep', fn (Builder $step) => $this->scopeApprover($step, $user));
                });
        });
    }

    private function pendingApprovalQuery(User $user): Builder
    {
        return WorkflowRequest::query()
            ->where('status', WorkflowRequest::STATUS_PENDING)
            ->whereHas('currentStep', fn (Builder $step) => $this->scopeApprover($step, $user));
    }

    private function scopeApprover(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $approver) use ($user) {
            $approver->where('approver_user_id', $user->id);

            if ($user->role_id) {
                $approver->orWhere('approver_role_id', $user->role_id);
            }

            if ($user->department_id) {
                $approver->orWhere('approver_department_id', $user->department_id);
            }
        });
    }
}
