<div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $role->name ?? '') }}" required></div>
<div class="mb-3"><label class="form-label">Key</label><input name="key" class="form-control" value="{{ old('key', $role->key ?? '') }}" required></div>
<div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control">{{ old('description', $role->description ?? '') }}</textarea></div>
<button class="btn btn-primary">Save</button><a href="{{ route('admin.roles.index') }}" class="btn btn-light">Back</a>
