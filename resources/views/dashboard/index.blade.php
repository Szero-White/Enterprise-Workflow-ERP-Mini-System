@extends('layouts.app')

@section('page_title', __('dashboard.page_title'))
@section('page_eyebrow', __('dashboard.eyebrow'))

@section('content')
@if($businessSummary)
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
        <div>
            <h2 class="h4 fw-bold mb-1">{{ __('dashboard.business_performance') }}</h2>
            <p class="text-muted mb-0">{{ __('dashboard.business_performance_description') }}</p>
        </div>
        <div class="erp-page-actions">
            <a href="{{ route('inventory.receipts.create') }}" class="btn btn-outline-primary"><i class="bi bi-box-arrow-in-down me-2"></i>{{ __('dashboard.receive_stock') }}</a>
            <a href="{{ route('sales.orders.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>{{ __('dashboard.create_sales_order') }}</a>
        </div>
    </div>

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
                <div class="erp-stat-card">
                    <div class="d-flex justify-content-between gap-3">
                        <div>
                            <div class="erp-stat-label mb-2">{{ $card['label'] }}</div>
                            <div class="erp-stat-value {{ $loop->first ? 'fs-4' : '' }}">{{ $card['value'] }}</div>
                        </div>
                        <div class="erp-stat-icon bg-{{ $card['tone'] }}-subtle text-{{ $card['tone'] }}">
                            <i class="bi {{ $card['icon'] }}"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="content-card p-3 p-lg-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h2 class="erp-section-title">{{ __('dashboard.revenue_last_7_days') }}</h2>
                        <p class="erp-section-subtitle">{{ __('dashboard.confirmed_orders_only') }}</p>
                    </div>
                    <span class="badge rounded-pill text-bg-light border">{{ __('dashboard.seven_days') }}</span>
                </div>
                <div style="height: 290px"><canvas id="salesChart"></canvas></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="content-card p-3 p-lg-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h2 class="erp-section-title">{{ __('dashboard.stock_attention') }}</h2>
                        <p class="erp-section-subtitle">{{ __('dashboard.stock_attention_description') }}</p>
                    </div>
                    <a href="{{ route('inventory.stocks.index', ['low_stock' => 1]) }}" class="small text-decoration-none">{{ __('dashboard.view_all') }}</a>
                </div>
                <div class="d-grid gap-2">
                    @forelse($lowStockProducts as $stock)
                        <div class="erp-soft-panel p-3 d-flex justify-content-between gap-3 align-items-center">
                            <div class="min-w-0">
                                <div class="fw-semibold text-truncate">{{ $stock->product?->name }}</div>
                                <div class="small text-muted">{{ $stock->product?->sku }} · {{ $stock->warehouse?->code }}</div>
                            </div>
                            <span class="badge text-bg-warning rounded-pill">{{ rtrim(rtrim(number_format((float)$stock->quantity, 3, '.', ''), '0'), '.') }}</span>
                        </div>
                    @empty
                        <div class="erp-empty py-4"><i class="bi bi-check2-circle"></i>{{ __('dashboard.no_low_stock') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="content-card mb-4 overflow-hidden">
        <div class="p-3 p-lg-4 d-flex justify-content-between align-items-center gap-3 border-bottom">
            <div>
                <h2 class="erp-section-title">{{ __('dashboard.latest_sales_orders') }}</h2>
                <p class="erp-section-subtitle">{{ __('dashboard.latest_sales_orders_description') }}</p>
            </div>
            <a href="{{ route('sales.orders.index') }}" class="btn btn-sm btn-outline-primary">{{ __('dashboard.manage_orders') }}</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>{{ __('dashboard.order_code') }}</th><th>{{ __('dashboard.customer') }}</th><th>{{ __('dashboard.warehouse') }}</th><th>{{ __('dashboard.date') }}</th><th>{{ __('dashboard.total') }}</th><th>{{ __('dashboard.status') }}</th></tr></thead>
                <tbody>
                @forelse($recentOrders as $order)
                    <tr>
                        <td><a class="fw-semibold text-decoration-none" href="{{ route('sales.orders.show', $order) }}">{{ $order->order_code }}</a></td>
                        <td>{{ $order->customer?->name }}</td>
                        <td>{{ $order->warehouse?->code }}</td>
                        <td>{{ $order->order_date->format('d/m/Y') }}</td>
                        <td class="erp-money">{{ number_format((float)$order->total_amount, 0, ',', '.') }} ₫</td>
                        <td><span class="badge rounded-pill {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="erp-empty"><i class="bi bi-receipt"></i>{{ __('dashboard.no_sales_orders') }}</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

<div class="mb-3">
    <h2 class="h5 fw-bold mb-1">{{ __('dashboard.internal_workflow') }}</h2>
    <p class="text-muted mb-0">{{ __('dashboard.internal_workflow_description') }}</p>
</div>
<div class="row g-3 mb-4">
    @foreach($workflowStats as $stat)
        <div class="col-sm-6 col-xl-3">
            <div class="erp-stat-card">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <div class="erp-stat-label mb-2">{{ $stat['label'] }}</div>
                        <div class="erp-stat-value">{{ number_format($stat['value']) }}</div>
                    </div>
                    <div class="erp-stat-icon bg-{{ $stat['tone'] }}-subtle text-{{ $stat['tone'] }}"><i class="bi {{ $stat['icon'] }}"></i></div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="content-card overflow-hidden">
    <div class="p-3 p-lg-4 border-bottom">
        <h2 class="erp-section-title">{{ __('dashboard.recent_internal_requests') }}</h2>
        <p class="erp-section-subtitle">{{ __('dashboard.recent_internal_requests_description') }}</p>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>{{ __('dashboard.code') }}</th><th>{{ __('dashboard.form') }}</th><th>{{ __('dashboard.creator') }}</th><th>{{ __('dashboard.status') }}</th><th>{{ __('dashboard.current_step') }}</th><th>{{ __('dashboard.time') }}</th></tr></thead>
            <tbody>
            @forelse($latestRequests as $item)
                <tr>
                    <td class="fw-semibold">{{ $item->request_code }}</td>
                    <td>{{ $item->formTemplate?->name ?? '-' }}</td>
                    <td>{{ $item->creator?->name ?? '-' }}</td>
                    <td>@include('partials.status_badge', ['status' => $item->status])</td>
                    <td>{{ $item->currentStep?->step_name ?? '-' }}</td>
                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="erp-empty"><i class="bi bi-inbox"></i>{{ __('dashboard.no_requests') }}</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@if($businessSummary)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    const chartCanvas = document.getElementById('salesChart');
    if (chartCanvas) {
        new Chart(chartCanvas, {
            type: 'line',
            data: {
                labels: @json($salesChart['labels']),
                datasets: [{
                    label: @json(__('dashboard.revenue')),
                    data: @json($salesChart['values']),
                    tension: .35,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 3
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: value => new Intl.NumberFormat('vi-VN').format(value) + ' ₫' } }
                }
            }
        });
    }
</script>
@endpush
@endif
