@extends('layouts.app')

@php
    $isPending = $workflowRequest->status === \App\Models\WorkflowRequest::STATUS_PENDING;
    $pageTitle = $isPending ? 'Duyệt đơn' : 'Chi tiết đơn';
    $pageEyebrow = __('menu.approval').' / '.($isPending ? __('menu.pending_approvals') : __('menu.approval_history'));
@endphp

@section('page_title', $pageTitle)
@section('page_eyebrow', $pageEyebrow)

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">{{ $pageTitle }}: {{ $workflowRequest->request_code }}</h2>
        <p class="text-muted mb-0">{{ $isPending ? 'Kiểm tra dữ liệu đã gửi và chọn thao tác phê duyệt tiếp theo.' : 'Xem chi tiết đơn và lịch sử duyệt.' }}</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <a href="{{ $isPending ? route('manager.approvals.index') : route('manager.approvals.history') }}" class="btn btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
        <span class="badge {{ $isPending ? 'text-bg-primary' : 'text-bg-secondary' }} rounded-pill px-3 py-2 fw-semibold">{{ $workflowRequest->currentStep?->step_name ?? 'Không có bước' }}</span>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="content-card p-3 p-lg-4">
            <h5 class="mb-3">{{ __('ui.request_data') }}</h5>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <tbody>
                    <tr><th>{{ __('ui.creator') }}</th><td>{{ $workflowRequest->creator?->name ?? '-' }}</td></tr>
                    <tr><th>{{ __('ui.form') }}</th><td>{{ $workflowRequest->formTemplate?->name ?? '-' }}</td></tr>
                    @foreach($workflowRequest->values as $value)
                        <tr><th width="220">{{ $value->field?->label ?? $value->field_key }}</th><td>{{ $value->value ?: '-' }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>


            @if($workflowRequest->purchaseRequest)
                <div class="mt-4">
                    <h6>{{ __('procurement.purchase_request.items') }}</h6>
                    <div class="table-responsive">
                        <table class="table erp-table align-middle mb-0">
                            <thead><tr><th>{{ __('procurement.purchase_request.item') }}</th><th>{{ __('procurement.purchase_request.quantity') }}</th><th>{{ __('procurement.purchase_request.estimated_unit_cost') }}</th></tr></thead>
                            <tbody>@foreach($workflowRequest->purchaseRequest->items as $line)<tr><td>{{ $line->item_sku }} · {{ $line->item_name }}</td><td>{{ $line->requested_quantity }} {{ $line->unit }}</td><td>{{ number_format((float)$line->estimated_unit_cost,0,',','.') }} ₫</td></tr>@endforeach</tbody>
                        </table>
                    </div>
                </div>
            @endif

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
        @if($workflowRequest->status === \App\Models\WorkflowRequest::STATUS_PENDING)
        <div class="content-card p-3 p-lg-4 mb-3">
            <h5 class="mb-3">{{ __('ui.action') }}</h5>
            <form method="POST" id="approvalForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="comment">{{ __('ui.comment') }}</label>
                    <textarea id="comment" name="comment" class="form-control @error('comment') is-invalid @enderror" rows="4">{{ old('comment') }}</textarea>
                    <div class="form-text">Bắt buộc nhập khi từ chối hoặc trả đơn về cho người tạo.</div>
                    @include('partials.form_error', ['field' => 'comment'])
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button formaction="{{ route('manager.approvals.approve', $workflowRequest) }}" class="btn btn-success">{{ __('ui.approve') }}</button>
                    <button formaction="{{ route('manager.approvals.return', $workflowRequest) }}" class="btn btn-warning">{{ __('ui.return') }}</button>
                    <button formaction="{{ route('manager.approvals.reject', $workflowRequest) }}" class="btn btn-danger" onclick="return confirm('{{ __('ui.confirm_reject_request') }}')">{{ __('ui.reject') }}</button>
                </div>
            </form>
        </div>
        @endif
        <div class="content-card p-3 p-lg-4">
            <h5 class="mb-3">{{ __('ui.history') }}</h5>
            @forelse($workflowRequest->histories as $history)
                <div class="border-bottom py-3">
                    <div class="fw-semibold">{{ trans()->has('ui.action_labels.'.$history->action) ? __('ui.action_labels.'.$history->action) : strtoupper($history->action) }}</div>
                    <div class="text-muted small">{{ $history->actor?->name ?? '-' }} &middot; {{ $history->acted_at ? $history->acted_at->format('d/m/Y H:i') : $history->created_at->format('d/m/Y H:i') }}</div>
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
