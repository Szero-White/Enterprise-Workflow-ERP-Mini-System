@extends('layouts.app')
@section('page_title', $order->order_code)
@section('page_eyebrow', __('sales.detail_eyebrow'))
@section('content')
<x-erp.page-header :title="$order->order_code" :eyebrow="__('sales.detail_eyebrow')" :description="__('sales.detail.description')">
    <x-slot:actions>
        <a href="{{ route('sales.orders.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left"></i>{{ __('sales.detail.back_to_list') }}</a>
        @if($order->status === \App\Enums\SalesOrderStatus::Draft)
            <form method="POST" action="{{ route('sales.orders.confirm', $order) }}" onsubmit="return confirm(@js(__('sales.detail.confirm_prompt')))">
                @csrf
                <button class="btn btn-success"><i class="bi bi-check2-circle"></i>{{ __('sales.detail.confirm') }}</button>
            </form>
        @endif
        @if($order->status !== \App\Enums\SalesOrderStatus::Cancelled)
            <form method="POST" action="{{ route('sales.orders.cancel', $order) }}" onsubmit="return confirm(@js(__('sales.detail.cancel_prompt')))">
                @csrf
                <button class="btn btn-outline-danger"><i class="bi bi-x-circle"></i>{{ __('sales.detail.cancel') }}</button>
            </form>
        @endif
    </x-slot:actions>
</x-erp.page-header>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <span class="badge rounded-pill {{ $order->status->badgeClass() }} px-3 py-2">{{ $order->status->label() }}</span>
    <span class="small text-muted">{{ __('sales.detail.created_by', ['name' => $order->creator?->name, 'time' => $order->created_at->format('d/m/Y H:i')]) }}</span>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <x-erp.panel :title="__('sales.detail.items_title')" :subtitle="__('sales.detail.items_description')" :flush="true" class="h-100">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>SKU</th><th>{{ __('sales.detail.product') }}</th><th>{{ __('sales.detail.quantity_short') }}</th><th>{{ __('sales.detail.unit_price') }}</th><th class="text-end">{{ __('sales.detail.line_total') }}</th></tr></thead>
                    <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td><span class="erp-record-code">{{ $item->product_sku }}</span></td>
                            <td>
                                <div class="erp-product-cell">
                                    <div class="erp-product-thumb"><i class="bi bi-box-seam"></i></div>
                                    <div><div class="erp-record-primary">{{ $item->product_name }}</div><div class="erp-record-secondary">{{ $item->unit }}</div></div>
                                </div>
                            </td>
                            <td>{{ rtrim(rtrim(number_format((float)$item->quantity, 3, '.', ''), '0'), '.') }} {{ $item->unit }}</td>
                            <td class="erp-money">{{ number_format((float)$item->unit_price, 0, ',', '.') }} ₫</td>
                            <td class="text-end erp-money">{{ number_format((float)$item->line_total, 0, ',', '.') }} ₫</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-erp.panel>
    </div>

    <div class="col-xl-4">
        <x-erp.panel :title="__('sales.detail.order_information')" class="mb-3">
            <dl class="row mb-0 small erp-detail-list">
                <dt class="col-5">{{ __('sales.detail.customer') }}</dt><dd class="col-7">{{ $order->customer?->name }}</dd>
                <dt class="col-5">{{ __('sales.detail.warehouse') }}</dt><dd class="col-7">{{ $order->warehouse?->code }} - {{ $order->warehouse?->name }}</dd>
                <dt class="col-5">{{ __('sales.detail.order_date') }}</dt><dd class="col-7">{{ $order->order_date->format('d/m/Y') }}</dd>
                <dt class="col-5">{{ __('sales.detail.confirmed_at') }}</dt><dd class="col-7">{{ $order->confirmed_at?->format('d/m/Y H:i') ?? '-' }}</dd>
            </dl>
        </x-erp.panel>

        <section class="erp-panel overflow-hidden">
            <div class="erp-order-summary__hero">
                <div class="erp-order-summary__label">{{ __('sales.detail.grand_total') }}</div>
                <div class="erp-order-summary__total">{{ number_format((float)$order->total_amount, 0, ',', '.') }} ₫</div>
            </div>
            <div class="erp-panel__body pt-0">
                <div class="erp-order-summary__line"><span class="text-muted">{{ __('sales.detail.subtotal') }}</span><span class="erp-money">{{ number_format((float)$order->subtotal, 0, ',', '.') }} ₫</span></div>
                <div class="erp-order-summary__line"><span class="text-muted">{{ __('sales.detail.discount') }}</span><span class="erp-money">- {{ number_format((float)$order->discount_amount, 0, ',', '.') }} ₫</span></div>
                @if($order->notes)
                    <div class="erp-soft-panel p-3 mt-3 small"><div class="fw-semibold mb-1">{{ __('sales.detail.notes') }}</div><div class="text-muted">{{ $order->notes }}</div></div>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection
