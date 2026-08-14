<div class="erp-form-section__header">
    <h3 class="erp-form-section__title">{{ __('inventory.warehouse.form_heading') }}</h3>
    <p class="erp-form-section__subtitle">{{ __('inventory.warehouse.form_hint') }}</p>
</div>
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label erp-required">{{ __('inventory.warehouse.code') }}</label>
        <input name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $warehouse->code ?? '') }}" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8">
        <label class="form-label erp-required">{{ __('inventory.warehouse.name') }}</label>
        <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $warehouse->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12"><label class="form-label">{{ __('inventory.warehouse.address') }}</label><textarea name="address" rows="3" class="form-control">{{ old('address', $warehouse->address ?? '') }}</textarea></div>
    <div class="col-12">
        <div class="erp-soft-panel p-3">
            <div class="form-check form-switch mb-0">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $warehouse->is_active ?? true))>
                <label class="form-check-label fw-semibold" for="is_active">{{ __('inventory.warehouse.is_active') }}</label>
            </div>
        </div>
    </div>
</div>
