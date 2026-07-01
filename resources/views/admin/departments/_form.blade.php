<div class="row g-3">
    <div class="col-md-6">
        <label for="department_name" class="form-label erp-required">{{ __('ui.name') }}</label>
        <input id="department_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $department->name ?? '') }}" required>
        @include('partials.form_error', ['field' => 'name'])
    </div>
    <div class="col-md-6">
        <label for="department_code" class="form-label erp-required">{{ __('ui.entity_code') }}</label>
        <input id="department_code" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $department->code ?? '') }}" required>
        <div class="erp-form-hint">Ví dụ: <code>HR</code>, <code>ENG</code>, <code>FIN</code>.</div>
        @include('partials.form_error', ['field' => 'code'])
    </div>
    <div class="col-12">
        <label for="department_description" class="form-label">{{ __('ui.description') }}</label>
        <textarea id="department_description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $department->description ?? '') }}</textarea>
        @include('partials.form_error', ['field' => 'description'])
    </div>
</div>

@include('partials.form_actions', ['cancelUrl' => route('admin.departments.index')])
