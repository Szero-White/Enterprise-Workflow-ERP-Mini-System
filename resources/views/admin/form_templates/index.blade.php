@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h2>Form Templates</h2><a href="{{ route('admin.form-templates.create') }}" class="btn btn-primary">Create</a></div>
<div class="content-card p-3 table-responsive"><table class="table align-middle">
<thead><tr><th>Name</th><th>Code</th><th>Fields</th><th>Active</th><th width="220">Action</th></tr></thead><tbody>
@foreach($templates as $template)
<tr><td>{{ $template->name }}</td><td>{{ $template->code }}</td><td>{{ $template->fields_count }}</td><td>{{ $template->is_active ? 'Yes' : 'No' }}</td><td>
<a href="{{ route('admin.form-templates.show', $template) }}" class="btn btn-sm btn-info">View</a>
<a href="{{ route('admin.form-templates.edit', $template) }}" class="btn btn-sm btn-warning">Edit</a>
<form action="{{ route('admin.form-templates.destroy', $template) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE') <button class="btn btn-sm btn-danger">Delete</button></form>
</td></tr>
@endforeach
</tbody></table>{{ $templates->links() }}</div>
@endsection
