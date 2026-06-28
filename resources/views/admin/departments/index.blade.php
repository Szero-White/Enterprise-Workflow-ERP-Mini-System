@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Departments</h2>
    <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">Create</a>
</div>
<div class="content-card p-3 table-responsive">
<table class="table align-middle">
    <thead><tr><th>Name</th><th>Code</th><th>Description</th><th width="160">Action</th></tr></thead>
    <tbody>
    @foreach($departments as $department)
        <tr>
            <td>{{ $department->name }}</td><td>{{ $department->code }}</td><td>{{ $department->description }}</td>
            <td>
                <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('admin.departments.destroy', $department) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                    @csrf @method('DELETE') <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $departments->links() }}
</div>
@endsection
