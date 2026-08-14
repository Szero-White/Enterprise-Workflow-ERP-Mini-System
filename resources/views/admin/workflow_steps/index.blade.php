@extends('layouts.app')

@section('page_title', __('ui.workflow_steps'))
@section('page_eyebrow', __('menu.admin').' / '.__('menu.workflow_templates'))

@section('content')
<x-erp.page-header
    :title="$workflowTemplate->name"
    :eyebrow="__('ui.workflow_steps')"
    :description="__('ui.workflow_steps_description')"
>
    <x-slot:actions>
        <a href="{{ route('admin.workflow-templates.steps.create', $workflowTemplate) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i>{{ __('ui.add_workflow_step') }}
        </a>
    </x-slot:actions>
</x-erp.page-header>

<div class="erp-table-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>{{ __('ui.order') }}</th><th>{{ __('ui.step') }}</th><th>{{ __('ui.approver_strategy') }}</th><th>{{ __('ui.approver') }}</th><th width="150">{{ __('ui.action') }}</th></tr></thead>
            <tbody>
            @forelse($steps as $step)
                <tr>
                    <td><span class="erp-order-chip">{{ $step->step_order }}</span></td>
                    <td><span class="erp-record-primary">{{ $step->step_name }}</span></td>
                    <td>{{ __('ui.approver_type_'.$step->approver_type) }}</td>
                    <td>{{ $step->approverLabel() }}</td>
                    <td>
                        <div class="erp-row-actions">
                            <a href="{{ route('admin.workflow-templates.steps.edit', [$workflowTemplate, $step]) }}" class="btn btn-sm btn-light border erp-action-btn" title="{{ __('ui.edit') }}"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.workflow-templates.steps.destroy', [$workflowTemplate, $step]) }}" method="POST" data-confirm="{{ __('ui.confirm_delete_step') }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger erp-action-btn" title="{{ __('ui.delete') }}"><i class="bi bi-trash"></i></button></form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-erp.empty-state icon="bi-diagram-3" :title="__('ui.no_steps')" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="erp-pagination">{{ $steps->links() }}</div>
</div>
@endsection
