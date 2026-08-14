<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use App\Models\WorkflowRequest;
use App\Services\Dashboard\DashboardDataService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardDataService $dashboardDataService)
    {
    }

    public function index(): View
    {
        $workflowStats = [
            ['label' => __('dashboard.workflow_total_users'), 'value' => User::count(), 'icon' => 'bi-people-fill', 'tone' => 'primary'],
            ['label' => __('dashboard.workflow_departments'), 'value' => Department::count(), 'icon' => 'bi-diagram-3-fill', 'tone' => 'info'],
            ['label' => __('dashboard.workflow_total_requests'), 'value' => WorkflowRequest::count(), 'icon' => 'bi-files', 'tone' => 'dark'],
            ['label' => __('dashboard.workflow_pending'), 'value' => WorkflowRequest::where('status', WorkflowRequest::STATUS_PENDING)->count(), 'icon' => 'bi-hourglass-split', 'tone' => 'warning'],
        ];

        $canViewInventory = auth()->user()->hasRole(['admin', 'manager']);

        return view('dashboard.index', [
            'workflowStats' => $workflowStats,
            'inventorySummary' => $canViewInventory ? $this->dashboardDataService->inventorySummary() : null,
            'lowStockItems' => $canViewInventory ? $this->dashboardDataService->lowStockItems() : collect(),
            'recentInventoryMovements' => $canViewInventory ? $this->dashboardDataService->recentInventoryMovements() : collect(),
            'latestRequests' => $this->dashboardDataService->latestWorkflowRequests(),
        ]);
    }
}
