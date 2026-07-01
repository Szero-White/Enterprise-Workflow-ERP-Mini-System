@extends('layouts.app')

@section('page_title', 'Chi tiết đơn')
@section('page_eyebrow', __('menu.employee').' / '.__('menu.my_requests'))

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">{{ $workflowRequest->request_code }}</h2>
        <p class="text-muted mb-0">Xem dữ liệu đã gửi, tệp đính kèm và tiến độ phê duyệt.</p>
    </div>
    @include('partials.status_badge', ['status' => $workflowRequest->status])
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="content-card p-3 p-lg-4">
            <h5 class="mb-3">{{ __('ui.request_data') }}</h5>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <tbody>
                    @forelse($workflowRequest->values as $value)
                        <tr>
                            <th width="220">{{ $value->field?->label ?? $value->field_key }}</th>
                            <td>{{ $value->value ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td class="text-muted">{{ __('ui.no_request_data') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($workflowRequest->attachments->isNotEmpty())
                <div class="mt-4">
                    <h6>{{ __('ui.attachments') }}</h6>
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
            <h5 class="mb-3">{{ __('menu.approval_history') }}</h5>
            @forelse($workflowRequest->histories as $history)
                <div class="border-bottom py-3">
                    <div class="fw-semibold">{{ trans()->has('ui.action_labels.'.$history->action) ? __('ui.action_labels.'.$history->action) : strtoupper($history->action) }}</div>
                    <div class="text-muted small">{{ $history->actor?->name ?? '-' }}</div>
                    <div class="text-muted small">{{ $history->step?->step_name ?? '-' }} &middot; {{ $history->created_at->format('d/m/Y H:i') }}</div>
                    @if($history->comment)
                        <div class="mt-2">{{ $history->comment }}</div>
                    @endif
                </div>
            @empty
                <div class="text-muted">{{ __('ui.no_approval_history') }}</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
