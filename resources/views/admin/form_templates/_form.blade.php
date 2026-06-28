<div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $formTemplate->name ?? '') }}" required></div>
<div class="mb-3"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code', $formTemplate->code ?? '') }}" required></div>
<div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control">{{ old('description', $formTemplate->description ?? '') }}</textarea></div>
<div class="form-check mb-3"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $formTemplate->is_active ?? true))><label class="form-check-label" for="is_active">Active</label></div>
<button class="btn btn-primary">Save</button><a href="{{ route('admin.form-templates.index') }}" class="btn btn-light">Back</a>
