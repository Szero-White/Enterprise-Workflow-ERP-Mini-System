<div class="row g-3">
    <div class="col-md-6">
        <label for="field_label" class="form-label erp-required">{{ __('ui.label') }}</label>
        <input id="field_label" name="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label', $field->label ?? '') }}" required>
        @include('partials.form_error', ['field' => 'label'])
    </div>
    <div class="col-md-6">
        <label for="field_key" class="form-label erp-required">{{ __('ui.field_key_label') }}</label>
        <input id="field_key" name="field_key" class="form-control @error('field_key') is-invalid @enderror" value="{{ old('field_key', $field->field_key ?? '') }}" placeholder="from_date" required>
        <div class="erp-form-hint">{{ __('ui.field_key_hint') }}</div>
        @include('partials.form_error', ['field' => 'field_key'])
    </div>
    <div class="col-md-6">
        <label for="field_type" class="form-label erp-required">{{ __('ui.field_type') }}</label>
        <select name="field_type" id="field_type" data-form-field-type class="form-select @error('field_type') is-invalid @enderror" required>
            @foreach(\App\Models\FormField::TYPES as $type)
                <option value="{{ $type }}" @selected(old('field_type', $field->field_type ?? '') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        @include('partials.form_error', ['field' => 'field_type'])
    </div>
    <div class="col-md-6">
        <label for="sort_order" class="form-label erp-required">{{ __('ui.order') }}</label>
        <input id="sort_order" type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $field->sort_order ?? 0) }}" min="0" required>
        @include('partials.form_error', ['field' => 'sort_order'])
    </div>
    <div class="col-12" id="options_box" data-form-field-options>
        <label for="options_text" class="form-label">{{ __('ui.options') }}</label>
        <textarea id="options_text" name="options_text" class="form-control @error('options_text') is-invalid @enderror" rows="4">{{ old('options_text', isset($field) && is_array($field->options) ? implode("\n", $field->options) : '') }}</textarea>
        <div class="erp-form-hint">{{ __('ui.field_options_hint') }}</div>
        @include('partials.form_error', ['field' => 'options_text'])
    </div>
    <div class="col-12">
        <div class="form-check form-switch mt-2">
            <input type="hidden" name="is_required" value="0">
            <input class="form-check-input" type="checkbox" name="is_required" value="1" id="is_required" @checked(old('is_required', $field->is_required ?? false))>
            <label class="form-check-label" for="is_required">{{ __('ui.required_field') }}</label>
        </div>
    </div>
</div>

@include('partials.form_actions', ['cancelUrl' => route('admin.form-templates.show', $formTemplate)])
