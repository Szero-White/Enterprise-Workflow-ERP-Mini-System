@extends('layouts.app')

@section('page_title', 'Form Templates')
@section('page_eyebrow', 'Admin / Form Templates')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">Form Templates</h2>
        <p class="text-muted mb-0">Prepare dynamic forms used by employees when submitting requests.</p>
    </div>
    <a href="{{ route('admin.form-templates.create') }}" class="btn btn-primary rounded-3">
        <i class="bi bi-plus-circle me-2"></i>Create Template
    </a>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">No.</th>
            <th>Name</th>
            <th>Code</th>
            <th>Fields</th>
            <th>Status</th>
            <th width="220">Action</th>
        </tr>
        </thead>
        <tbody>
        @forelse($templates as $template)
            <tr>
                <td class="text-muted fw-semibold">{{ $templates->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ $template->name }}</td>
                <td><code>{{ $template->code }}</code></td>
                <td>{{ $template->fields_count }}</td>
                <td>@include('partials.boolean_badge', ['value' => $template->is_active, 'trueLabel' => 'Active', 'falseLabel' => 'Inactive'])</td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.form-templates.show', $template) }}" class="btn btn-sm btn-outline-secondary">View</a>
                        <a href="{{ route('admin.form-templates.edit', $template) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('admin.form-templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Delete this template?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="text-muted">No form templates found.</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $templates->links() }}
</div>
@endsection
