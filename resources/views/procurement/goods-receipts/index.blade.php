@extends('layouts.app')

@section('page_title', __('procurement.goods_receipt.index_title'))
@section('page_eyebrow', __('procurement.eyebrow'))

@section('content')
    <x-erp.page-header
        :title="__('procurement.goods_receipt.index_title')"
        :eyebrow="__('procurement.eyebrow')"
        :description="__('procurement.goods_receipt.index_description')"
    />

    <x-erp.panel>
        <div class="table-responsive">
            <table class="table erp-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('procurement.goods_receipt.number') }}</th>
                        <th>PO</th>
                        <th>{{ __('procurement.purchase_order.supplier') }}</th>
                        <th>Kho</th>
                        <th>{{ __('procurement.goods_receipt.received_at') }}</th>
                        <th class="text-end">{{ __('ui.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($goodsReceipts as $goodsReceipt)
                        <tr>
                            <td><span class="erp-record-code">{{ $goodsReceipt->receipt_number }}</span></td>
                            <td>{{ $goodsReceipt->purchaseOrder->po_number }}</td>
                            <td>{{ $goodsReceipt->purchaseOrder->supplier->name }}</td>
                            <td>{{ $goodsReceipt->warehouse->name }}</td>
                            <td>{{ $goodsReceipt->received_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-light border" href="{{ route('procurement.goods-receipts.show', $goodsReceipt) }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-erp.empty-state icon="bi-box-arrow-in-down" :title="__('procurement.goods_receipt.empty')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($goodsReceipts->hasPages())
            <div class="erp-pagination">{{ $goodsReceipts->links() }}</div>
        @endif
    </x-erp.panel>
@endsection
