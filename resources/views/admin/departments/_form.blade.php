<div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $department->name ?? '') }}" required></div>
<div class="mb-3"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code', $department->code ?? '') }}" required></div>
<div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control">{{ old('description', $department->description ?? '') }}</textarea></div>
<button class="btn btn-primary">Save</button>
<a href="{{ route('admin.departments.index') }}" class="btn btn-light">Back</a>
