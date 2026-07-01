@extends('layouts.app')

@section('page_title', 'Bước duyệt')
@section('page_eyebrow', __('menu.admin').' / '.__('menu.workflow_templates'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Bước duyệt: {{ $workflowTemplate->name }}</h2>
    <a href="{{ route('admin.workflow-templates.steps.create', $workflowTemplate) }}" class="btn btn-primary">Thêm bước</a>
</div>
<div class="content-card p-3 table-responsive"><table class="table align-middle">
<thead><tr><th>{{ __('ui.order') }}</th><th>{{ __('ui.step') }}</th><th>{{ __('ui.role') }}</th><th>{{ __('ui.department') }}</th><th>{{ __('ui.name') }}</th><th width="160">{{ __('ui.action') }}</th></tr></thead><tbody>
@forelse($steps as $step)
<tr><td>{{ $step->step_order }}</td><td>{{ $step->step_name }}</td><td>{{ $step->approverRole ? (trans()->has('ui.roles.'.$step->approverRole->key) ? __('ui.roles.'.$step->approverRole->key) : $step->approverRole->name) : '-' }}</td><td>{{ $step->approverDepartment?->name ?? '-' }}</td><td>{{ $step->approverUser?->name ?? '-' }}</td><td>
<a href="{{ route('admin.workflow-templates.steps.edit', [$workflowTemplate, $step]) }}" class="btn btn-sm btn-warning">{{ __('ui.edit') }}</a>
<form action="{{ route('admin.workflow-templates.steps.destroy', [$workflowTemplate, $step]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('ui.confirm_delete_step') }}')">@csrf @method('DELETE') <button class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button></form>
</td></tr>
@empty<tr><td colspan="6" class="text-center text-muted">{{ __('ui.no_steps') }}</td></tr>@endforelse
</tbody></table>{{ $steps->links() }}</div>
@endsection
