@extends('layouts.app')

@section('page_title', 'Workflow Detail')
@section('page_eyebrow', 'Admin / Workflows')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">{{ $workflowTemplate->name }}</h2>
        <p class="text-muted mb-0">Form: {{ $workflowTemplate->formTemplate?->name ?? '-' }}</p>
    </div>
    <a href="{{ route('admin.workflow-templates.steps.create', $workflowTemplate) }}" class="btn btn-primary rounded-3">
        <i class="bi bi-plus-circle me-2"></i>Add Step
    </a>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">No.</th>
            <th>Order</th>
            <th>Step</th>
            <th>Role</th>
            <th>Department</th>
            <th>User</th>
            <th width="180">Action</th>
        </tr>
        </thead>
        <tbody>
        @forelse($workflowTemplate->steps as $step)
            <tr>
                <td class="text-muted fw-semibold">{{ $loop->iteration }}</td>
                <td>{{ $step->step_order }}</td>
                <td class="fw-semibold">{{ $step->step_name }}</td>
                <td>{{ $step->approverRole?->name ?? '-' }}</td>
                <td>{{ $step->approverDepartment?->name ?? '-' }}</td>
                <td>{{ $step->approverUser?->name ?? '-' }}</td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.workflow-templates.steps.edit', [$workflowTemplate, $step]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('admin.workflow-templates.steps.destroy', [$workflowTemplate, $step]) }}" method="POST" onsubmit="return confirm('Delete this step?')">
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
                    <div class="text-muted">No approval steps found.</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
