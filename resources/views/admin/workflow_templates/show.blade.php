@extends('layouts.app')

@section('page_title', 'Chi tiết quy trình')
@section('page_eyebrow', __('menu.admin').' / '.__('menu.workflow_templates'))

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">{{ $workflowTemplate->name }}</h2>
        <p class="text-muted mb-0">{{ __('ui.form') }}: {{ $workflowTemplate->formTemplate?->name ?? '-' }}</p>
    </div>
    <a href="{{ route('admin.workflow-templates.steps.create', $workflowTemplate) }}" class="btn btn-primary rounded-3">
        <i class="bi bi-plus-circle me-2"></i>Thêm bước
    </a>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">{{ __('ui.no') }}</th>
            <th>{{ __('ui.order') }}</th>
            <th>{{ __('ui.step') }}</th>
            <th>{{ __('ui.role') }}</th>
            <th>{{ __('ui.department') }}</th>
            <th>{{ __('ui.name') }}</th>
            <th width="180">{{ __('ui.action') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($workflowTemplate->steps as $step)
            <tr>
                <td class="text-muted fw-semibold">{{ $loop->iteration }}</td>
                <td>{{ $step->step_order }}</td>
                <td class="fw-semibold">{{ $step->step_name }}</td>
                <td>{{ $step->approverRole ? (trans()->has('ui.roles.'.$step->approverRole->key) ? __('ui.roles.'.$step->approverRole->key) : $step->approverRole->name) : '-' }}</td>
                <td>{{ $step->approverDepartment?->name ?? '-' }}</td>
                <td>{{ $step->approverUser?->name ?? '-' }}</td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.workflow-templates.steps.edit', [$workflowTemplate, $step]) }}" class="btn btn-sm btn-outline-primary">{{ __('ui.edit') }}</a>
                        <form action="{{ route('admin.workflow-templates.steps.destroy', [$workflowTemplate, $step]) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_delete_step') }}')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">{{ __('ui.delete') }}</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="text-muted">{{ __('ui.no_steps') }}</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
