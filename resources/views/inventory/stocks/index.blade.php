@extends('layouts.app')
@section('page_title', __('inventory.stock.index_title'))
@section('page_eyebrow', __('inventory.eyebrow'))
@section('content')
<x-erp.page-header :title="__('inventory.stock.index_title')" :eyebrow="__('inventory.eyebrow')" :description="__('inventory.stock.index_description')">
    <x-slot:actions>
        <a href="{{ route('inventory.receipts.create') }}" class="btn btn-primary"><i class="bi bi-box-arrow-in-down"></i>{{ __('inventory.stock.receive') }}</a>
    </x-slot:actions>
</x-erp.page-header>

<div class="erp-table-card mb-4">
    <div class="erp-table-toolbar">
        <form method="GET" class="erp-filter-bar">
            <div class="erp-filter-search">
                <i class="bi bi-search"></i>
                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('inventory.stock.search_placeholder') }}">
            </div>
            <select class="form-select" name="warehouse_id">
                <option value="">{{ __('inventory.stock.all_warehouses') }}</option>
                @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected((string)request('warehouse_id') === (string)$warehouse->id)>{{ $warehouse->code }} - {{ $warehouse->name }}</option>
                @endforeach
            </select>
            <label class="form-check d-inline-flex align-items-center gap-2 mb-0 px-2">
                <input class="form-check-input mt-0" type="checkbox" name="low_stock" value="1" @checked(request()->boolean('low_stock'))>
                <span class="form-check-label">{{ __('inventory.stock.low_stock_only') }}</span>
            </label>
            <button class="btn btn-light border"><i class="bi bi-funnel"></i>{{ __('inventory.stock.filter') }}</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>{{ __('inventory.stock.item') }}</th><th>{{ __('inventory.stock.warehouse') }}</th><th>{{ __('inventory.stock.category') }}</th><th>{{ __('inventory.stock.current_stock') }}</th><th>{{ __('inventory.stock.reorder_level') }}</th><th>{{ __('inventory.stock.condition') }}</th></tr></thead>
            <tbody>
            @forelse($stocks as $stock)
                @php($isLow = (float)$stock->quantity <= (float)$stock->item->reorder_level)
                <tr>
                    <td>
                        <div class="erp-item-cell">
                            <div class="erp-item-thumb"><i class="bi bi-box-seam"></i></div>
                            <div class="min-w-0">
                                <div class="erp-record-primary text-truncate">{{ $stock->item->name }}</div>
                                <div class="erp-record-secondary"><span class="erp-record-code">{{ $stock->item->sku }}</span></div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge rounded-pill text-bg-light border">{{ $stock->warehouse->code }}</span></td>
                    <td>{{ $stock->item->category?->name ?? '-' }}</td>
                    <td class="erp-money">{{ rtrim(rtrim(number_format((float)$stock->quantity, 3, '.', ''), '0'), '.') }} {{ $stock->item->unit }}</td>
                    <td>{{ rtrim(rtrim(number_format((float)$stock->item->reorder_level, 3, '.', ''), '0'), '.') }}</td>
                    <td><span class="badge rounded-pill {{ $isLow ? 'text-bg-warning' : 'text-bg-success' }}">{{ $isLow ? __('inventory.stock.low') : __('inventory.stock.healthy') }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6"><x-erp.empty-state icon="bi-boxes" :title="__('inventory.stock.empty')" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($stocks->hasPages())<div class="erp-pagination">{{ $stocks->links() }}</div>@endif
</div>

<x-erp.panel :title="__('inventory.stock.recent_movements')" :subtitle="__('inventory.stock.recent_movements_description')">
    <div class="row g-2">
        @forelse($recentMovements as $movement)
            <div class="col-md-6 col-xl-4">
                <div class="erp-stock-item h-100">
                    <div class="erp-stock-item__icon"><i class="bi bi-arrow-left-right"></i></div>
                    <div class="min-w-0 flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <span class="erp-record-code">{{ $movement->item?->sku }}</span>
                            <span class="{{ (float)$movement->quantity >= 0 ? 'text-success' : 'text-danger' }} fw-bold small">{{ (float)$movement->quantity >= 0 ? '+' : '' }}{{ rtrim(rtrim(number_format((float)$movement->quantity, 3, '.', ''), '0'), '.') }}</span>
                        </div>
                        <div class="erp-record-secondary">{{ $movement->type->label() }} · {{ $movement->warehouse?->code }} · {{ $movement->created_at->format('d/m H:i') }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><x-erp.empty-state icon="bi-clock-history" :title="__('inventory.stock.no_movements')" /></div>
        @endforelse
    </div>
</x-erp.panel>
@endsection
