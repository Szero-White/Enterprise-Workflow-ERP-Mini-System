<div class="row g-3">
    <div class="col-md-6">
        <label for="role_name" class="form-label erp-required">Name</label>
        <input id="role_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name ?? '') }}" required>
        @include('partials.form_error', ['field' => 'name'])
    </div>
    <div class="col-md-6">
        <label for="role_key" class="form-label erp-required">Key</label>
        <input id="role_key" name="key" class="form-control @error('key') is-invalid @enderror" value="{{ old('key', $role->key ?? '') }}" required>
        <div class="erp-form-hint">Use a stable key such as <code>admin</code> or <code>manager</code>.</div>
        @include('partials.form_error', ['field' => 'key'])
    </div>
    <div class="col-12">
        <label for="role_description" class="form-label">Description</label>
        <textarea id="role_description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $role->description ?? '') }}</textarea>
        @include('partials.form_error', ['field' => 'description'])
    </div>
</div>

@include('partials.form_actions', ['cancelUrl' => route('admin.roles.index')])
