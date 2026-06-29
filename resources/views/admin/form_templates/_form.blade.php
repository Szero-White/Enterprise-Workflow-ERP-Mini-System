<div class="row g-3">
    <div class="col-md-6">
        <label for="template_name" class="form-label erp-required">Name</label>
        <input id="template_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $formTemplate->name ?? '') }}" required>
        @include('partials.form_error', ['field' => 'name'])
    </div>
    <div class="col-md-6">
        <label for="template_code" class="form-label erp-required">Code</label>
        <input id="template_code" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $formTemplate->code ?? '') }}" required>
        <div class="erp-form-hint">Use uppercase short code such as <code>LEAVE</code>.</div>
        @include('partials.form_error', ['field' => 'code'])
    </div>
    <div class="col-12">
        <label for="template_description" class="form-label">Description</label>
        <textarea id="template_description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $formTemplate->description ?? '') }}</textarea>
        @include('partials.form_error', ['field' => 'description'])
    </div>
    <div class="col-12">
        <label class="form-label d-block">Status</label>
        <div class="form-check form-switch mt-2">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $formTemplate->is_active ?? true))>
            <label class="form-check-label" for="is_active">Template is active</label>
        </div>
    </div>
</div>

@include('partials.form_actions', ['cancelUrl' => route('admin.form-templates.index')])
