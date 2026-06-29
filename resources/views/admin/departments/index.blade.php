@extends('layouts.app')

@section('page_title', 'Departments')
@section('page_eyebrow', 'Admin / Departments')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">Departments</h2>
        <p class="text-muted mb-0">Keep department structure clean for organization and approval routing.</p>
    </div>
    <a href="{{ route('admin.departments.create') }}" class="btn btn-primary rounded-3">
        <i class="bi bi-plus-circle me-2"></i>Create Department
    </a>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">No.</th>
            <th>Name</th>
            <th>Code</th>
            <th>Description</th>
            <th width="180">Action</th>
        </tr>
        </thead>
        <tbody>
        @forelse($departments as $department)
            <tr>
                <td class="text-muted fw-semibold">{{ $departments->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ $department->name }}</td>
                <td><code>{{ $department->code }}</code></td>
                <td>{{ $department->description ?: '-' }}</td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('admin.departments.destroy', $department) }}" method="POST" onsubmit="return confirm('Delete this department?')">
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
                    <div class="text-muted">No departments found.</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $departments->links() }}
</div>
@endsection
