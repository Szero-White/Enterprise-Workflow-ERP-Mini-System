@extends('layouts.app')

@section('page_title', $purchaseOrder->po_number)
@section('page_eyebrow', __('procurement.eyebrow'))

@section('content')
    <x-erp.page-header
        :title="$purchaseOrder->po_number"
        :eyebrow="__('procurement.purchase_order.index_title')"
        :description="$purchaseOrder->supplier->name"
    >
        <x-slot:actions>
            @if($purchaseOrder->status === \App\Enums\PurchaseOrderStatus::Draft)
                <form method="POST" action="{{ route('procurement.purchase-orders.issue', $purchaseOrder) }}">
                    @csrf
                    <button class="btn btn-primary">{{ __('procurement.purchase_order.issue') }}</button>
                </form>
            @endif

            @if(in_array($purchaseOrder->status, [\App\Enums\PurchaseOrderStatus::Issued, \App\Enums\PurchaseOrderStatus::PartiallyReceived], true))
                <a class="btn btn-primary" href="{{ route('procurement.goods-receipts.create', $purchaseOrder) }}">
                    {{ __('procurement.purchase_order.receive') }}
                </a>
            @endif

            @if(in_array($purchaseOrder->status, [\App\Enums\PurchaseOrderStatus::Draft, \App\Enums\PurchaseOrderStatus::Issued], true))
                <form method="POST" action="{{ route('procurement.purchase-orders.cancel', $purchaseOrder) }}">
                    @csrf
                    <button class="btn btn-light border">{{ __('procurement.purchase_order.cancel') }}</button>
                </form>
            @endif
        </x-slot:actions>
    </x-erp.page-header>

    <div class="row g-3">
        <div class="col-lg-8">
            <x-erp.panel :title="__('procurement.purchase_order.items')">
                <div class="table-responsive">
                    <table class="table erp-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('procurement.purchase_request.item') }}</th>
                                <th>{{ __('procurement.purchase_order.ordered_quantity') }}</th>
                                <th>{{ __('procurement.purchase_order.received_quantity') }}</th>
                                <th>{{ __('procurement.purchase_order.unit_cost') }}</th>
                                <th class="text-end">{{ __('procurement.purchase_order.line_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $line)
                                <tr>
                                    <td>
                                        <div class="erp-record-primary">{{ $line->item_name }}</div>
                                        <div class="erp-record-secondary">{{ $line->item_sku }}</div>
                                    </td>
                                    <td>{{ $line->ordered_quantity }} {{ $line->unit }}</td>
                                    <td>{{ $line->received_quantity }} {{ $line->unit }}</td>
                                    <td>{{ number_format((float) $line->unit_cost, 0, ',', '.') }} ₫</td>
                                    <td class="text-end">{{ number_format((float) $line->line_total, 0, ',', '.') }} ₫</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-erp.panel>
        </div>

        <div class="col-lg-4">
            <x-erp.panel :title="__('procurement.purchase_order.details')">
                <dl class="row mb-0">
                    <dt class="col-5">{{ __('procurement.purchase_order.status_label') }}</dt>
                    <dd class="col-7"><span class="badge text-bg-light border">{{ $purchaseOrder->status->label() }}</span></dd>

                    <dt class="col-5">{{ __('procurement.purchase_order.warehouse') }}</dt>
                    <dd class="col-7">{{ $purchaseOrder->warehouse->name }}</dd>

                    <dt class="col-5">{{ __('procurement.purchase_order.expected_date') }}</dt>
                    <dd class="col-7">{{ $purchaseOrder->expected_date?->format('d/m/Y') ?? '-' }}</dd>

                    <dt class="col-5">{{ __('procurement.purchase_order.subtotal') }}</dt>
                    <dd class="col-7">{{ number_format((float) $purchaseOrder->subtotal, 0, ',', '.') }} ₫</dd>
                </dl>
            </x-erp.panel>
        </div>
    </div>
@endsection
