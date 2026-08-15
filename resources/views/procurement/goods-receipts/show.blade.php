@extends('layouts.app')

@section('page_title', $goodsReceipt->receipt_number)
@section('page_eyebrow', __('procurement.eyebrow'))

@section('content')
    <x-erp.page-header
        :title="$goodsReceipt->receipt_number"
        :eyebrow="__('procurement.goods_receipt.index_title')"
        :description="$goodsReceipt->purchaseOrder->po_number.' · '.$goodsReceipt->purchaseOrder->supplier->name"
    />

    <div class="row g-3">
        <div class="col-lg-8">
            <x-erp.panel :title="__('procurement.goods_receipt.items_received')">
                <div class="table-responsive">
                    <table class="table erp-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('procurement.goods_receipt.item') }}</th>
                                <th>{{ __('procurement.goods_receipt.quantity') }}</th>
                                <th>{{ __('procurement.goods_receipt.unit_cost') }}</th>
                                <th>{{ __('procurement.goods_receipt.assets') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($goodsReceipt->items as $line)
                                <tr>
                                    <td>
                                        <div class="erp-record-primary">{{ $line->item->name }}</div>
                                        <div class="erp-record-secondary">{{ $line->item->sku }}</div>
                                    </td>
                                    <td>{{ $line->quantity }} {{ $line->item->unit }}</td>
                                    <td>{{ number_format((int) $line->unit_cost, 0, ',', '.') }} ₫</td>
                                    <td>
                                        @if($line->assets->isNotEmpty())
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($line->assets->take(4) as $asset)
                                                    <a class="badge text-bg-light border text-decoration-none" href="{{ route('assets.show', $asset) }}">{{ $asset->asset_code }}</a>
                                                @endforeach
                                                @if($line->assets->count() > 4)
                                                    <span class="badge text-bg-light border">+{{ $line->assets->count() - 4 }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-erp.panel>
        </div>

        <div class="col-lg-4">
            <x-erp.panel :title="__('procurement.goods_receipt.document_info')">
                <dl class="row mb-0">
                    <dt class="col-5">Kho</dt>
                    <dd class="col-7">{{ $goodsReceipt->warehouse->name }}</dd>

                    <dt class="col-5">{{ __('procurement.goods_receipt.receiver') }}</dt>
                    <dd class="col-7">{{ $goodsReceipt->receiver->name }}</dd>

                    <dt class="col-5">{{ __('procurement.goods_receipt.received_at') }}</dt>
                    <dd class="col-7">{{ $goodsReceipt->received_at->format('d/m/Y H:i') }}</dd>

                    <dt class="col-5">{{ __('procurement.goods_receipt.supplier_reference') }}</dt>
                    <dd class="col-7">{{ $goodsReceipt->supplier_reference ?: '-' }}</dd>
                </dl>
            </x-erp.panel>
        </div>
    </div>
@endsection
