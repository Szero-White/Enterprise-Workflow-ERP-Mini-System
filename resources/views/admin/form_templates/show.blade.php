@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><h2>{{ $formTemplate->name }}</h2><div class="text-muted">Code: {{ $formTemplate->code }}</div></div>
    <a href="{{ route('admin.form-templates.fields.create', $formTemplate) }}" class="btn btn-primary">Add Field</a>
</div>
<div class="content-card p-3 table-responsive">
<table class="table align-middle">
<thead><tr><th>Order</th><th>Label</th><th>Key</th><th>Type</th><th>Required</th><th>Options</th><th width="160">Action</th></tr></thead>
<tbody>
@forelse($formTemplate->fields as $field)
<tr>
<td>{{ $field->sort_order }}</td><td>{{ $field->label }}</td><td>{{ $field->field_key }}</td><td>{{ $field->field_type }}</td><td>{{ $field->is_required ? 'Yes' : 'No' }}</td><td>{{ is_array($field->options) ? implode(', ', $field->options) : '-' }}</td>
<td><a href="{{ route('admin.form-templates.fields.edit', [$formTemplate, $field]) }}" class="btn btn-sm btn-warning">Edit</a>
<form action="{{ route('admin.form-templates.fields.destroy', [$formTemplate, $field]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE') <button class="btn btn-sm btn-danger">Delete</button></form></td>
</tr>
@empty<tr><td colspan="7" class="text-center text-muted">Chưa có field.</td></tr>@endforelse
</tbody></table>
</div>
@endsection
