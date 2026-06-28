@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><h2>{{ $workflowTemplate->name }}</h2><div class="text-muted">Form: {{ $workflowTemplate->formTemplate?->name }}</div></div>
    <a href="{{ route('admin.workflow-templates.steps.create', $workflowTemplate) }}" class="btn btn-primary">Add Step</a>
</div>
<div class="content-card p-3 table-responsive"><table class="table align-middle">
<thead><tr><th>Order</th><th>Step</th><th>Role</th><th>Department</th><th>User</th><th width="160">Action</th></tr></thead><tbody>
@forelse($workflowTemplate->steps as $step)
<tr><td>{{ $step->step_order }}</td><td>{{ $step->step_name }}</td><td>{{ $step->approverRole?->name ?? '-' }}</td><td>{{ $step->approverDepartment?->name ?? '-' }}</td><td>{{ $step->approverUser?->name ?? '-' }}</td><td>
<a href="{{ route('admin.workflow-templates.steps.edit', [$workflowTemplate, $step]) }}" class="btn btn-sm btn-warning">Edit</a>
<form action="{{ route('admin.workflow-templates.steps.destroy', [$workflowTemplate, $step]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE') <button class="btn btn-sm btn-danger">Delete</button></form>
</td></tr>
@empty<tr><td colspan="6" class="text-center text-muted">Chưa có bước duyệt.</td></tr>@endforelse
</tbody></table></div>
@endsection
