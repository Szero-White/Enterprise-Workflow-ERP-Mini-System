@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h2>Roles</h2><a href="{{ route('admin.roles.create') }}" class="btn btn-primary">Create</a></div>
<div class="content-card p-3 table-responsive"><table class="table align-middle">
<thead><tr><th>Name</th><th>Key</th><th>Description</th><th width="160">Action</th></tr></thead><tbody>
@foreach($roles as $role)
<tr><td>{{ $role->name }}</td><td>{{ $role->key }}</td><td>{{ $role->description }}</td><td>
<a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-warning">Edit</a>
<form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE') <button class="btn btn-sm btn-danger">Delete</button></form>
</td></tr>
@endforeach
</tbody></table>{{ $roles->links() }}</div>
@endsection
