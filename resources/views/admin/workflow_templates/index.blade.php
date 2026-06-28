@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h2>Workflow Templates</h2><a href="{{ route('admin.workflow-templates.create') }}" class="btn btn-primary">Create</a></div>
<div class="content-card p-3 table-responsive"><table class="table align-middle">
<thead><tr><th>Name</th><th>Form</th><th>Steps</th><th>Active</th><th width="220">Action</th></tr></thead><tbody>
@foreach($workflows as $workflow)
<tr><td>{{ $workflow->name }}</td><td>{{ $workflow->formTemplate?->name }}</td><td>{{ $workflow->steps_count }}</td><td>{{ $workflow->is_active ? 'Yes' : 'No' }}</td><td>
<a href="{{ route('admin.workflow-templates.show', $workflow) }}" class="btn btn-sm btn-info">View</a>
<a href="{{ route('admin.workflow-templates.edit', $workflow) }}" class="btn btn-sm btn-warning">Edit</a>
<form action="{{ route('admin.workflow-templates.destroy', $workflow) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE') <button class="btn btn-sm btn-danger">Delete</button></form>
</td></tr>
@endforeach
</tbody></table>{{ $workflows->links() }}</div>
@endsection
