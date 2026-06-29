@extends('layouts.app')

@section('page_title', 'Workflow Templates')
@section('page_eyebrow', 'Admin / Workflows')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">Workflow Templates</h2>
        <p class="text-muted mb-0">Map approval steps to each form template in a controlled flow.</p>
    </div>
    <a href="{{ route('admin.workflow-templates.create') }}" class="btn btn-primary rounded-3">
        <i class="bi bi-plus-circle me-2"></i>Create Workflow
    </a>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">No.</th>
            <th>Name</th>
            <th>Form</th>
            <th>Steps</th>
            <th>Status</th>
            <th width="220">Action</th>
        </tr>
        </thead>
        <tbody>
        @forelse($workflows as $workflow)
            <tr>
                <td class="text-muted fw-semibold">{{ $workflows->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ $workflow->name }}</td>
                <td>{{ $workflow->formTemplate?->name ?? '-' }}</td>
                <td>{{ $workflow->steps_count }}</td>
                <td>@include('partials.boolean_badge', ['value' => $workflow->is_active, 'trueLabel' => 'Active', 'falseLabel' => 'Inactive'])</td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.workflow-templates.show', $workflow) }}" class="btn btn-sm btn-outline-secondary">View</a>
                        <a href="{{ route('admin.workflow-templates.edit', $workflow) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('admin.workflow-templates.destroy', $workflow) }}" method="POST" onsubmit="return confirm('Delete this workflow?')">
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
                    <div class="text-muted">No workflow templates found.</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $workflows->links() }}
</div>
@endsection
