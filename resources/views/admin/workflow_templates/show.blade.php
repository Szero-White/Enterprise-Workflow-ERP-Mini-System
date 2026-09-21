@extends('layouts.app')

@section('page_title', __('ui.workflow_template_detail'))
@section('page_eyebrow', __('menu.admin').' / '.__('menu.workflow_templates'))

@section('content')
<x-erp.page-header
    :title="$workflowTemplate->name"
    :subtitle="__('ui.form').': '.($workflowTemplate->formTemplate?->displayName() ?? '-').' · '.__('ui.version').' v'.$workflowTemplate->version"
>
    <x-slot:actions>
        @can('activate', $workflowTemplate)
            <form action="{{ route('admin.workflow-templates.activate', $workflowTemplate) }}" method="POST">
                @csrf
                <button class="btn btn-primary"><i class="bi bi-play-circle"></i>{{ __('ui.activate') }}</button>
            </form>
        @endcan

        @can('cloneVersion', $workflowTemplate)
            <form action="{{ route('admin.workflow-templates.clone-version', $workflowTemplate) }}" method="POST">
                @csrf
                <button class="btn btn-primary"><i class="bi bi-files"></i>{{ __('ui.clone_version') }}</button>
            </form>
        @endcan

        @can('manageSteps', $workflowTemplate)
            <a href="{{ route('admin.workflow-templates.steps.create', $workflowTemplate) }}" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle"></i>{{ __('ui.add_workflow_step') }}
            </a>
        @endcan

        @can('update', $workflowTemplate)
            <a href="{{ route('admin.workflow-templates.edit', $workflowTemplate) }}" class="btn btn-light border">
                <i class="bi bi-pencil"></i>{{ __('ui.edit') }}
            </a>
        @endcan

        @can('deactivate', $workflowTemplate)
            <form action="{{ route('admin.workflow-templates.deactivate', $workflowTemplate) }}" method="POST">
                @csrf
                <button class="btn btn-light border"><i class="bi bi-pause-circle"></i>{{ __('ui.deactivate') }}</button>
            </form>
        @endcan

        @can('delete', $workflowTemplate)
            <form action="{{ route('admin.workflow-templates.destroy', $workflowTemplate) }}" method="POST" data-confirm="{{ __('ui.confirm_delete_workflow') }}">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger"><i class="bi bi-trash"></i>{{ __('ui.delete') }}</button>
            </form>
        @endcan
    </x-slot:actions>
</x-erp.page-header>

<div class="mb-3 d-flex align-items-center gap-2">
    <span class="text-muted small">{{ __('ui.status') }}:</span>
    <x-erp.lifecycle-badge :status="$workflowTemplate->lifecycle_status" />
</div>

@if($workflowTemplate->isLocked())
    <div class="alert alert-secondary d-flex gap-2 align-items-start" role="alert">
        <i class="bi bi-lock-fill mt-1"></i>
        <div>{{ __('ui.configuration_locked_hint') }}</div>
    </div>
@elseif($workflowTemplate->lifecycle_status === \App\Enums\LifecycleStatus::Current)
    <div class="alert alert-info d-flex gap-2 align-items-start" role="alert">
        <i class="bi bi-shield-check mt-1"></i>
        <div>{{ __('ui.workflow_configuration_current_hint') }}</div>
    </div>
@elseif(in_array($workflowTemplate->lifecycle_status, [\App\Enums\LifecycleStatus::Legacy, \App\Enums\LifecycleStatus::Inactive], true))
    <div class="alert alert-secondary d-flex gap-2 align-items-start" role="alert">
        <i class="bi bi-eye mt-1"></i>
        <div>{{ __('ui.configuration_read_only_hint') }}</div>
    </div>
@endif

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="erp-stat-card p-3 h-100">
            <div class="text-muted small">{{ __('ui.version') }}</div>
            <div class="fs-5 fw-semibold mt-1">v{{ $workflowTemplate->version }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="erp-stat-card p-3 h-100">
            <div class="text-muted small">{{ __('ui.steps') }}</div>
            <div class="fs-5 fw-semibold mt-1">{{ $workflowTemplate->steps->count() }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="erp-stat-card p-3 h-100">
            <div class="text-muted small">{{ __('ui.requests_used') }}</div>
            <div class="fs-5 fw-semibold mt-1">{{ $workflowTemplate->requests_count }}</div>
        </div>
    </div>
</div>

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
                    @can('manageSteps', $workflowTemplate)
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.workflow-templates.steps.edit', [$workflowTemplate, $step]) }}" class="btn btn-sm btn-outline-primary">{{ __('ui.edit') }}</a>
                            <form action="{{ route('admin.workflow-templates.steps.destroy', [$workflowTemplate, $step]) }}" method="POST" data-confirm="{{ __('ui.confirm_delete_step') }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('ui.delete') }}</button>
                            </form>
                        </div>
                    @else
                        <span class="text-muted small">
                            <i class="bi {{ $workflowTemplate->isLocked() ? 'bi-lock' : 'bi-eye' }} me-1"></i>
                            {{ $workflowTemplate->isLocked() ? __('ui.locked') : __('ui.read_only') }}
                        </span>
                    @endcan
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
