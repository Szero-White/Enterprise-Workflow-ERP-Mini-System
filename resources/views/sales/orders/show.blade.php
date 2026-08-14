@extends('layouts.app')
@section('page_title', $order->order_code)
@section('page_eyebrow', __('sales.detail_eyebrow'))
@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div class="d-flex align-items-center gap-2"><span class="badge rounded-pill {{ $order->status->badgeClass() }} px-3 py-2">{{ $order->status->label() }}</span><span class="text-muted">{{ __('sales.detail.created_by', ['name' => $order->creator?->name, 'time' => $order->created_at->format('d/m/Y H:i')]) }}</span></div>
    <div class="erp-page-actions">
        <a href="{{ route('sales.orders.index') }}" class="btn btn-light border">{{ __('sales.detail.back_to_list') }}</a>
        @if($order->status === \App\Enums\SalesOrderStatus::Draft)
            <form method="POST" action="{{ route('sales.orders.confirm',$order) }}" onsubmit="return confirm(@js(__('sales.detail.confirm_prompt')))">@csrf<button class="btn btn-success"><i class="bi bi-check2-circle me-2"></i>{{ __('sales.detail.confirm') }}</button></form>
        @endif
        @if($order->status !== \App\Enums\SalesOrderStatus::Cancelled)
            <form method="POST" action="{{ route('sales.orders.cancel',$order) }}" onsubmit="return confirm(@js(__('sales.detail.cancel_prompt')))">@csrf<button class="btn btn-outline-danger">{{ __('sales.detail.cancel') }}</button></form>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="content-card overflow-hidden">
            <div class="p-3 p-lg-4 border-bottom"><h2 class="erp-section-title">{{ __('sales.detail.items_title') }}</h2><p class="erp-section-subtitle">{{ __('sales.detail.items_description') }}</p></div>
            <div class="table-responsive"><table class="table align-middle"><thead><tr><th>SKU</th><th>{{ __('sales.detail.product') }}</th><th>{{ __('sales.detail.quantity_short') }}</th><th>{{ __('sales.detail.unit_price') }}</th><th class="text-end">{{ __('sales.detail.line_total') }}</th></tr></thead><tbody>
            @foreach($order->items as $item)<tr><td class="fw-semibold">{{ $item->product_sku }}</td><td>{{ $item->product_name }}</td><td>{{ rtrim(rtrim(number_format((float)$item->quantity,3,'.',''),'0'),'.') }} {{ $item->unit }}</td><td>{{ number_format((float)$item->unit_price,0,',','.') }} ₫</td><td class="text-end erp-money">{{ number_format((float)$item->line_total,0,',','.') }} ₫</td></tr>@endforeach
            </tbody></table></div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="content-card p-3 p-lg-4 mb-3">
            <h2 class="erp-section-title mb-3">{{ __('sales.detail.order_information') }}</h2>
            <dl class="row mb-0 small"><dt class="col-5 text-muted">{{ __('sales.detail.customer') }}</dt><dd class="col-7 fw-semibold">{{ $order->customer?->name }}</dd><dt class="col-5 text-muted">{{ __('sales.detail.warehouse') }}</dt><dd class="col-7">{{ $order->warehouse?->code }} - {{ $order->warehouse?->name }}</dd><dt class="col-5 text-muted">{{ __('sales.detail.order_date') }}</dt><dd class="col-7">{{ $order->order_date->format('d/m/Y') }}</dd><dt class="col-5 text-muted">{{ __('sales.detail.confirmed_at') }}</dt><dd class="col-7">{{ $order->confirmed_at?->format('d/m/Y H:i') ?? '-' }}</dd></dl>
        </div>
        <div class="content-card p-3 p-lg-4">
            <h2 class="erp-section-title mb-3">{{ __('sales.detail.payment') }}</h2>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">{{ __('sales.detail.subtotal') }}</span><span>{{ number_format((float)$order->subtotal,0,',','.') }} ₫</span></div><div class="d-flex justify-content-between mb-3"><span class="text-muted">{{ __('sales.detail.discount') }}</span><span>- {{ number_format((float)$order->discount_amount,0,',','.') }} ₫</span></div><div class="border-top pt-3 d-flex justify-content-between"><strong>{{ __('sales.detail.grand_total') }}</strong><strong class="fs-5">{{ number_format((float)$order->total_amount,0,',','.') }} ₫</strong></div>
            @if($order->notes)<div class="erp-soft-panel p-3 mt-3 small"><div class="fw-semibold mb-1">{{ __('sales.detail.notes') }}</div>{{ $order->notes }}</div>@endif
        </div>
    </div>
</div>
@endsection
