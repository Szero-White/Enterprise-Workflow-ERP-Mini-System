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
            @can('update', $purchaseRequest)
                <a class="btn btn-light border" href="{{ route('procurement.purchase-requests.edit', $purchaseRequest) }}">
                    <i class="bi bi-pencil"></i>
                    {{ __('ui.edit') }}
                </a>
            @endcan

            @if(
                $purchaseRequest->status === \App\Enums\PurchaseRequestStatus::Approved
                && $purchaseRequest->fulfillment_route === \App\Enums\PurchaseRequestFulfillmentRoute::Procurement
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
                                @if($purchaseRequest->fulfillment_route === \App\Enums\PurchaseRequestFulfillmentRoute::Stock)
                                    <th>{{ __('procurement.stock_fulfillment.assignment') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseRequest->items as $line)
                                <tr>
                                    <td>
                                        <div class="erp-record-primary">{{ $line->item_name }}</div>
                                        <div class="erp-record-secondary">{{ $line->item_sku }}</div>
                                    </td>
                                    <td>{{ \App\Support\QuantityFormatter::format($line->requested_quantity) }} {{ $line->unit }}</td>
                                    <td>{{ number_format((int) $line->estimated_unit_cost, 0, ',', '.') }} ₫</td>
                                    <td class="text-end">
                                        {{ number_format($line->estimated_line_total, 0, ',', '.') }} ₫
                                    </td>
                                    @if($purchaseRequest->fulfillment_route === \App\Enums\PurchaseRequestFulfillmentRoute::Stock)
                                        @php
                                            $assignedCount = $line->assetAssignments->count();
                                            $requestedCount = (int) round((float) $line->requested_quantity);
                                            $remainingCount = max(0, $requestedCount - $assignedCount);
                                        @endphp
                                        <td>
                                            <div class="small mb-2">
                                                {{ __('procurement.stock_fulfillment.progress', ['assigned' => $assignedCount, 'requested' => $requestedCount]) }}
                                            </div>
                                            @can('fulfillFromStock', $purchaseRequest)
                                                @if($remainingCount > 0)
                                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('procurement.purchase-requests.stock-fulfillment.show', [$purchaseRequest, $line]) }}">
                                                        <i class="bi bi-person-check"></i>
                                                        {{ __('procurement.stock_fulfillment.assign_now') }}
                                                    </a>
                                                @endif
                                            @endcan
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-erp.panel>
        </div>

        <div class="col-xl-4">
            <x-erp.panel :title="__('procurement.purchase_request.details')">
                <dl class="row mb-0">
                    <dt class="col-5">{{ __('procurement.purchase_request.requester') }}</dt>
                    <dd class="col-7">{{ $purchaseRequest->workflowRequest->creator?->name ?? '-' }}</dd>

                    <dt class="col-5">{{ __('procurement.purchase_request.required_date') }}</dt>
                    <dd class="col-7">{{ $purchaseRequest->required_date?->format('d/m/Y') ?? '-' }}</dd>

                    <dt class="col-5">{{ __('procurement.purchase_request.estimated_total') }}</dt>
                    <dd class="col-7">{{ number_format((int) $purchaseRequest->estimated_total, 0, ',', '.') }} ₫</dd>

                    <dt class="col-5">{{ __('procurement.purchase_request.workflow_status') }}</dt>
                    <dd class="col-7">@include('partials.status_badge', ['status' => $purchaseRequest->workflowRequest->status])</dd>

                    <dt class="col-5">{{ __('procurement.purchase_request.procurement_status') }}</dt>
                    <dd class="col-7"><span class="badge text-bg-light border">{{ $purchaseRequest->status->label() }}</span></dd>

                    <dt class="col-5">{{ __('procurement.purchase_request.fulfillment_route_label') }}</dt>
                    <dd class="col-7"><span class="badge text-bg-light border">{{ $purchaseRequest->fulfillment_route->label() }}</span></dd>

                    @if($purchaseRequest->workflowRequest->status === \App\Models\WorkflowRequest::STATUS_PENDING && $purchaseRequest->workflowRequest->currentStep)
                        <dt class="col-5">{{ __('ui.current_approval_step') }}</dt>
                        <dd class="col-7">
                            <div class="fw-semibold">{{ $purchaseRequest->workflowRequest->currentStep->step_name }}</div>
                            <div class="small text-muted">{{ $purchaseRequest->workflowRequest->currentStep->approverLabel() }}</div>
                        </dd>
                    @endif

                    <dt class="col-5">{{ __('procurement.purchase_request.next_action_label') }}</dt>
                    <dd class="col-7">
                        @if($purchaseRequest->activePurchaseOrder?->status === \App\Enums\PurchaseOrderStatus::Draft)
                            {{ __('procurement.purchase_request.next_action.purchase_order_draft', ['po' => $purchaseRequest->activePurchaseOrder->po_number]) }}
                        @elseif(
                            $purchaseRequest->status === \App\Enums\PurchaseRequestStatus::Approved
                            && $purchaseRequest->fulfillment_route === \App\Enums\PurchaseRequestFulfillmentRoute::Stock
                        )
                            {{ __('procurement.purchase_request.next_action.stock') }}
                        @else
                            {{ $purchaseRequest->status->nextActionLabel() }}
                        @endif
                    </dd>
                </dl>
            </x-erp.panel>

            <div class="mt-3">
                @include('partials.approval_progress', ['workflowRequest' => $purchaseRequest->workflowRequest])
            </div>
        </div>
    </div>
@endsection
