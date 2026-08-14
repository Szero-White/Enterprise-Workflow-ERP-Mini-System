<div class="erp-form-section__header">
    <h3 class="erp-form-section__title">{{ __('crm.form_heading') }}</h3>
    <p class="erp-form-section__subtitle">{{ __('crm.form_hint') }}</p>
</div>
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label erp-required">{{ __('crm.code') }}</label>
        <input name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $customer->code ?? '') }}" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8">
        <label class="form-label erp-required">{{ __('crm.name') }}</label>
        <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $customer->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6"><label class="form-label">{{ __('crm.company_name') }}</label><input name="company_name" class="form-control" value="{{ old('company_name', $customer->company_name ?? '') }}"></div>
    <div class="col-md-6"><label class="form-label">{{ __('crm.tax_code') }}</label><input name="tax_code" class="form-control" value="{{ old('tax_code', $customer->tax_code ?? '') }}"></div>
    <div class="col-md-6">
        <label class="form-label">{{ __('crm.email') }}</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $customer->email ?? '') }}">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6"><label class="form-label">{{ __('crm.phone') }}</label><input name="phone" class="form-control" value="{{ old('phone', $customer->phone ?? '') }}"></div>
    <div class="col-12"><label class="form-label">{{ __('crm.address') }}</label><textarea name="address" rows="2" class="form-control">{{ old('address', $customer->address ?? '') }}</textarea></div>
    <div class="col-12"><label class="form-label">{{ __('crm.notes') }}</label><textarea name="notes" rows="3" class="form-control">{{ old('notes', $customer->notes ?? '') }}</textarea></div>
    <div class="col-12">
        <div class="erp-soft-panel p-3">
            <div class="form-check form-switch mb-0">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $customer->is_active ?? true))>
                <label class="form-check-label fw-semibold" for="is_active">{{ __('crm.is_active') }}</label>
            </div>
        </div>
    </div>
</div>
