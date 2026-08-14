@extends('layouts.app')
@section('page_title', __('sales.create.title'))
@section('page_eyebrow', __('sales.eyebrow'))
@section('content')
<x-erp.page-header :title="__('sales.create.title')" :eyebrow="__('sales.eyebrow')" :description="__('sales.create.description')">
    <x-slot:actions>
        <a href="{{ route('sales.orders.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left"></i>{{ __('sales.create.cancel') }}</a>
    </x-slot:actions>
</x-erp.page-header>

<form method="POST" action="{{ route('sales.orders.store') }}" id="order-form">
    @csrf
    <div class="erp-order-layout">
        <div>
            <section class="erp-form-section mb-3">
                <div class="erp-form-section__header">
                    <h2 class="erp-form-section__title">{{ __('sales.create.order_information') }}</h2>
                    <p class="erp-form-section__subtitle">{{ __('sales.create.description') }}</p>
                </div>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label erp-required">{{ __('sales.create.customer') }}</label>
                        <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                            <option value="">{{ __('sales.create.select_customer') }}</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected((string)old('customer_id') === (string)$customer->id)>{{ $customer->code }} - {{ $customer->name }}</option>
                            @endforeach
                        </select>
                        @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label erp-required">{{ __('sales.create.warehouse') }}</label>
                        <select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                            <option value="">{{ __('sales.create.select_warehouse') }}</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected((string)old('warehouse_id') === (string)$warehouse->id)>{{ $warehouse->code }} - {{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                        @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label erp-required">{{ __('sales.create.order_date') }}</label>
                        <input type="date" name="order_date" class="form-control" value="{{ old('order_date', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('sales.create.notes') }}</label>
                        <textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </section>

            <section class="erp-panel">
                <header class="erp-panel__header">
                    <div>
                        <h2 class="erp-panel__title">{{ __('sales.create.products') }}</h2>
                        <p class="erp-panel__subtitle">{{ __('sales.create.products_description') }}</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-row"><i class="bi bi-plus-lg"></i>{{ __('sales.create.add_line') }}</button>
                </header>
                <div class="table-responsive">
                    <table class="table align-middle erp-order-items-table">
                        <thead>
                            <tr>
                                <th class="erp-order-col-product">{{ __('sales.create.products') }}</th>
                                <th class="erp-order-col-quantity">{{ __('sales.create.quantity') }}</th>
                                <th class="erp-order-col-price">{{ __('sales.create.unit_price') }}</th>
                                <th class="erp-order-col-total">{{ __('sales.create.line_total') }}</th>
                                <th class="erp-order-col-action"></th>
                            </tr>
                        </thead>
                        <tbody id="order-items"></tbody>
                    </table>
                </div>
                @error('items')<div class="alert alert-danger m-3">{{ $message }}</div>@enderror
            </section>
        </div>

        <aside class="erp-order-summary">
            <section class="erp-panel">
                <div class="erp-order-summary__hero">
                    <div class="erp-order-summary__label">{{ __('sales.create.total') }}</div>
                    <div class="erp-order-summary__total" id="total-view">0 ₫</div>
                </div>
                <div class="erp-panel__body pt-0">
                    <div class="erp-order-summary__line">
                        <span class="text-muted">{{ __('sales.create.subtotal') }}</span>
                        <strong id="subtotal-view">0 ₫</strong>
                    </div>
                    <div class="mt-2 mb-3">
                        <label class="form-label">{{ __('sales.create.discount') }}</label>
                        <div class="input-group">
                            <input type="number" min="0" step="0.01" name="discount_amount" id="discount" class="form-control" value="{{ old('discount_amount', 0) }}">
                            <span class="input-group-text">₫</span>
                        </div>
                    </div>
                    <div class="erp-inline-note mb-3">
                        <i class="bi bi-shield-check flex-shrink-0"></i>
                        <div><strong>{{ __('sales.create.pricing_policy') }}</strong><br>{{ __('sales.create.pricing_policy_hint') }}</div>
                    </div>
                    <div class="erp-inline-note mb-3">
                        <i class="bi bi-info-circle flex-shrink-0"></i>
                        <div>{{ __('sales.create.draft_notice', ['status' => __('sales.status.draft')]) }}</div>
                    </div>
                    <button class="btn btn-primary w-100"><i class="bi bi-save2"></i>{{ __('sales.create.save_draft') }}</button>
                    <a href="{{ route('sales.orders.index') }}" class="btn btn-light border w-100 mt-2">{{ __('sales.create.cancel') }}</a>
                </div>
            </section>
        </aside>
    </div>
</form>

<script type="application/json" id="sales-order-form-data">{!! json_encode([
    'products' => $products->map(fn ($product) => [
        'id' => $product->id,
        'sku' => $product->sku,
        'name' => $product->name,
        'price' => (float) $product->sale_price,
        'unit' => $product->unit,
    ])->values(),
    'items' => old('items', [['product_id' => '', 'quantity' => 1]]),
    'labels' => [
        'selectProduct' => __('sales.create.select_product'),
        'removeLine' => __('sales.create.remove_line'),
    ],
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endsection

@push('scripts')
<script src="{{ asset('js/sales-order-form.js') }}"></script>
@endpush
