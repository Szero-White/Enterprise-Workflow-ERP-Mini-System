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
            'users' => User::count(),
            'departments' => Department::count(),
            'requests' => WorkflowRequest::count(),
            'pending' => WorkflowRequest::where('status', 'pending')->count(),
            'approved' => WorkflowRequest::where('status', 'approved')->count(),
            'rejected' => WorkflowRequest::where('status', 'rejected')->count(),
        ];

        $latestRequests = WorkflowRequest::with(['formTemplate', 'creator', 'currentStep'])
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard.index', compact('stats', 'latestRequests'));
    }
}
