@extends('layouts.app')

@section('page_title', 'Roles')
@section('page_eyebrow', 'Admin / Roles')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">Roles</h2>
        <p class="text-muted mb-0">Maintain role definitions used across access control and workflows.</p>
    </div>
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary rounded-3">
        <i class="bi bi-plus-circle me-2"></i>Create Role
    </a>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">No.</th>
            <th>Name</th>
            <th>Key</th>
            <th>Description</th>
            <th width="180">Action</th>
        </tr>
        </thead>
        <tbody>
        @forelse($roles as $role)
            <tr>
                <td class="text-muted fw-semibold">{{ $roles->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ $role->name }}</td>
                <td><code>{{ $role->key }}</code></td>
                <td>{{ $role->description ?: '-' }}</td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Delete this role?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center py-5">
                    <div class="text-muted">No roles found.</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $roles->links() }}
</div>
@endsection
