<div class="row g-3">
    @isset($formTemplate)
        <div class="col-12">
            <div class="erp-inline-note">
                <i class="bi bi-diagram-3"></i>
                <span>{{ __('ui.version') }} <strong>v{{ $formTemplate->version }}</strong>. {{ $formTemplate->is_active ? __('ui.configuration_active_hint') : __('ui.form_template_draft_hint') }}</span>
            </div>
        </div>
    @endisset

    <div class="col-md-6">
        <label for="template_name" class="form-label erp-required">{{ __('ui.name') }}</label>
        <input id="template_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $formTemplate->name ?? '') }}" required>
        @include('partials.form_error', ['field' => 'name'])
    </div>
    <div class="col-md-6">
        <label for="template_code" class="form-label erp-required">{{ __('ui.entity_code') }}</label>
        <input id="template_code" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $formTemplate->code ?? '') }}" @isset($formTemplate) readonly @endisset required>
        <div class="erp-form-hint">{{ __('ui.form_template_code_hint') }}</div>
        @include('partials.form_error', ['field' => 'code'])
    </div>
    <div class="col-12">
        <label for="template_description" class="form-label">{{ __('ui.description') }}</label>
        <textarea id="template_description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $formTemplate->description ?? '') }}</textarea>
        @include('partials.form_error', ['field' => 'description'])
    </div>

    @unless(isset($formTemplate))
        <div class="col-12">
            <div class="erp-inline-note">
                <i class="bi bi-info-circle"></i>
                <span>{{ __('ui.form_template_draft_hint') }}</span>
            </div>
        </div>
    @endunless
</div>

@include('partials.form_actions', ['cancelUrl' => route('admin.form-templates.index')])
