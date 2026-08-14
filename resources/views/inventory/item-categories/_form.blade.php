<div class="erp-form-section">
    <h3 class="erp-form-section__title">{{ __('items.category.form_heading') }}</h3>
    <p class="erp-form-section__subtitle">{{ __('items.category.form_hint') }}</p>

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label erp-required">{{ __('items.category.code') }}</label>
            <input name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $category->code ?? '') }}" required>
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-8">
            <label class="form-label erp-required">{{ __('items.category.name') }}</label>
            <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name ?? '') }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label">{{ __('items.category.description') }}</label>
            <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description ?? '') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $category->is_active ?? true))>
                <label class="form-check-label fw-semibold" for="is_active">{{ __('items.category.is_active') }}</label>
            </div>
        </div>
    </div>
</div>
