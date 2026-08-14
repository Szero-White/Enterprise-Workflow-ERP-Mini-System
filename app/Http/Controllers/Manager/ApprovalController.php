<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalActionRequest;
use App\Models\WorkflowRequest;
use App\Services\ApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(private ApprovalService $approvalService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $query = WorkflowRequest::with(['formTemplate', 'creator', 'currentStep.approverRole', 'currentStep.approverDepartment'])
            ->where('status', WorkflowRequest::STATUS_PENDING)
            ->whereHas('currentStep', fn ($step) => $step->approverFor($user))
            ->latest();

        if ($request->filled('keyword')) {
            $query->where('request_code', 'like', '%'.$request->keyword.'%');
        }

        if ($request->filled('creator_id')) {
            $query->where('created_by', $request->creator_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $requests = $query->paginate(10)->withQueryString();

        return view('manager.approvals.index', compact('requests'));
    }

    public function show(WorkflowRequest $workflowRequest): View
    {
        Gate::authorize('review', $workflowRequest);

        $workflowRequest->load(['formTemplate.fields', 'values.field', 'histories.actor', 'histories.step', 'attachments', 'creator', 'currentStep', 'workflowTemplate.steps', 'purchaseRequest.items.item']);

        return view('manager.approvals.show', compact('workflowRequest'));
    }

    public function approve(ApprovalActionRequest $request, WorkflowRequest $workflowRequest): RedirectResponse
    {
        $this->approvalService->approve($request->user(), $workflowRequest->load(['currentStep', 'workflowTemplate.steps']), $request->comment);

        return redirect()->route('manager.approvals.index')->with('success', __('messages.request_approved'));
    }

    public function reject(ApprovalActionRequest $request, WorkflowRequest $workflowRequest): RedirectResponse
    {
        $this->approvalService->reject($request->user(), $workflowRequest->load(['currentStep', 'workflowTemplate.steps']), $request->comment);

        return redirect()->route('manager.approvals.index')->with('success', __('messages.request_rejected'));
    }

    public function returnToEmployee(ApprovalActionRequest $request, WorkflowRequest $workflowRequest): RedirectResponse
    {
        $this->approvalService->returnToEmployee($request->user(), $workflowRequest->load(['currentStep', 'workflowTemplate.steps']), $request->comment);

        return redirect()->route('manager.approvals.index')->with('success', __('messages.request_returned'));
    }

    public function history(Request $request): View
    {
        $user = $request->user();

        $query = WorkflowRequest::with(['formTemplate', 'creator', 'currentStep.approverRole', 'currentStep.approverDepartment', 'histories' => function ($q) use ($user) {
            $q->where('actor_id', $user->id)->latest();
        }])
            ->whereHas('histories', function ($q) use ($user) {
                $q->where('actor_id', $user->id);
            })
            ->latest();

        if ($request->filled('keyword')) {
            $query->where('request_code', 'like', '%'.$request->keyword.'%');
        }

        if ($request->filled('creator_id')) {
            $query->where('created_by', $request->creator_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('action')) {
            $query->whereHas('histories', function ($q) use ($user, $request) {
                $q->where('actor_id', $user->id)
                  ->where('action', $request->action);
            });
        }

        $requests = $query->paginate(10)->withQueryString();

        return view('manager.approvals.history', compact('requests'));
    }
}
