<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use App\Models\WorkflowRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            ['label' => 'Total Users', 'value' => User::count(), 'icon' => 'bi-people-fill', 'card_class' => 'border-primary-subtle bg-primary-subtle'],
            ['label' => 'Total Departments', 'value' => Department::count(), 'icon' => 'bi-diagram-3-fill', 'card_class' => 'border-info-subtle bg-info-subtle'],
            ['label' => 'Total Requests', 'value' => WorkflowRequest::count(), 'icon' => 'bi-files', 'card_class' => 'border-dark-subtle bg-light'],
            ['label' => 'Pending', 'value' => WorkflowRequest::where('status', WorkflowRequest::STATUS_PENDING)->count(), 'icon' => 'bi-hourglass-split', 'card_class' => 'border-warning-subtle bg-warning-subtle'],
            ['label' => 'Approved', 'value' => WorkflowRequest::where('status', WorkflowRequest::STATUS_APPROVED)->count(), 'icon' => 'bi-check-circle-fill', 'card_class' => 'border-success-subtle bg-success-subtle'],
            ['label' => 'Rejected', 'value' => WorkflowRequest::where('status', WorkflowRequest::STATUS_REJECTED)->count(), 'icon' => 'bi-x-circle-fill', 'card_class' => 'border-danger-subtle bg-danger-subtle'],
            ['label' => 'Returned', 'value' => WorkflowRequest::where('status', WorkflowRequest::STATUS_RETURNED)->count(), 'icon' => 'bi-arrow-counterclockwise', 'card_class' => 'border-info-subtle bg-info-subtle'],
        ];

        $latestRequests = WorkflowRequest::with(['formTemplate', 'creator', 'currentStep'])
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard.index', compact('stats', 'latestRequests'));
    }
}
