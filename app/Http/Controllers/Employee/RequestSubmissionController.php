<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkflowRequestSubmissionRequest;
use App\Models\FormTemplate;
use App\Models\WorkflowRequest;
use App\Services\DynamicRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RequestSubmissionController extends Controller
{
    public function __construct(private DynamicRequestService $dynamicRequestService)
    {
    }

    public function index(Request $request): View
    {
        $query = WorkflowRequest::with(['formTemplate', 'currentStep'])
            ->where('created_by', $request->user()->id)
            ->latest();

        if ($request->filled('keyword')) {
            $query->where('request_code', 'like', '%'.$request->keyword.'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $requests = $query->paginate(10)->withQueryString();

        return view('employee.requests.index', compact('requests'));
    }

    public function selectTemplate(): View
    {
        $templates = FormTemplate::where('is_active', true)
            ->withCount('fields')
            ->orderBy('name')
            ->get();

        return view('employee.requests.select_template', compact('templates'));
    }

    public function create(FormTemplate $formTemplate): View
    {
        $formTemplate->load('fields');

        return view('employee.requests.form', compact('formTemplate'));
    }

    public function store(WorkflowRequestSubmissionRequest $request, FormTemplate $formTemplate): RedirectResponse
    {
        $formTemplate->load('fields');

        $workflowRequest = $this->dynamicRequestService->create($request->user(), $formTemplate, $request);

        return redirect()->route('employee.requests.show', $workflowRequest)->with('success', __('messages.request_submitted'));
    }

    public function show(WorkflowRequest $workflowRequest): View
    {
        $this->authorizeOwner($workflowRequest);
        $workflowRequest->load(['formTemplate.fields', 'values.field', 'histories.actor', 'histories.step', 'attachments', 'currentStep']);

        return view('employee.requests.show', compact('workflowRequest'));
    }

    public function edit(WorkflowRequest $workflowRequest): View
    {
        $this->authorizeOwner($workflowRequest);
        abort_if($workflowRequest->status !== WorkflowRequest::STATUS_RETURNED, 403, __('messages.returned_request_only'));

        $workflowRequest->load(['formTemplate.fields', 'values']);
        $formTemplate = $workflowRequest->formTemplate;
        $oldValues = $workflowRequest->values->pluck('value', 'field_key');

        return view('employee.requests.edit', compact('workflowRequest', 'formTemplate', 'oldValues'));
    }

    public function update(WorkflowRequestSubmissionRequest $request, WorkflowRequest $workflowRequest): RedirectResponse
    {
        $this->authorizeOwner($workflowRequest);
        abort_if($workflowRequest->status !== WorkflowRequest::STATUS_RETURNED, 403, __('messages.returned_request_only'));

        $workflowRequest->load('formTemplate.fields');

        $this->dynamicRequestService->updateReturned($request->user(), $workflowRequest, $request);

        return redirect()->route('employee.requests.show', $workflowRequest)->with('success', __('messages.request_resubmitted'));
    }

    private function authorizeOwner(WorkflowRequest $workflowRequest): void
    {
        if ($workflowRequest->created_by !== auth()->id() && ! auth()->user()->hasRole('admin')) {
            abort(403);
        }
    }
}
