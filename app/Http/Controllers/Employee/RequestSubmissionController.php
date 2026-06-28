<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
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

    public function store(Request $request, FormTemplate $formTemplate): RedirectResponse
    {
        $formTemplate->load('fields');
        $request->validate($this->dynamicRules($formTemplate));

        $workflowRequest = $this->dynamicRequestService->create($request->user(), $formTemplate, $request);

        return redirect()->route('employee.requests.show', $workflowRequest)->with('success', 'Đã gửi đơn.');
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
        abort_if($workflowRequest->status !== 'returned', 403, 'Chỉ đơn bị trả về mới được sửa.');

        $workflowRequest->load(['formTemplate.fields', 'values']);
        $formTemplate = $workflowRequest->formTemplate;
        $oldValues = $workflowRequest->values->pluck('value', 'field_key');

        return view('employee.requests.edit', compact('workflowRequest', 'formTemplate', 'oldValues'));
    }

    public function update(Request $request, WorkflowRequest $workflowRequest): RedirectResponse
    {
        $this->authorizeOwner($workflowRequest);
        abort_if($workflowRequest->status !== 'returned', 403, 'Chỉ đơn bị trả về mới được sửa.');

        $workflowRequest->load('formTemplate.fields');
        $request->validate($this->dynamicRules($workflowRequest->formTemplate, true));

        $this->dynamicRequestService->updateReturned($request->user(), $workflowRequest, $request);

        return redirect()->route('employee.requests.show', $workflowRequest)->with('success', 'Đã gửi lại đơn.');
    }

    private function dynamicRules(FormTemplate $formTemplate, bool $isUpdate = false): array
    {
        $rules = [];
        foreach ($formTemplate->fields as $field) {
            $fieldRules = $field->is_required ? ['required'] : ['nullable'];

            $fieldRules[] = match ($field->field_type) {
                'number' => 'numeric',
                'date' => 'date',
                'file' => $isUpdate ? 'file|max:5120' : 'file|max:5120',
                default => 'string',
            };

            if ($field->field_type === 'select' && is_array($field->options)) {
                $fieldRules[] = 'in:'.implode(',', $field->options);
            }

            $rules[$field->field_key] = $fieldRules;
        }

        return $rules;
    }

    private function authorizeOwner(WorkflowRequest $workflowRequest): void
    {
        if ($workflowRequest->created_by !== auth()->id() && ! auth()->user()->hasRole('admin')) {
            abort(403);
        }
    }
}
