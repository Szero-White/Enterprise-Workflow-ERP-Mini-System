@extends('layouts.app')

@section('page_title', 'Request Detail')
@section('page_eyebrow', 'Employee / Requests')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">{{ $workflowRequest->request_code }}</h2>
        <p class="text-muted mb-0">Review submitted data, attachments, and approval progress.</p>
    </div>
    @include('partials.status_badge', ['status' => $workflowRequest->status])
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="content-card p-3 p-lg-4">
            <h5 class="mb-3">Request Data</h5>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <tbody>
                    @forelse($workflowRequest->values as $value)
                        <tr>
                            <th width="220">{{ $value->field?->label ?? $value->field_key }}</th>
                            <td>{{ $value->value ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td class="text-muted">No request data available.</td></tr>
                    @endforelse
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
        <div class="content-card p-3 p-lg-4">
            <h5 class="mb-3">Approval History</h5>
            @forelse($workflowRequest->histories as $history)
                <div class="border-bottom py-3">
                    <div class="fw-semibold">{{ strtoupper($history->action) }}</div>
                    <div class="text-muted small">{{ $history->actor?->name ?? '-' }}</div>
                    <div class="text-muted small">{{ $history->step?->step_name ?? '-' }} · {{ $history->created_at->format('d/m/Y H:i') }}</div>
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
