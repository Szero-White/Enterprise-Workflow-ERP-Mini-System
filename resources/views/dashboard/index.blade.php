@extends('layouts.app')

@section('page_title', __('dashboard.page_title'))
@section('page_eyebrow', __('dashboard.eyebrow'))

@section('content')
<section class="erp-dashboard-welcome">
    <div class="erp-dashboard-welcome__copy">
        <div class="erp-dashboard-welcome__kicker">{{ __('dashboard.hero_kicker') }}</div>
        <h1 class="erp-dashboard-welcome__title">{{ __('dashboard.hero_title') }}</h1>
        <p class="erp-dashboard-welcome__description">{{ __('dashboard.hero_description') }}</p>
    </div>
    @if($inventorySummary)
        <div class="erp-page-actions flex-shrink-0">
            <a href="{{ route('inventory.stocks.index') }}" class="btn btn-light border">
                <i class="bi bi-boxes"></i>{{ __('dashboard.view_inventory') }}
            </a>
            <a href="{{ route('inventory.receipts.create') }}" class="btn btn-primary">
                <i class="bi bi-box-arrow-in-down"></i>{{ __('dashboard.receive_stock') }}
            </a>
        </div>
    @endif
</section>

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

@if($inventorySummary)
    <div class="erp-section-divider"></div>

    <div class="erp-section-heading">
        <div>
            <h2 class="erp-section-heading__title">{{ __('dashboard.inventory_operations') }}</h2>
            <p class="erp-section-heading__text">{{ __('dashboard.inventory_operations_description') }}</p>
        </div>
    </div>

    @php
        $inventoryCards = [
            ['label' => __('dashboard.active_items'), 'value' => number_format($inventorySummary['active_items']), 'icon' => 'bi-box-seam', 'tone' => 'primary'],
            ['label' => __('dashboard.active_warehouses'), 'value' => number_format($inventorySummary['active_warehouses']), 'icon' => 'bi-buildings', 'tone' => 'info'],
            ['label' => __('dashboard.stock_positions'), 'value' => number_format($inventorySummary['stock_positions']), 'icon' => 'bi-boxes', 'tone' => 'dark'],
            ['label' => __('dashboard.low_stock_alerts'), 'value' => number_format($inventorySummary['low_stock']), 'icon' => 'bi-exclamation-circle', 'tone' => 'warning'],
        ];
    @endphp

    <div class="erp-dashboard-metrics">
        @foreach($inventoryCards as $card)
            <x-erp.metric-card
                :label="$card['label']"
                :value="$card['value']"
                :icon="$card['icon']"
                :tone="$card['tone']"
            />
        @endforeach
    </div>

    <div class="erp-dashboard-grid">
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

        <x-erp.panel
            :title="__('dashboard.recent_inventory_movements')"
            :subtitle="__('dashboard.recent_inventory_movements_description')"
            class="h-100"
        >
            <div class="erp-stock-list">
                @forelse($recentInventoryMovements as $movement)
                    <div class="erp-stock-item">
                        <div class="erp-stock-item__icon"><i class="bi bi-arrow-left-right"></i></div>
                        <div class="min-w-0">
                            <div class="erp-record-primary text-truncate">{{ $movement->product?->name }}</div>
                            <div class="erp-record-secondary">{{ $movement->type->label() }} · {{ $movement->warehouse?->code }}</div>
                        </div>
                        <span class="erp-stock-item__qty">{{ $movement->quantity > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format((float)$movement->quantity, 3, '.', ''), '0'), '.') }}</span>
                    </div>
                @empty
                    <x-erp.empty-state icon="bi-clock-history" :title="__('dashboard.no_inventory_movements')" />
                @endforelse
            </div>
        </x-erp.panel>
    </div>
@endif
@endsection
