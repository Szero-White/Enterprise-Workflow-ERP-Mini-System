@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h2>{{ $workflowRequest->request_code }}</h2><span class="badge bg-secondary fs-6 badge-status">{{ $workflowRequest->status }}</span></div>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="content-card p-3">
            <h5>Request Data</h5>
            <table class="table">
                @foreach($workflowRequest->values as $value)
                    <tr><th width="220">{{ $value->field?->label ?? $value->field_key }}</th><td>{{ $value->value }}</td></tr>
                @endforeach
            </table>
            @if($workflowRequest->attachments->isNotEmpty())
                <h6>Attachments</h6>
                <ul>
                    @foreach($workflowRequest->attachments as $file)
                        <li><a href="{{ asset('storage/'.$file->path) }}" target="_blank">{{ $file->original_name }}</a></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
    <div class="col-lg-5">
        <div class="content-card p-3">
            <h5>Approval History</h5>
            @foreach($workflowRequest->histories as $history)
                <div class="border-bottom py-2">
                    <strong>{{ strtoupper($history->action) }}</strong> by {{ $history->actor?->name }}<br>
                    <span class="text-muted small">{{ $history->step?->step_name }} · {{ $history->created_at->format('d/m/Y H:i') }}</span>
                    @if($history->comment)<div>{{ $history->comment }}</div>@endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
