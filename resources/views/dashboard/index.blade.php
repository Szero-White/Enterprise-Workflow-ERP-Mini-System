@extends('layouts.app')

@section('page_title', __('dashboard.page_title'))
@section('page_eyebrow', __('dashboard.eyebrow'))

@section('content')
@if($businessSummary)
    <section class="erp-dashboard-hero">
        <div class="erp-dashboard-hero__content">
            <div>
                <div class="erp-dashboard-hero__kicker">{{ __('dashboard.hero_kicker') }}</div>
                <h2 class="erp-dashboard-hero__title">{{ __('dashboard.hero_title') }}</h2>
                <p class="erp-dashboard-hero__description">{{ __('dashboard.hero_description') }}</p>
            </div>
            <div class="erp-page-actions flex-shrink-0">
                <a href="{{ route('inventory.receipts.create') }}" class="btn btn-outline-light">
                    <i class="bi bi-box-arrow-in-down"></i>{{ __('dashboard.receive_stock') }}
                </a>
                <a href="{{ route('sales.orders.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-lg"></i>{{ __('dashboard.create_sales_order') }}
                </a>
            </div>
        </div>
    </section>

    <x-erp.page-header
        :title="__('dashboard.business_snapshot')"
        :eyebrow="__('dashboard.business_performance')"
        :description="__('dashboard.business_snapshot_description')"
    />

    @php
        $businessCards = [
            ['label' => __('dashboard.confirmed_revenue'), 'value' => number_format($businessSummary['revenue'], 0, ',', '.').' ₫', 'icon' => 'bi-cash-stack', 'tone' => 'success'],
            ['label' => __('dashboard.total_sales_orders'), 'value' => number_format($businessSummary['orders']), 'icon' => 'bi-receipt-cutoff', 'tone' => 'primary'],
            ['label' => __('dashboard.active_customers'), 'value' => number_format($businessSummary['customers']), 'icon' => 'bi-people-fill', 'tone' => 'info'],
            ['label' => __('dashboard.active_products'), 'value' => number_format($businessSummary['products']), 'icon' => 'bi-box-seam-fill', 'tone' => 'dark'],
            ['label' => __('dashboard.low_stock_alerts'), 'value' => number_format($businessSummary['low_stock']), 'icon' => 'bi-exclamation-triangle-fill', 'tone' => 'warning'],
        ];
    @endphp

    <div class="row g-3 mb-4">
        @foreach($businessCards as $card)
            <div class="col-sm-6 col-xl">
                <x-erp.metric-card
                    :label="$card['label']"
                    :value="$card['value']"
                    :icon="$card['icon']"
                    :tone="$card['tone']"
                />
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <x-erp.panel
                :title="__('dashboard.revenue_last_7_days')"
                :subtitle="__('dashboard.confirmed_orders_only')"
                class="h-100"
            >
                <x-slot:actions>
                    <span class="badge rounded-pill text-bg-light border">{{ __('dashboard.seven_days') }}</span>
                </x-slot:actions>
                <div class="erp-chart-wrap"><canvas id="salesChart"></canvas></div>
            </x-erp.panel>
        </div>

        <div class="col-xl-4">
            <x-erp.panel
                :title="__('dashboard.stock_attention')"
                :subtitle="__('dashboard.stock_attention_description')"
                class="h-100"
            >
                <x-slot:actions>
                    <a href="{{ route('inventory.stocks.index', ['low_stock' => 1]) }}" class="btn btn-sm btn-light border">{{ __('dashboard.view_all') }}</a>
                </x-slot:actions>

                <div class="erp-stock-list">
                    @forelse($lowStockProducts as $stock)
                        <div class="erp-stock-item">
                            <div class="erp-stock-item__icon"><i class="bi bi-exclamation-triangle"></i></div>
                            <div class="min-w-0">
                                <div class="erp-record-primary text-truncate">{{ $stock->product?->name }}</div>
                                <div class="erp-record-secondary">{{ $stock->product?->sku }} · {{ $stock->warehouse?->code }}</div>
                            </div>
                            <span class="erp-stock-item__qty">{{ rtrim(rtrim(number_format((float)$stock->quantity, 3, '.', ''), '0'), '.') }}</span>
                        </div>
                    @empty
                        <x-erp.empty-state icon="bi-check2-circle" :title="__('dashboard.no_low_stock')" />
                    @endforelse
                </div>
            </x-erp.panel>
        </div>
    </div>

    <x-erp.panel
        :title="__('dashboard.latest_sales_orders')"
        :subtitle="__('dashboard.latest_sales_orders_description')"
        :flush="true"
        class="mb-4"
    >
        <x-slot:actions>
            <a href="{{ route('sales.orders.index') }}" class="btn btn-sm btn-outline-primary">{{ __('dashboard.manage_orders') }}</a>
        </x-slot:actions>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>{{ __('dashboard.order_code') }}</th>
                        <th>{{ __('dashboard.customer') }}</th>
                        <th>{{ __('dashboard.warehouse') }}</th>
                        <th>{{ __('dashboard.date') }}</th>
                        <th>{{ __('dashboard.total') }}</th>
                        <th>{{ __('dashboard.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($recentOrders as $order)
                    <tr>
                        <td><a class="erp-record-code text-decoration-none" href="{{ route('sales.orders.show', $order) }}">{{ $order->order_code }}</a></td>
                        <td><div class="erp-record-primary">{{ $order->customer?->name }}</div></td>
                        <td>{{ $order->warehouse?->code }}</td>
                        <td>{{ $order->order_date->format('d/m/Y') }}</td>
                        <td class="erp-money">{{ number_format((float)$order->total_amount, 0, ',', '.') }} ₫</td>
                        <td><span class="badge rounded-pill {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-erp.empty-state icon="bi-receipt" :title="__('dashboard.no_sales_orders')" /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-erp.panel>
@endif

<div class="erp-section-heading">
    <div>
        <h2 class="erp-section-heading__title">{{ __('dashboard.internal_workflow') }}</h2>
        <p class="erp-section-heading__text">{{ __('dashboard.internal_workflow_description') }}</p>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach($workflowStats as $stat)
        <div class="col-sm-6 col-xl-3">
            <x-erp.metric-card
                :label="$stat['label']"
                :value="number_format($stat['value'])"
                :icon="$stat['icon']"
                :tone="$stat['tone']"
            />
        </div>
    @endforeach
</div>

<x-erp.panel
    :title="__('dashboard.recent_internal_requests')"
    :subtitle="__('dashboard.recent_internal_requests_description')"
    :flush="true"
>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>{{ __('dashboard.code') }}</th>
                    <th>{{ __('dashboard.form') }}</th>
                    <th>{{ __('dashboard.creator') }}</th>
                    <th>{{ __('dashboard.status') }}</th>
                    <th>{{ __('dashboard.current_step') }}</th>
                    <th>{{ __('dashboard.time') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse($latestRequests as $item)
                <tr>
                    <td class="erp-record-code">{{ $item->request_code }}</td>
                    <td><div class="erp-record-primary">{{ $item->formTemplate?->name ?? '-' }}</div></td>
                    <td>{{ $item->creator?->name ?? '-' }}</td>
                    <td>@include('partials.status_badge', ['status' => $item->status])</td>
                    <td>{{ $item->currentStep?->step_name ?? '-' }}</td>
                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6"><x-erp.empty-state icon="bi-inbox" :title="__('dashboard.no_requests')" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-erp.panel>
@endsection

@if($businessSummary)
<script type="application/json" id="sales-chart-data">{!! json_encode([
    'labels' => $salesChart['labels'],
    'values' => $salesChart['values'],
    'label' => __('dashboard.revenue'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
@endif
