<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">{{ __('procurement.supplier.code') }}</label>
        <input
            name="code"
            class="form-control"
            value="{{ old('code', $supplier->code ?? '') }}"
            required
        >
        @include('partials.form_error', ['field' => 'code'])
    </div>

    <div class="col-md-8">
        <label class="form-label">{{ __('procurement.supplier.name') }}</label>
        <input
            name="name"
            class="form-control"
            value="{{ old('name', $supplier->name ?? '') }}"
            required
        >
        @include('partials.form_error', ['field' => 'name'])
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ __('procurement.supplier.tax_code') }}</label>
        <input name="tax_code" class="form-control" value="{{ old('tax_code', $supplier->tax_code ?? '') }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ __('procurement.supplier.contact_name') }}</label>
        <input name="contact_name" class="form-control" value="{{ old('contact_name', $supplier->contact_name ?? '') }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ __('procurement.supplier.phone') }}</label>
        <input name="phone" class="form-control" value="{{ old('phone', $supplier->phone ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">{{ __('procurement.supplier.email') }}</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email ?? '') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">{{ __('procurement.supplier.payment_terms') }}</label>
        <input
            name="payment_terms"
            class="form-control"
            value="{{ old('payment_terms', $supplier->payment_terms ?? '') }}"
            placeholder="Net 30"
        >
    </div>

    <div class="col-md-3">
        <label class="form-label">{{ __('procurement.supplier.lead_time_days') }}</label>
        <input
            type="number"
            min="0"
            name="lead_time_days"
            class="form-control"
            value="{{ old('lead_time_days', $supplier->lead_time_days ?? '') }}"
        >
    </div>

    <div class="col-12">
        <label class="form-label">{{ __('procurement.supplier.address') }}</label>
        <textarea name="address" class="form-control" rows="2">{{ old('address', $supplier->address ?? '') }}</textarea>
    </div>

    <div class="col-12">
        <label class="form-label">{{ __('procurement.supplier.notes') }}</label>
        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $supplier->notes ?? '') }}</textarea>
    </div>

    <div class="col-12">
        <input type="hidden" name="is_active" value="0">
        <div class="form-check">
            <input
                class="form-check-input"
                type="checkbox"
                name="is_active"
                value="1"
                id="is_active"
                @checked(old('is_active', $supplier->is_active ?? true))
            >
            <label class="form-check-label" for="is_active">
                {{ __('procurement.supplier.is_active') }}
            </label>
        </div>
    </div>
</div>
