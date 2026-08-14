@extends('layouts.app')

@section('page_title', $purchaseRequest->workflowRequest->request_code)
@section('page_eyebrow', __('procurement.eyebrow'))

@section('content')
    <x-erp.page-header
        :title="$purchaseRequest->workflowRequest->request_code"
        :eyebrow="__('procurement.purchase_request.index_title')"
        :description="$purchaseRequest->purpose"
    >
        <x-slot:actions>
            @if(
                $purchaseRequest->workflowRequest->status === \App\Models\WorkflowRequest::STATUS_RETURNED
                && $purchaseRequest->workflowRequest->created_by === auth()->id()
            )
                <a class="btn btn-light border" href="{{ route('procurement.purchase-requests.edit', $purchaseRequest) }}">
                    <i class="bi bi-pencil"></i>
                    {{ __('ui.edit') }}
                </a>
            @endif

            @if(
                $purchaseRequest->status === \App\Enums\PurchaseRequestStatus::Approved
                && auth()->user()->hasRole(['admin', 'procurement'])
                && ! $purchaseRequest->activePurchaseOrder
            )
                <a class="btn btn-primary" href="{{ route('procurement.purchase-orders.create', $purchaseRequest) }}">
                    <i class="bi bi-file-earmark-plus"></i>
                    {{ __('procurement.purchase_request.create_po') }}
                </a>
            @endif

            @if($purchaseRequest->activePurchaseOrder)
                <a class="btn btn-primary" href="{{ route('procurement.purchase-orders.show', $purchaseRequest->activePurchaseOrder) }}">
                    <i class="bi bi-file-earmark-text"></i>
                    {{ $purchaseRequest->activePurchaseOrder->po_number }}
                </a>
            @endif
        </x-slot:actions>
    </x-erp.page-header>

    <div class="row g-3">
        <div class="col-xl-8">
            <x-erp.panel :title="__('procurement.purchase_request.items')">
                <div class="table-responsive">
                    <table class="table erp-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('procurement.purchase_request.item') }}</th>
                                <th>{{ __('procurement.purchase_request.quantity') }}</th>
                                <th>{{ __('procurement.purchase_request.estimated_unit_cost') }}</th>
                                <th class="text-end">{{ __('procurement.purchase_request.line_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseRequest->items as $line)
                                <tr>
                                    <td>
                                        <div class="erp-record-primary">{{ $line->item_name }}</div>
                                        <div class="erp-record-secondary">{{ $line->item_sku }}</div>
                                    </td>
                                    <td>{{ $line->requested_quantity }} {{ $line->unit }}</td>
                                    <td>{{ number_format((float) $line->estimated_unit_cost, 0, ',', '.') }} ₫</td>
                                    <td class="text-end">
                                        {{ number_format((float) $line->requested_quantity * (float) $line->estimated_unit_cost, 0, ',', '.') }} ₫
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-erp.panel>
        </div>

        <div class="col-xl-4">
            <x-erp.panel title="Thông tin yêu cầu">
                <dl class="row mb-0">
                    <dt class="col-5">Người yêu cầu</dt>
                    <dd class="col-7">{{ $purchaseRequest->workflowRequest->creator?->name ?? '-' }}</dd>

                    <dt class="col-5">Ngày cần hàng</dt>
                    <dd class="col-7">{{ $purchaseRequest->required_date?->format('d/m/Y') ?? '-' }}</dd>

                    <dt class="col-5">Ngân sách</dt>
                    <dd class="col-7">{{ number_format((float) $purchaseRequest->estimated_total, 0, ',', '.') }} ₫</dd>

                    <dt class="col-5">Workflow</dt>
                    <dd class="col-7">@include('partials.status_badge', ['status' => $purchaseRequest->workflowRequest->status])</dd>

                    <dt class="col-5">Mua sắm</dt>
                    <dd class="col-7"><span class="badge text-bg-light border">{{ $purchaseRequest->status->label() }}</span></dd>
                </dl>
            </x-erp.panel>
        </div>
    </div>
@endsection
