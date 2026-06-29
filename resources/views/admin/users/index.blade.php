@extends('layouts.app')

@section('page_title', 'Users')
@section('page_eyebrow', 'Admin / Users')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">Users</h2>
        <p class="text-muted mb-0">Manage employee accounts, departments, and role assignments.</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary rounded-3">
        <i class="bi bi-plus-circle me-2"></i>Create User
    </a>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">No.</th>
            <th>Name</th>
            <th>Email</th>
            <th>Department</th>
            <th>Role</th>
            <th>Status</th>
            <th width="180">Action</th>
        </tr>
        </thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td class="text-muted fw-semibold">{{ $users->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->department?->name ?? '-' }}</td>
                <td>{{ $user->role?->name ?? '-' }}</td>
                <td>@include('partials.boolean_badge', ['value' => $user->is_active, 'trueLabel' => 'Active', 'falseLabel' => 'Inactive'])</td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete this user?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="text-muted">No users found.</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $users->links() }}
</div>
@endsection
