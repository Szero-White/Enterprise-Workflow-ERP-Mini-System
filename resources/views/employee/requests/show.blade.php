@extends('layouts.app')

@inject('fieldValuePresenter', 'App\Support\Forms\DynamicFieldValuePresenter')

@section('page_title', __('ui.request_detail'))
@section('page_eyebrow', __('menu.employee').' / '.__('menu.my_requests'))

@section('content')
<x-erp.page-header :title="$workflowRequest->request_code" :eyebrow="__('ui.my_requests_eyebrow')" :description="__('ui.request_detail_description')">
    <x-slot:actions>
        @include('partials.status_badge', ['status' => $workflowRequest->status])
    </x-slot:actions>
</x-erp.page-header>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="content-card p-3 p-lg-4">
            <h5 class="mb-3">{{ __('ui.request_data') }}</h5>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <tbody>
                    @php($displayValues = $workflowRequest->values->filter(fn ($value) => $value->field?->field_type !== 'file'))
                    @forelse($displayValues as $value)
                        <tr>
                            <th width="220">{{ $value->field?->label ?? $value->field_key }}</th>
                            <td>{{ $value->field ? $fieldValuePresenter->display($value->field, $value->value) : ($value->value ?: '—') }}</td>
                        </tr>
                    @empty
                        <tr><td class="text-muted">{{ __('ui.no_request_data') }}</td></tr>
                    @endforelse
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
    <div class="col-lg-5">
        @include('partials.approval_progress', ['workflowRequest' => $workflowRequest])
    </div>
</div>
@endsection
