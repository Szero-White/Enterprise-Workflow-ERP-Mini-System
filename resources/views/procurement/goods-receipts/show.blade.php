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
            <x-erp.panel title="Vật tư đã nhận">
                <div class="table-responsive">
                    <table class="table erp-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Vật tư</th>
                                <th>Số lượng</th>
                                <th>Đơn giá</th>
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
                                    <td>{{ number_format((float) $line->unit_cost, 0, ',', '.') }} ₫</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-erp.panel>
        </div>

        <div class="col-lg-4">
            <x-erp.panel title="Thông tin chứng từ">
                <dl class="row mb-0">
                    <dt class="col-5">Kho</dt>
                    <dd class="col-7">{{ $goodsReceipt->warehouse->name }}</dd>

                    <dt class="col-5">Người nhận</dt>
                    <dd class="col-7">{{ $goodsReceipt->receiver->name }}</dd>

                    <dt class="col-5">Thời điểm</dt>
                    <dd class="col-7">{{ $goodsReceipt->received_at->format('d/m/Y H:i') }}</dd>

                    <dt class="col-5">Chứng từ NCC</dt>
                    <dd class="col-7">{{ $goodsReceipt->supplier_reference ?: '-' }}</dd>
                </dl>
            </x-erp.panel>
        </div>
    </div>
@endsection
