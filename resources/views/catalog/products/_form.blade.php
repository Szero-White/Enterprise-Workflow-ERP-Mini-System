<div class="erp-form-section__header">
    <h3 class="erp-form-section__title">{{ __('catalog.product.form_heading') }}</h3>
    <p class="erp-form-section__subtitle">{{ __('catalog.product.form_hint') }}</p>
</div>
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label erp-required">{{ __('catalog.product.sku') }}</label>
        <input name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku ?? '') }}" required>
        @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8">
        <label class="form-label erp-required">{{ __('catalog.product.name') }}</label>
        <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('catalog.product.category') }}</label>
        <select name="category_id" class="form-select">
            <option value="">{{ __('catalog.product.uncategorized') }}</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string)old('category_id', $product->category_id ?? '') === (string)$category->id)>{{ $category->code }} - {{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 col-lg-2">
        <label class="form-label erp-required">{{ __('catalog.product.unit') }}</label>
        <input name="unit" class="form-control" value="{{ old('unit', $product->unit ?? __('catalog.product.default_unit')) }}" required>
    </div>
    <div class="col-md-6 col-lg-2">
        <label class="form-label erp-required">{{ __('catalog.product.cost_price') }}</label>
        <div class="input-group">
            <input type="number" step="0.01" min="0" name="cost_price" class="form-control @error('cost_price') is-invalid @enderror" value="{{ old('cost_price', $product->cost_price ?? 0) }}" required>
            <span class="input-group-text">₫</span>
        </div>
        @error('cost_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 col-lg-2">
        <label class="form-label erp-required">{{ __('catalog.product.sale_price') }}</label>
        <div class="input-group">
            <input type="number" step="0.01" min="0" name="sale_price" class="form-control @error('sale_price') is-invalid @enderror" value="{{ old('sale_price', $product->sale_price ?? 0) }}" required>
            <span class="input-group-text">₫</span>
        </div>
        @error('sale_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-5">
        <label class="form-label erp-required">{{ __('catalog.product.reorder_level') }}</label>
        <input type="number" step="0.001" min="0" name="reorder_level" class="form-control @error('reorder_level') is-invalid @enderror" value="{{ old('reorder_level', $product->reorder_level ?? 0) }}" required>
        <div class="erp-form-hint">{{ __('catalog.product.reorder_hint') }}</div>
        @error('reorder_level')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label">{{ __('catalog.product.description') }}</label>
        <textarea name="description" rows="4" class="form-control">{{ old('description', $product->description ?? '') }}</textarea>
    </div>
    <div class="col-12">
        <div class="erp-soft-panel p-3">
            <div class="form-check form-switch mb-0">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $product->is_active ?? true))>
                <label class="form-check-label fw-semibold" for="is_active">{{ __('catalog.product.is_active') }}</label>
            </div>
        </div>
    </div>
</div>
