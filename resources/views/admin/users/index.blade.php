@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h2>Users</h2><a href="{{ route('admin.users.create') }}" class="btn btn-primary">Create</a></div>
<div class="content-card p-3 table-responsive"><table class="table align-middle">
<thead><tr><th>Name</th><th>Email</th><th>Department</th><th>Role</th><th>Status</th><th width="160">Action</th></tr></thead><tbody>
@foreach($users as $user)
<tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->department?->name }}</td><td>{{ $user->role?->name }}</td><td>{{ $user->is_active ? 'Active' : 'Inactive' }}</td><td>
<a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning">Edit</a>
<form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE') <button class="btn btn-sm btn-danger">Delete</button></form>
</td></tr>
@endforeach
</tbody></table>{{ $users->links() }}</div>
@endsection
