@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h2>Review: {{ $workflowRequest->request_code }}</h2><span class="badge bg-secondary fs-6">{{ $workflowRequest->currentStep?->step_name }}</span></div>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="content-card p-3">
            <h5>Request Data</h5>
            <table class="table">
                <tr><th>Creator</th><td>{{ $workflowRequest->creator?->name }}</td></tr>
                <tr><th>Form</th><td>{{ $workflowRequest->formTemplate?->name }}</td></tr>
                @foreach($workflowRequest->values as $value)
                    <tr><th width="220">{{ $value->field?->label ?? $value->field_key }}</th><td>{{ $value->value }}</td></tr>
                @endforeach
            </table>
            @if($workflowRequest->attachments->isNotEmpty())
                <h6>Attachments</h6>
                <ul>@foreach($workflowRequest->attachments as $file)<li><a href="{{ asset('storage/'.$file->path) }}" target="_blank">{{ $file->original_name }}</a></li>@endforeach</ul>
            @endif
        </div>
    </div>
    <div class="col-lg-5">
        <div class="content-card p-3 mb-3">
            <h5>Action</h5>
            <form method="POST" id="approvalForm">
                @csrf
                <div class="mb-3"><label class="form-label">Comment</label><textarea name="comment" class="form-control" rows="4"></textarea></div>
                <button formaction="{{ route('manager.approvals.approve', $workflowRequest) }}" class="btn btn-success">Approve</button>
                <button formaction="{{ route('manager.approvals.return', $workflowRequest) }}" class="btn btn-warning">Return</button>
                <button formaction="{{ route('manager.approvals.reject', $workflowRequest) }}" class="btn btn-danger" onclick="return confirm('Reject this request?')">Reject</button>
            </form>
        </div>
        <div class="content-card p-3">
            <h5>History</h5>
            @foreach($workflowRequest->histories as $history)
                <div class="border-bottom py-2"><strong>{{ strtoupper($history->action) }}</strong> by {{ $history->actor?->name }}<br><span class="text-muted small">{{ $history->created_at->format('d/m/Y H:i') }}</span><div>{{ $history->comment }}</div></div>
            @endforeach
        </div>
    </div>
</div>
@endsection
