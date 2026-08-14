@extends('layouts.app')

@section('page_title', __('procurement.purchase_order.index_title'))
@section('page_eyebrow', __('procurement.eyebrow'))

@section('content')
    <x-erp.page-header
        :title="__('procurement.purchase_order.index_title')"
        :eyebrow="__('procurement.eyebrow')"
        :description="__('procurement.purchase_order.index_description')"
    />

    <x-erp.panel>
        <div class="table-responsive">
            <table class="table erp-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('procurement.purchase_order.number') }}</th>
                        <th>{{ __('procurement.purchase_order.supplier') }}</th>
                        <th>{{ __('procurement.purchase_order.warehouse') }}</th>
                        <th>{{ __('procurement.purchase_order.subtotal') }}</th>
                        <th>{{ __('ui.status') }}</th>
                        <th class="text-end">{{ __('ui.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $purchaseOrder)
                        <tr>
                            <td><span class="erp-record-code">{{ $purchaseOrder->po_number }}</span></td>
                            <td>{{ $purchaseOrder->supplier->name }}</td>
                            <td>{{ $purchaseOrder->warehouse->name }}</td>
                            <td>{{ number_format((float) $purchaseOrder->subtotal, 0, ',', '.') }} ₫</td>
                            <td><span class="badge text-bg-light border">{{ $purchaseOrder->status->label() }}</span></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-light border" href="{{ route('procurement.purchase-orders.show', $purchaseOrder) }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-erp.empty-state icon="bi-file-earmark-text" :title="__('procurement.purchase_order.empty')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchaseOrders->hasPages())
            <div class="erp-pagination">{{ $purchaseOrders->links() }}</div>
        @endif
    </x-erp.panel>
@endsection
