<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardDataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardDataService $dashboardDataService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $workflowSummary = $this->dashboardDataService->workflowSummary($user);

        $workflowStats = $user->hasRole('admin')
            ? [
                ['label' => __('dashboard.workflow_total_users'), 'value' => $workflowSummary['total_users'], 'icon' => 'bi-people-fill', 'tone' => 'primary'],
                ['label' => __('dashboard.workflow_departments'), 'value' => $workflowSummary['departments'], 'icon' => 'bi-diagram-3-fill', 'tone' => 'info'],
                ['label' => __('dashboard.workflow_total_requests'), 'value' => $workflowSummary['total_requests'], 'icon' => 'bi-files', 'tone' => 'dark'],
                ['label' => __('dashboard.workflow_pending'), 'value' => $workflowSummary['pending_requests'], 'icon' => 'bi-hourglass-split', 'tone' => 'warning'],
            ]
            : [
                ['label' => __('dashboard.my_requests'), 'value' => $workflowSummary['my_requests'], 'icon' => 'bi-folder2-open', 'tone' => 'primary'],
                ['label' => __('dashboard.pending_for_me'), 'value' => $workflowSummary['pending_for_me'], 'icon' => 'bi-hourglass-split', 'tone' => 'warning'],
                ['label' => __('dashboard.my_approved'), 'value' => $workflowSummary['my_approved'], 'icon' => 'bi-check2-circle', 'tone' => 'info'],
                ['label' => __('dashboard.my_returned'), 'value' => $workflowSummary['my_returned'], 'icon' => 'bi-arrow-return-left', 'tone' => 'dark'],
            ];

        $canViewInventory = $user->hasRole(['admin', 'manager', 'procurement', 'asset_manager']);
        $canViewProcurementOperations = $user->hasRole(['procurement', 'admin']);
        $canViewAssets = $user->hasRole(['asset_manager', 'admin']);

        return view('dashboard.index', [
            'workflowStats' => $workflowStats,
            'inventorySummary' => $canViewInventory ? $this->dashboardDataService->inventorySummary() : null,
            'procurementSummary' => $canViewProcurementOperations ? $this->dashboardDataService->procurementSummary() : null,
            'assetSummary' => $canViewAssets ? $this->dashboardDataService->assetSummary() : null,
            'lowStockItems' => $canViewInventory ? $this->dashboardDataService->lowStockItems() : collect(),
            'recentInventoryMovements' => $canViewInventory ? $this->dashboardDataService->recentInventoryMovements() : collect(),
            'latestRequests' => $this->dashboardDataService->latestWorkflowRequests($user),
        ]);
    }
}
