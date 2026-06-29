@extends('layouts.app')

@section('page_title', 'Review Request')
@section('page_eyebrow', 'Manager / Approvals')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">Review: {{ $workflowRequest->request_code }}</h2>
        <p class="text-muted mb-0">Inspect submitted data and decide the next approval action.</p>
    </div>
    <span class="badge text-bg-primary rounded-pill px-3 py-2 fw-semibold">{{ $workflowRequest->currentStep?->step_name ?? 'No step' }}</span>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="content-card p-3 p-lg-4">
            <h5 class="mb-3">Request Data</h5>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <tbody>
                    <tr><th>Creator</th><td>{{ $workflowRequest->creator?->name ?? '-' }}</td></tr>
                    <tr><th>Form</th><td>{{ $workflowRequest->formTemplate?->name ?? '-' }}</td></tr>
                    @foreach($workflowRequest->values as $value)
                        <tr><th width="220">{{ $value->field?->label ?? $value->field_key }}</th><td>{{ $value->value ?: '-' }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if($workflowRequest->attachments->isNotEmpty())
                <div class="mt-4">
                    <h6>Attachments</h6>
                    <div class="d-flex flex-column gap-2">
                        @foreach($workflowRequest->attachments as $file)
                            <a href="{{ asset('storage/'.$file->path) }}" target="_blank" class="btn btn-light border text-start">{{ $file->original_name }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="col-lg-5">
        <div class="content-card p-3 p-lg-4 mb-3">
            <h5 class="mb-3">Action</h5>
            <form method="POST" id="approvalForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="comment">Comment</label>
                    <textarea id="comment" name="comment" class="form-control" rows="4"></textarea>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button formaction="{{ route('manager.approvals.approve', $workflowRequest) }}" class="btn btn-success">Approve</button>
                    <button formaction="{{ route('manager.approvals.return', $workflowRequest) }}" class="btn btn-warning">Return</button>
                    <button formaction="{{ route('manager.approvals.reject', $workflowRequest) }}" class="btn btn-danger" onclick="return confirm('Reject this request?')">Reject</button>
                </div>
            </form>
        </div>
        <div class="content-card p-3 p-lg-4">
            <h5 class="mb-3">History</h5>
            @forelse($workflowRequest->histories as $history)
                <div class="border-bottom py-3">
                    <div class="fw-semibold">{{ strtoupper($history->action) }}</div>
                    <div class="text-muted small">{{ $history->actor?->name ?? '-' }} · {{ $history->created_at->format('d/m/Y H:i') }}</div>
                    @if($history->comment)
                        <div class="mt-2">{{ $history->comment }}</div>
                    @endif
                </div>
            @empty
                <div class="text-muted">No approval history yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
