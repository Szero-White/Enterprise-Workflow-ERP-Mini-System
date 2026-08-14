<div class="erp-form-section">
    <h3 class="erp-form-section__title">{{ __('items.item.form_heading') }}</h3>
    <p class="erp-form-section__subtitle">{{ __('items.item.form_hint') }}</p>

    <div class="row g-3">
        <div class="col-md-5">
            <label class="form-label erp-required">{{ __('items.item.sku') }}</label>
            <input name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $item->sku ?? '') }}" required>
            @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-7">
            <label class="form-label erp-required">{{ __('items.item.name') }}</label>
            <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name ?? '') }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('items.item.category') }}</label>
            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                <option value="">{{ __('items.item.uncategorized') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $item->category_id ?? '') === (string) $category->id)>
                        {{ $category->code }} - {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label erp-required">{{ __('items.item.unit') }}</label>
            <input name="unit" class="form-control @error('unit') is-invalid @enderror" value="{{ old('unit', $item->unit ?? __('items.item.default_unit')) }}" required>
            @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label erp-required">{{ __('items.item.cost_price') }}</label>
            <div class="input-group">
                <input type="number" step="0.01" min="0" name="cost_price" class="form-control @error('cost_price') is-invalid @enderror" value="{{ old('cost_price', $item->cost_price ?? 0) }}" required>
                <span class="input-group-text">₫</span>
            </div>
            <div class="erp-form-hint">{{ __('items.item.cost_price_hint') }}</div>
            @error('cost_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label erp-required">{{ __('items.item.reorder_level') }}</label>
            <input type="number" step="0.001" min="0" name="reorder_level" class="form-control @error('reorder_level') is-invalid @enderror" value="{{ old('reorder_level', $item->reorder_level ?? 0) }}" required>
            <div class="erp-form-hint">{{ __('items.item.reorder_hint') }}</div>
            @error('reorder_level')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-8">
            <label class="form-label">{{ __('items.item.description') }}</label>
            <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $item->description ?? '') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="is_asset_trackable" value="1" id="is_asset_trackable" @checked(old('is_asset_trackable', $item->is_asset_trackable ?? false))>
                <label class="form-check-label fw-semibold" for="is_asset_trackable">{{ __('items.item.is_asset_trackable') }}</label>
                <div class="erp-form-hint">{{ __('items.item.is_asset_trackable_hint') }}</div>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $item->is_active ?? true))>
                <label class="form-check-label fw-semibold" for="is_active">{{ __('items.item.is_active') }}</label>
            </div>
        </div>
    </div>
</div>
