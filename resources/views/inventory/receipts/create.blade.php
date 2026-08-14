@extends('layouts.app')
@section('page_title', __('inventory.receipt.title'))
@section('page_eyebrow', __('inventory.eyebrow'))
@section('content')
<x-erp.page-header :title="__('inventory.receipt.title')" :eyebrow="__('inventory.eyebrow')" :description="__('inventory.receipt.page_description')">
    <x-slot:actions>
        <a href="{{ route('inventory.stocks.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left"></i>{{ __('inventory.receipt.back') }}</a>
    </x-slot:actions>
</x-erp.page-header>

<form method="POST" action="{{ route('inventory.receipts.store') }}">
    @csrf
    <div class="erp-form-layout">
        <section class="erp-form-section">
            <div class="erp-form-section__header">
                <h2 class="erp-form-section__title">{{ __('inventory.receipt.quick_receipt') }}</h2>
                <p class="erp-form-section__subtitle">{{ __('inventory.receipt.description') }}</p>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label erp-required">{{ __('inventory.receipt.warehouse') }}</label>
                    <select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                        <option value="">{{ __('inventory.receipt.select_warehouse') }}</option>
                        @foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected((string)old('warehouse_id') === (string)$warehouse->id)>{{ $warehouse->code }} - {{ $warehouse->name }}</option>@endforeach
                    </select>
                    @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label erp-required">{{ __('inventory.receipt.item') }}</label>
                    <select name="item_id" class="form-select @error('item_id') is-invalid @enderror" required>
                        <option value="">{{ __('inventory.receipt.select_item') }}</option>
                        @foreach($items as $item)<option value="{{ $item->id }}" @selected((string)old('item_id') === (string)$item->id)>{{ $item->sku }} - {{ $item->name }}</option>@endforeach
                    </select>
                    @error('item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label erp-required">{{ __('inventory.receipt.quantity') }}</label>
                    <input type="number" step="0.001" min="0.001" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" required>
                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('inventory.receipt.unit_cost') }}</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" name="unit_cost" class="form-control @error('unit_cost') is-invalid @enderror" value="{{ old('unit_cost') }}">
                        <span class="input-group-text">₫</span>
                    </div>
                    <div class="erp-form-hint">{{ __('inventory.receipt.unit_cost_hint') }}</div>
                    @error('unit_cost')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-12"><label class="form-label">{{ __('inventory.receipt.note') }}</label><textarea name="note" rows="3" class="form-control">{{ old('note') }}</textarea></div>
            </div>
        </section>

        <aside class="erp-form-aside">
            <div class="erp-form-actions-card">
                <div class="erp-form-actions-card__title">{{ __('inventory.receipt.confirm') }}</div>
                <div class="erp-form-actions-card__hint">{{ __('inventory.receipt.description') }}</div>
                <button class="btn btn-primary w-100"><i class="bi bi-check2-circle"></i>{{ __('inventory.receipt.confirm') }}</button>
                <a href="{{ route('inventory.stocks.index') }}" class="btn btn-light border w-100 mt-2">{{ __('inventory.receipt.back') }}</a>
            </div>
        </aside>
    </div>
</form>
@endsection
