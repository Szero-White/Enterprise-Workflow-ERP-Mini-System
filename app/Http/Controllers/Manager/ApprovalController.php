<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalActionRequest;
use App\Http\Requests\ApprovalFilterRequest;
use App\Models\WorkflowRequest;
use App\Services\ApprovalQueryService;
use App\Services\ApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(
        private ApprovalService $approvalService,
        private ApprovalQueryService $approvalQueryService,
    ) {}

    public function index(ApprovalFilterRequest $request): View
    {
        $filters = $request->filters();
        $requests = $this->approvalQueryService->pendingFor($request->user(), $filters);
        $formTemplates = $this->approvalQueryService->formTemplates();

        return view('manager.approvals.index', compact('requests', 'filters', 'formTemplates'));
    }

    public function show(WorkflowRequest $workflowRequest): View
    {
        Gate::authorize('review', $workflowRequest);

        $workflowRequest->load([
            'formTemplate.fields',
            'values.field',
            'histories.actor',
            'histories.step',
            'attachments',
            'creator',
            'currentStep.approverRole',
            'currentStep.approverDepartment',
            'currentStep.approverUser',
            'workflowTemplate.steps.approverRole',
            'workflowTemplate.steps.approverDepartment',
            'workflowTemplate.steps.approverUser',
            'purchaseRequest.items.item',
        ]);

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

    public function history(ApprovalFilterRequest $request): View
    {
        $filters = $request->filters();
        $histories = $this->approvalQueryService->historyFor($request->user(), $filters);
        $formTemplates = $this->approvalQueryService->formTemplates();

        return view('manager.approvals.history', compact('histories', 'filters', 'formTemplates'));
    }
}
