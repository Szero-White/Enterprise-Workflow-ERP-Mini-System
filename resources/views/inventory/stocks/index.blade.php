@extends('layouts.app')
@section('page_title', __('inventory.stock.index_title'))
@section('page_eyebrow', __('inventory.eyebrow'))
@section('content')
<div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-3">
    <form method="GET" class="erp-filter-bar">
        <input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('inventory.stock.search_placeholder') }}">
        <select class="form-select" name="warehouse_id"><option value="">{{ __('inventory.stock.all_warehouses') }}</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected((string)request('warehouse_id') === (string)$warehouse->id)>{{ $warehouse->code }} - {{ $warehouse->name }}</option>@endforeach</select>
        <div class="form-check ms-1"><input class="form-check-input" type="checkbox" name="low_stock" value="1" id="low_stock" @checked(request()->boolean('low_stock'))><label class="form-check-label" for="low_stock">{{ __('inventory.stock.low_stock_only') }}</label></div>
        <button class="btn btn-outline-secondary">{{ __('inventory.stock.filter') }}</button>
    </form>
    <a href="{{ route('inventory.receipts.create') }}" class="btn btn-primary"><i class="bi bi-box-arrow-in-down me-2"></i>{{ __('inventory.stock.receive') }}</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="content-card overflow-hidden h-100">
            <div class="p-3 p-lg-4 border-bottom"><h2 class="erp-section-title">{{ __('inventory.stock.stock_by_warehouse') }}</h2><p class="erp-section-subtitle">{{ __('inventory.stock.stock_by_warehouse_description') }}</p></div>
            <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>{{ __('inventory.stock.warehouse') }}</th><th>SKU</th><th>{{ __('inventory.stock.product') }}</th><th>{{ __('inventory.stock.category') }}</th><th>{{ __('inventory.stock.current_stock') }}</th><th>{{ __('inventory.stock.reorder_level') }}</th><th>{{ __('inventory.stock.condition') }}</th></tr></thead><tbody>
            @forelse($stocks as $stock)
                @php($isLow = (float)$stock->quantity <= (float)$stock->product->reorder_level)
                <tr><td><span class="badge text-bg-light border">{{ $stock->warehouse->code }}</span></td><td class="fw-semibold">{{ $stock->product->sku }}</td><td>{{ $stock->product->name }}</td><td>{{ $stock->product->category?->name ?? '-' }}</td><td class="fw-bold">{{ rtrim(rtrim(number_format((float)$stock->quantity,3,'.',''),'0'),'.') }} {{ $stock->product->unit }}</td><td>{{ rtrim(rtrim(number_format((float)$stock->product->reorder_level,3,'.',''),'0'),'.') }}</td><td><span class="badge rounded-pill {{ $isLow ? 'text-bg-warning' : 'text-bg-success' }}">{{ $isLow ? __('inventory.stock.low') : __('inventory.stock.healthy') }}</span></td></tr>
            @empty<tr><td colspan="7"><div class="erp-empty"><i class="bi bi-boxes"></i>{{ __('inventory.stock.empty') }}</div></td></tr>@endforelse
            </tbody></table></div>
            @if($stocks->hasPages())<div class="p-3 border-top">{{ $stocks->links() }}</div>@endif
        </div>
    </div>
    <div class="col-xl-4">
        <div class="content-card overflow-hidden h-100">
            <div class="p-3 p-lg-4 border-bottom"><h2 class="erp-section-title">{{ __('inventory.stock.recent_movements') }}</h2><p class="erp-section-subtitle">{{ __('inventory.stock.recent_movements_description') }}</p></div>
            <div class="p-3 d-grid gap-2">
                @forelse($recentMovements as $movement)
                    <div class="erp-soft-panel p-3">
                        <div class="d-flex justify-content-between gap-2"><div class="fw-semibold">{{ $movement->product?->sku }}</div><span class="{{ (float)$movement->quantity >= 0 ? 'text-success' : 'text-danger' }} fw-bold">{{ (float)$movement->quantity >= 0 ? '+' : '' }}{{ rtrim(rtrim(number_format((float)$movement->quantity,3,'.',''),'0'),'.') }}</span></div>
                        <div class="small text-muted mt-1">{{ $movement->type->label() }} · {{ $movement->warehouse?->code }} · {{ $movement->created_at->format('d/m H:i') }}</div>
                    </div>
                @empty<div class="erp-empty py-4"><i class="bi bi-clock-history"></i>{{ __('inventory.stock.no_movements') }}</div>@endforelse
            </div>
        </div>
    </div>
</div>
@endsection
