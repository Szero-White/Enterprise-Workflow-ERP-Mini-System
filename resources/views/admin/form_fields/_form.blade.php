@php
    $conditionEnabled = old('condition_enabled', isset($field) && $field->hasCondition());
    $conditionFieldKey = old('condition_field_key', $field->condition_field_key ?? '');
    $conditionOperator = old('condition_operator', $field->condition_operator ?? \App\Models\FormField::CONDITION_EQUALS);
    $conditionValue = old('condition_value', $field->condition_value ?? '');
@endphp

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
            @foreach(\App\Enums\FormFieldType::cases() as $type)
                <option value="{{ $type->value }}" @selected(old('field_type', $field->field_type ?? '') === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        @include('partials.form_error', ['field' => 'field_type'])
    </div>
    <div class="col-md-6">
        <label for="sort_order" class="form-label erp-required">{{ __('ui.order') }}</label>
        <input id="sort_order" type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $field->sort_order ?? 0) }}" min="0" required>
        <div class="erp-form-hint">{{ __('ui.field_condition_order_hint') }}</div>
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
            <div class="erp-form-hint">{{ __('ui.required_field_condition_hint') }}</div>
        </div>
    </div>

    <div class="col-12 pt-2 border-top">
        <div class="form-check form-switch">
            <input type="hidden" name="condition_enabled" value="0">
            <input
                class="form-check-input"
                type="checkbox"
                name="condition_enabled"
                value="1"
                id="condition_enabled"
                data-condition-enabled
                @checked($conditionEnabled)
            >
            <label class="form-check-label fw-semibold" for="condition_enabled">{{ __('ui.conditional_display') }}</label>
            <div class="erp-form-hint">{{ __('ui.conditional_display_hint') }}</div>
        </div>
    </div>

    <div class="col-12" data-condition-config @if(! $conditionEnabled) hidden @endif>
        <div class="rounded-3 border p-3 bg-light-subtle">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="condition_field_key" class="form-label">{{ __('ui.condition_source_field') }}</label>
                    <select id="condition_field_key" name="condition_field_key" class="form-select @error('condition_field_key') is-invalid @enderror" data-condition-source>
                        <option value="">{{ __('ui.select_placeholder') }}</option>
                        @foreach($conditionFields ?? collect() as $candidate)
                            <option value="{{ $candidate->field_key }}" @selected($conditionFieldKey === $candidate->field_key)>
                                {{ $candidate->label }} ({{ $candidate->field_key }})
                            </option>
                        @endforeach
                    </select>
                    @include('partials.form_error', ['field' => 'condition_field_key'])
                </div>
                <div class="col-md-4">
                    <label for="condition_operator" class="form-label">{{ __('ui.condition_operator') }}</label>
                    <select id="condition_operator" name="condition_operator" class="form-select @error('condition_operator') is-invalid @enderror" data-condition-operator>
                        <option value="equals" @selected($conditionOperator === 'equals')>{{ __('ui.condition_operators.equals') }}</option>
                        <option value="not_equals" @selected($conditionOperator === 'not_equals')>{{ __('ui.condition_operators.not_equals') }}</option>
                        <option value="filled" @selected($conditionOperator === 'filled')>{{ __('ui.condition_operators.filled') }}</option>
                        <option value="empty" @selected($conditionOperator === 'empty')>{{ __('ui.condition_operators.empty') }}</option>
                    </select>
                    @include('partials.form_error', ['field' => 'condition_operator'])
                </div>
                <div class="col-md-4" data-condition-value-box>
                    <label for="condition_value" class="form-label">{{ __('ui.condition_value') }}</label>
                    <input id="condition_value" name="condition_value" class="form-control @error('condition_value') is-invalid @enderror" value="{{ $conditionValue }}" placeholder="{{ __('ui.condition_value_placeholder') }}">
                    @include('partials.form_error', ['field' => 'condition_value'])
                </div>
                <div class="col-12">
                    <div class="erp-form-hint">{{ __('ui.field_condition_dependency_hint') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.form_actions', ['cancelUrl' => route('admin.form-templates.show', $formTemplate)])
