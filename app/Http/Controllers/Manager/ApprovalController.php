<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalActionRequest;
use App\Models\WorkflowRequest;
use App\Services\ApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->whereHas('currentStep', function ($q) use ($user) {
                $q->where('approver_user_id', $user->id)
                    ->orWhere('approver_role_id', $user->role_id)
                    ->orWhere('approver_department_id', $user->department_id);
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

        $requests = $query->paginate(10)->withQueryString();

        return view('manager.approvals.index', compact('requests'));
    }

    public function show(WorkflowRequest $workflowRequest): View
    {
        $workflowRequest->load(['formTemplate.fields', 'values.field', 'histories.actor', 'histories.step', 'attachments', 'creator', 'currentStep']);
        abort_if(! $workflowRequest->currentStep || ! $workflowRequest->currentStep->canBeApprovedBy(auth()->user()), 403);

        return view('manager.approvals.show', compact('workflowRequest'));
    }

    public function approve(ApprovalActionRequest $request, WorkflowRequest $workflowRequest): RedirectResponse
    {
        $this->approvalService->approve($request->user(), $workflowRequest->load(['currentStep', 'workflowTemplate.steps']), $request->comment);

        return redirect()->route('manager.approvals.index')->with('success', 'Da duyet don.');
    }

    public function reject(ApprovalActionRequest $request, WorkflowRequest $workflowRequest): RedirectResponse
    {
        $this->approvalService->reject($request->user(), $workflowRequest->load(['currentStep', 'workflowTemplate.steps']), $request->comment);

        return redirect()->route('manager.approvals.index')->with('success', 'Da tu choi don.');
    }

    public function returnToEmployee(ApprovalActionRequest $request, WorkflowRequest $workflowRequest): RedirectResponse
    {
        $this->approvalService->returnToEmployee($request->user(), $workflowRequest->load(['currentStep', 'workflowTemplate.steps']), $request->comment);

        return redirect()->route('manager.approvals.index')->with('success', 'Da tra don ve cho nhan vien sua.');
    }
}
