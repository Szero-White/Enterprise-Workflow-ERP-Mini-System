@extends('layouts.app')
@section('page_title', __('sales.index.title'))
@section('page_eyebrow', __('sales.eyebrow'))
@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <form method="GET" class="erp-filter-bar"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('sales.index.search_placeholder') }}"><select class="form-select" name="status"><option value="">{{ __('sales.index.all_statuses') }}</option>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(request('status')===$status->value)>{{ $status->label() }}</option>@endforeach</select><button class="btn btn-outline-secondary">{{ __('sales.index.filter') }}</button></form>
    <a href="{{ route('sales.orders.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>{{ __('sales.index.create') }}</a>
</div>
<div class="content-card overflow-hidden"><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>{{ __('sales.index.order_code') }}</th><th>{{ __('sales.index.date') }}</th><th>{{ __('sales.index.customer') }}</th><th>{{ __('sales.index.warehouse') }}</th><th>{{ __('sales.index.creator') }}</th><th>{{ __('sales.index.total') }}</th><th>{{ __('sales.index.status') }}</th><th></th></tr></thead><tbody>
@forelse($orders as $order)<tr><td class="fw-semibold">{{ $order->order_code }}</td><td>{{ $order->order_date->format('d/m/Y') }}</td><td>{{ $order->customer?->name }}</td><td>{{ $order->warehouse?->code }}</td><td>{{ $order->creator?->name }}</td><td class="erp-money">{{ number_format((float)$order->total_amount,0,',','.') }} ₫</td><td><span class="badge rounded-pill {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('sales.orders.show',$order) }}">{{ __('sales.index.detail') }}</a></td></tr>@empty<tr><td colspan="8"><div class="erp-empty"><i class="bi bi-receipt"></i>{{ __('sales.index.empty') }}</div></td></tr>@endforelse
</tbody></table></div>@if($orders->hasPages())<div class="p-3 border-top">{{ $orders->links() }}</div>@endif</div>
@endsection
