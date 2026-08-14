@extends('layouts.app')
@section('page_title', __('sales.index.title'))
@section('page_eyebrow', __('sales.eyebrow'))
@section('content')
<x-erp.page-header :title="__('sales.index.title')" :eyebrow="__('sales.eyebrow')" :description="__('sales.index.description')">
    <x-slot:actions>
        <a href="{{ route('sales.orders.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i>{{ __('sales.index.create') }}</a>
    </x-slot:actions>
</x-erp.page-header>

<div class="erp-table-card">
    <div class="erp-table-toolbar">
        <form method="GET" class="erp-filter-bar">
            <div class="erp-filter-search">
                <i class="bi bi-search"></i>
                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('sales.index.search_placeholder') }}">
            </div>
            <select class="form-select" name="status">
                <option value="">{{ __('sales.index.all_statuses') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button class="btn btn-light border"><i class="bi bi-funnel"></i>{{ __('sales.index.filter') }}</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>{{ __('sales.index.order_code') }}</th>
                    <th>{{ __('sales.index.customer') }}</th>
                    <th>{{ __('sales.index.date') }}</th>
                    <th>{{ __('sales.index.warehouse') }}</th>
                    <th>{{ __('sales.index.creator') }}</th>
                    <th>{{ __('sales.index.total') }}</th>
                    <th>{{ __('sales.index.status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                <tr>
                    <td><a class="erp-record-code text-decoration-none" href="{{ route('sales.orders.show', $order) }}">{{ $order->order_code }}</a></td>
                    <td><div class="erp-record-primary">{{ $order->customer?->name }}</div></td>
                    <td>{{ $order->order_date->format('d/m/Y') }}</td>
                    <td><span class="badge rounded-pill text-bg-light border">{{ $order->warehouse?->code }}</span></td>
                    <td>{{ $order->creator?->name }}</td>
                    <td class="erp-money">{{ number_format((float)$order->total_amount, 0, ',', '.') }} ₫</td>
                    <td><span class="badge rounded-pill {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span></td>
                    <td class="text-end"><a class="btn btn-sm btn-light border erp-action-btn" href="{{ route('sales.orders.show', $order) }}" title="{{ __('sales.index.detail') }}"><i class="bi bi-arrow-up-right"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="8"><x-erp.empty-state icon="bi-receipt" :title="__('sales.index.empty')" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())<div class="erp-pagination">{{ $orders->links() }}</div>@endif
</div>
@endsection
