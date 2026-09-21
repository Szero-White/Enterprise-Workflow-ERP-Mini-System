@extends('layouts.app')

@inject('fieldValuePresenter', 'App\Support\Forms\DynamicFieldValuePresenter')

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
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <a href="{{ $isPending ? route('manager.approvals.index') : route('manager.approvals.history') }}" class="btn btn-light border">
                <i class="bi bi-arrow-left"></i>{{ __('ui.back') }}
            </a>
            @if($isPending)
                <span class="erp-current-step-badge">
                    <i class="bi bi-hourglass-split"></i>
                    {{ $workflowRequest->currentStep?->step_name ?? __('ui.no_current_step') }}
                </span>
            @endif
        </div>
    </x-slot:actions>
</x-erp.page-header>

<div class="row g-4 align-items-start erp-approval-layout">
    <div class="col-lg-7">
        <div class="content-card p-3 p-lg-4">
            <h5 class="mb-3">{{ __('ui.request_data') }}</h5>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <tbody>
                    <tr><th>{{ __('ui.creator') }}</th><td>{{ $workflowRequest->creator?->name ?? '-' }}</td></tr>
                    <tr><th>{{ __('ui.form') }}</th><td>{{ $workflowRequest->formTemplate?->name ?? '-' }}</td></tr>
                    @foreach($workflowRequest->values->filter(fn ($value) => $value->field?->field_type !== 'file') as $value)
                        <tr><th width="220">{{ $value->field?->label ?? $value->field_key }}</th><td>{{ $value->field ? $fieldValuePresenter->display($value->field, $value->value) : ($value->value ?: '—') }}</td></tr>
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
                            <tbody>@foreach($workflowRequest->purchaseRequest->items as $line)<tr><td>{{ $line->item_sku }} · {{ $line->item_name }}</td><td>{{ \App\Support\QuantityFormatter::format($line->requested_quantity) }} {{ $line->unit }}</td><td>{{ number_format((int)$line->estimated_unit_cost,0,',','.') }} ₫</td></tr>@endforeach</tbody>
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
    <div class="col-lg-5 erp-approval-sidebar">
        @if($workflowRequest->status === \App\Models\WorkflowRequest::STATUS_PENDING)
        <div class="content-card p-3 p-lg-4 mb-3 erp-approval-action-card">
            <h5 class="mb-2">{{ __('ui.action') }}</h5>
            <div class="erp-approval-action-card__description">{{ __('ui.approval_action_description') }}</div>
            <form method="POST" id="approvalForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="comment">{{ __('ui.comment') }}</label>
                    <textarea id="comment" name="comment" class="form-control @error('comment') is-invalid @enderror" rows="4">{{ old('comment') }}</textarea>
                    <div class="form-text">{{ __('ui.approval_comment_hint') }}</div>
                    @include('partials.form_error', ['field' => 'comment'])
                </div>
                <div class="erp-approval-actions">
                    <button formaction="{{ route('manager.approvals.approve', $workflowRequest) }}" class="btn btn-success">
                        <i class="bi bi-check2-circle"></i>{{ __('ui.approve') }}
                    </button>
                    <button formaction="{{ route('manager.approvals.return', $workflowRequest) }}" class="btn btn-warning">
                        <i class="bi bi-arrow-counterclockwise"></i>{{ __('ui.return') }}
                    </button>
                    <button formaction="{{ route('manager.approvals.reject', $workflowRequest) }}" class="btn btn-danger" data-confirm="{{ __('ui.confirm_reject_request') }}">
                        <i class="bi bi-x-circle"></i>{{ __('ui.reject') }}
                    </button>
                </div>
            </form>
        </div>
        @endif
        @include('partials.approval_progress', ['workflowRequest' => $workflowRequest])
    </div>
</div>
@endsection
