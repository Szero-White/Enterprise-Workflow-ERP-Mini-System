@extends('layouts.app')
@section('page_title', __('inventory.receipt.title'))
@section('page_eyebrow', __('inventory.eyebrow'))
@section('content')
<div class="content-card erp-form-card p-3 p-lg-4">
    <div class="mb-4"><h2 class="erp-section-title">{{ __('inventory.receipt.quick_receipt') }}</h2><p class="erp-section-subtitle">{{ __('inventory.receipt.description') }}</p></div>
    <form method="POST" action="{{ route('inventory.receipts.store') }}">@csrf
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label erp-required">{{ __('inventory.receipt.warehouse') }}</label><select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required><option value="">{{ __('inventory.receipt.select_warehouse') }}</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected((string)old('warehouse_id') === (string)$warehouse->id)>{{ $warehouse->code }} - {{ $warehouse->name }}</option>@endforeach</select>@error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-6"><label class="form-label erp-required">{{ __('inventory.receipt.product') }}</label><select name="product_id" class="form-select @error('product_id') is-invalid @enderror" required><option value="">{{ __('inventory.receipt.select_product') }}</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected((string)old('product_id') === (string)$product->id)>{{ $product->sku }} - {{ $product->name }}</option>@endforeach</select>@error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-6"><label class="form-label erp-required">{{ __('inventory.receipt.quantity') }}</label><input type="number" step="0.001" min="0.001" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" required>@error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-6"><label class="form-label">{{ __('inventory.receipt.unit_cost') }}</label><input type="number" step="0.01" min="0" name="unit_cost" class="form-control @error('unit_cost') is-invalid @enderror" value="{{ old('unit_cost') }}"><div class="erp-form-hint">{{ __('inventory.receipt.unit_cost_hint') }}</div>@error('unit_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-12"><label class="form-label">{{ __('inventory.receipt.note') }}</label><textarea name="note" rows="3" class="form-control">{{ old('note') }}</textarea></div>
        </div>
        <div class="d-flex gap-2 mt-4"><button class="btn btn-primary"><i class="bi bi-check2-circle me-2"></i>{{ __('inventory.receipt.confirm') }}</button><a href="{{ route('inventory.stocks.index') }}" class="btn btn-light border">{{ __('inventory.receipt.back') }}</a></div>
    </form>
</div>
@endsection
