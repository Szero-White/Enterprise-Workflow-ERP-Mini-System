@extends('layouts.app')

@section('page_title', 'Chi tiết quy trình')
@section('page_eyebrow', __('menu.admin').' / '.__('menu.workflow_templates'))

@section('content')
<x-erp.page-header
    :title="$workflowTemplate->name"
    :subtitle="__('ui.form').': '.($workflowTemplate->formTemplate?->name ?? '-')"
>
    <x-slot:actions>
        <a href="{{ route('admin.workflow-templates.steps.create', $workflowTemplate) }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>{{ __('ui.add_workflow_step') }}
        </a>
    </x-slot:actions>
</x-erp.page-header>

<div class="erp-table-card table-responsive">
    <table class="table align-middle mb-0">
        <thead>
        <tr>
            <th width="70">{{ __('ui.no') }}</th>
            <th width="90">{{ __('ui.order') }}</th>
            <th>{{ __('ui.step') }}</th>
            <th>{{ __('ui.approver_strategy') }}</th>
            <th>{{ __('ui.approver') }}</th>
            <th width="180">{{ __('ui.action') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($workflowTemplate->steps as $step)
            <tr>
                <td class="text-muted">{{ $loop->iteration }}</td>
                <td><span class="erp-order-chip">{{ $step->step_order }}</span></td>
                <td class="fw-medium">{{ $step->step_name }}</td>
                <td>{{ __('ui.approver_type_'.$step->approver_type) }}</td>
                <td>{{ $step->approverLabel() }}</td>
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
                <td colspan="6" class="text-center py-5 text-muted">{{ __('ui.no_steps') }}</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
