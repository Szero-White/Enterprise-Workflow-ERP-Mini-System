@extends('layouts.app')

@section('page_title', __('menu.workflow_templates'))
@section('page_eyebrow', __('menu.admin').' / '.__('menu.workflow_templates'))

@section('content')
<x-erp.page-header :title="__('menu.workflow_templates')" :eyebrow="__('menu.admin')" :description="__('ui.workflow_templates_description')">
    <x-slot:actions>
        <a href="{{ route('admin.workflow-templates.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i>{{ __('ui.create_workflow_template') }}</a>
    </x-slot:actions>
</x-erp.page-header>

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
                <td>{{ $workflow->formTemplate?->displayName() ?? '-' }}</td>
                <td><span class="badge text-bg-light border">v{{ $workflow->version }}</span></td>
                <td>{{ $workflow->steps_count }}</td>
                <td>@include('partials.boolean_badge', ['value' => $workflow->is_active])</td>
                <td>
                    @if($workflow->isLocked())
                        <span class="badge text-bg-secondary"><i class="bi bi-lock-fill me-1"></i>{{ __('ui.locked') }}</span>
                    @else
                        <span class="badge text-bg-light border">{{ __('ui.editable') }}</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.workflow-templates.show', $workflow) }}" class="btn btn-sm btn-outline-secondary">{{ __('ui.view') }}</a>
                        @if(! $workflow->isLocked() && ! $workflow->is_active)
                            <a href="{{ route('admin.workflow-templates.edit', $workflow) }}" class="btn btn-sm btn-outline-primary">{{ __('ui.edit') }}</a>
                        @endif
                        <form action="{{ route('admin.workflow-templates.clone-version', $workflow) }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-outline-primary">{{ __('ui.clone_version') }}</button>
                        </form>
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
