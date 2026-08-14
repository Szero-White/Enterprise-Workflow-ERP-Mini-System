@extends('layouts.app')

@php
    $isPending = $workflowRequest->status === \App\Models\WorkflowRequest::STATUS_PENDING;
    $pageTitle = $isPending ? __('ui.approve_request_title') : __('ui.request_detail');
    $pageEyebrow = __('menu.approval').' / '.($isPending ? __('menu.pending_approvals') : __('menu.approval_history'));
@endphp

@section('page_title', $pageTitle)
@section('page_eyebrow', $pageEyebrow)

@section('content')
<x-erp.page-header
    :title="$pageTitle.': '.$workflowRequest->request_code"
    :eyebrow="__('ui.approval')"
    :description="$isPending ? __('ui.approval_request_description') : __('ui.approval_detail_description')"
>
    <x-slot:actions>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ $isPending ? route('manager.approvals.index') : route('manager.approvals.history') }}" class="btn btn-light border"><i class="bi bi-arrow-left"></i>{{ __('ui.back') }}</a>
            <span class="erp-status-pill {{ $isPending ? 'text-primary bg-primary-subtle' : 'text-secondary bg-light border' }}">{{ $workflowRequest->currentStep?->step_name ?? __('ui.no_current_step') }}</span>
        </div>
    </x-slot:actions>
</x-erp.page-header>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="content-card p-3 p-lg-4">
            <h5 class="mb-3">{{ __('ui.request_data') }}</h5>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <tbody>
                    <tr><th>{{ __('ui.creator') }}</th><td>{{ $workflowRequest->creator?->name ?? '-' }}</td></tr>
                    <tr><th>{{ __('ui.form') }}</th><td>{{ $workflowRequest->formTemplate?->name ?? '-' }}</td></tr>
                    @foreach($workflowRequest->values->filter(fn ($value) => $value->field?->field_type !== 'file') as $value)
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
                            <a href="{{ route('attachments.download', $file) }}" class="btn btn-light border text-start erp-attachment-link"><i class="bi bi-paperclip"></i><span class="text-truncate">{{ $file->original_name }}</span></a>
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
                    <div class="form-text">{{ __('ui.approval_comment_hint') }}</div>
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
