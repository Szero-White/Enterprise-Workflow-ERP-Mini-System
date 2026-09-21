@extends('layouts.app')

@section('page_title', __('menu.workflow_templates'))
@section('page_eyebrow', __('menu.admin').' / '.__('menu.workflow_templates'))

@section('content')
<x-erp.page-header :title="__('menu.workflow_templates')" :eyebrow="__('menu.admin')" :description="__('ui.workflow_templates_description')">
    <x-slot:actions>
        <a href="{{ route('admin.workflow-templates.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i>{{ __('ui.create_workflow_template') }}</a>
    </x-slot:actions>
</x-erp.page-header>

<div class="row g-3 mb-3">
    @foreach(\App\Enums\LifecycleStatus::cases() as $status)
        <div class="col-6 col-xl-3">
            <a href="{{ route('admin.workflow-templates.index', ['status' => $status->value]) }}" class="text-decoration-none">
                <div class="erp-stat-card p-3 h-100 {{ $selectedStatus === $status->value ? 'border border-primary' : '' }}">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <span class="text-muted small">{{ $status->label() }}</span>
                        <span class="badge {{ $status->badgeClass() }}">{{ $statusCounts->get($status->value, 0) }}</span>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

@if($selectedStatus !== '')
    <div class="mb-3">
        <a href="{{ route('admin.workflow-templates.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-x-lg"></i>{{ __('ui.clear_status_filter') }}
        </a>
    </div>
@endif

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">{{ __('ui.no') }}</th>
            <th>{{ __('ui.name') }}</th>
            <th>{{ __('ui.form') }}</th>
            <th>{{ __('ui.version') }}</th>
            <th>{{ __('ui.steps') }}</th>
            <th>{{ __('ui.status') }}</th>
            <th>{{ __('ui.configuration_state') }}</th>
            <th width="280">{{ __('ui.action') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($workflows as $workflow)
            <tr>
                <td class="text-muted fw-semibold">{{ $workflows->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ $workflow->name }}</td>
                <td>{{ $workflow->formTemplate?->name ?? '-' }}</td>
                <td><span class="badge text-bg-light border">v{{ $workflow->version }}</span></td>
                <td>{{ __('ui.approval_steps_count', ['count' => $workflow->steps_count]) }}</td>
                <td><x-erp.lifecycle-badge :status="$workflow->lifecycle_status" /></td>
                <td>
                    @can('update', $workflow)
                        <span class="badge text-bg-light border">{{ __('ui.editable') }}</span>
                    @else
                        <span class="badge erp-workflow-lock-badge">
                            <i class="bi {{ $workflow->isLocked() ? 'bi-lock-fill' : 'bi-eye-fill' }} me-1"></i>
                            {{ $workflow->isLocked() ? __('ui.locked') : __('ui.read_only') }}
                        </span>
                    @endcan
                </td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.workflow-templates.show', $workflow) }}" class="btn btn-sm btn-light border">{{ __('ui.view') }}</a>
                        @can('update', $workflow)
                            <a href="{{ route('admin.workflow-templates.edit', $workflow) }}" class="btn btn-sm btn-outline-primary">{{ __('ui.edit') }}</a>
                        @endcan
                        @can('cloneVersion', $workflow)
                            <form action="{{ route('admin.workflow-templates.clone-version', $workflow) }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-outline-primary">{{ __('ui.clone_version') }}</button>
                            </form>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center py-5"><div class="text-muted">{{ __('ui.no_workflows') }}</div></td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $workflows->links() }}
</div>
@endsection
