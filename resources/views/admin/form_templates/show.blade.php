@extends('layouts.app')

@section('page_title', __('ui.form_template_detail'))
@section('page_eyebrow', __('menu.admin').' / '.__('menu.form_templates'))

@section('content')
<x-erp.page-header
    :title="$formTemplate->name"
    :eyebrow="__('menu.form_templates')"
    :description="__('ui.entity_code').': '.$formTemplate->code.' · '.__('ui.version').' v'.$formTemplate->version"
>
    <x-slot:actions>
        @can('publish', $formTemplate)
            <form action="{{ route('admin.form-templates.activate', $formTemplate) }}" method="POST" data-confirm="{{ __('ui.confirm_publish_version') }}">
                @csrf
                <button class="btn btn-primary"><i class="bi bi-rocket-takeoff"></i>{{ __('ui.publish_version') }}</button>
            </form>
        @endcan

        @can('cloneVersion', $formTemplate)
            <form action="{{ route('admin.form-templates.clone-version', $formTemplate) }}" method="POST">
                @csrf
                <button class="btn btn-primary"><i class="bi bi-files"></i>{{ __('ui.clone_version') }}</button>
            </form>
        @endcan

        @can('manageFields', $formTemplate)
            <a href="{{ route('admin.form-templates.fields.create', $formTemplate) }}" class="btn btn-outline-primary">
                <i class="bi bi-plus-lg"></i>{{ __('ui.add_field') }}
            </a>
        @endcan

        @can('update', $formTemplate)
            <a href="{{ route('admin.form-templates.edit', $formTemplate) }}" class="btn btn-light border">
                <i class="bi bi-pencil"></i>{{ __('ui.edit') }}
            </a>
        @endcan

        @can('deactivate', $formTemplate)
            <form action="{{ route('admin.form-templates.deactivate', $formTemplate) }}" method="POST">
                @csrf
                <button class="btn btn-light border"><i class="bi bi-pause-circle"></i>{{ __('ui.deactivate') }}</button>
            </form>
        @endcan

        @can('delete', $formTemplate)
            <form action="{{ route('admin.form-templates.destroy', $formTemplate) }}" method="POST" data-confirm="{{ __('ui.confirm_delete_form_template') }}">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger"><i class="bi bi-trash"></i>{{ __('ui.delete') }}</button>
            </form>
        @endcan
    </x-slot:actions>
</x-erp.page-header>

<div class="mb-3 d-flex align-items-center gap-2">
    <span class="text-muted small">{{ __('ui.status') }}:</span>
    <x-erp.lifecycle-badge :status="$formTemplate->lifecycle_status" />
</div>

@if($formTemplate->isLocked())
    <div class="alert alert-secondary d-flex gap-2 align-items-start" role="alert">
        <i class="bi bi-lock-fill mt-1"></i>
        <div>{{ __('ui.configuration_locked_hint') }}</div>
    </div>
@elseif($formTemplate->lifecycle_status === \App\Enums\LifecycleStatus::Current)
    <div class="alert alert-info d-flex gap-2 align-items-start" role="alert">
        <i class="bi bi-shield-check mt-1"></i>
        <div>{{ __('ui.configuration_current_hint') }}</div>
    </div>
@elseif(in_array($formTemplate->lifecycle_status, [\App\Enums\LifecycleStatus::Legacy, \App\Enums\LifecycleStatus::Inactive], true))
    <div class="alert alert-secondary d-flex gap-2 align-items-start" role="alert">
        <i class="bi bi-eye mt-1"></i>
        <div>{{ __('ui.configuration_read_only_hint') }}</div>
    </div>
@else
    <div class="content-card p-3 mb-3">
        <div class="d-flex gap-3 align-items-start justify-content-between flex-wrap">
            <div>
                <div class="fw-semibold mb-1"><i class="bi bi-diagram-3 me-2"></i>{{ __('ui.inherited_workflow_title') }}</div>
                @if($publishWorkflow)
                    <div class="mb-1">{{ $publishWorkflow->name }} · {{ __('ui.approval_steps_count', ['count' => $publishWorkflow->steps->count()]) }}</div>
                    <div class="text-muted small">{{ __('ui.inherited_workflow_ready') }}</div>
                @else
                    <div class="text-muted small">{{ __('ui.inherited_workflow_missing') }}</div>
                @endif
            </div>

            @if($publishWorkflow)
                <a href="{{ route('admin.workflow-templates.show', $publishWorkflow) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-diagram-3"></i>{{ __('ui.review_workflow') }}
                </a>
            @else
                @can('createWorkflow', $formTemplate)
                    <a href="{{ route('admin.workflow-templates.create', ['form_template_id' => $formTemplate->id]) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-diagram-3"></i>{{ __('ui.setup_workflow') }}
                    </a>
                @endcan
            @endif
        </div>
    </div>
@endif

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="erp-stat-card p-3 h-100">
            <div class="text-muted small">{{ __('ui.version') }}</div>
            <div class="fs-5 fw-semibold mt-1">v{{ $formTemplate->version }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="erp-stat-card p-3 h-100">
            <div class="text-muted small">{{ __('ui.fields') }}</div>
            <div class="fs-5 fw-semibold mt-1">{{ $formTemplate->fields->count() }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="erp-stat-card p-3 h-100">
            <div class="text-muted small">{{ __('ui.requests_used') }}</div>
            <div class="fs-5 fw-semibold mt-1">{{ $formTemplate->requests_count }}</div>
        </div>
    </div>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">{{ __('ui.no') }}</th>
            <th>{{ __('ui.order') }}</th>
            <th>{{ __('ui.label') }}</th>
            <th>{{ __('ui.key') }}</th>
            <th>{{ __('ui.type') }}</th>
            <th>{{ __('ui.required') }}</th>
            <th>{{ __('ui.options') }}</th>
            <th width="180">{{ __('ui.action') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($formTemplate->fields as $field)
            <tr>
                <td class="text-muted fw-semibold">{{ $loop->iteration }}</td>
                <td>{{ $field->sort_order }}</td>
                <td class="fw-semibold">
                    {{ $field->label }}
                    @if($field->hasCondition())
                        <div class="small text-muted fw-normal mt-1">{{ $field->condition_field_key }} · {{ __('ui.condition_operators.'.$field->condition_operator) }}@if($field->conditionRequiresValue()) · {{ $field->condition_value }}@endif</div>
                    @endif
                </td>
                <td><code>{{ $field->field_key }}</code></td>
                <td>{{ $field->type()?->label() ?? $field->field_type }}</td>
                <td>@include('partials.boolean_badge', ['value' => $field->is_required, 'trueLabel' => __('status.required'), 'falseLabel' => __('status.optional')])</td>
                <td>{{ is_array($field->options) ? implode(', ', $field->options) : '-' }}</td>
                <td>
                    @can('manageFields', $formTemplate)
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.form-templates.fields.edit', [$formTemplate, $field]) }}" class="btn btn-sm btn-outline-primary">{{ __('ui.edit') }}</a>
                            <form action="{{ route('admin.form-templates.fields.destroy', [$formTemplate, $field]) }}" method="POST" data-confirm="{{ __('ui.confirm_delete_field') }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('ui.delete') }}</button>
                            </form>
                        </div>
                    @else
                        <span class="text-muted small">
                            <i class="bi {{ $formTemplate->isLocked() ? 'bi-lock' : 'bi-eye' }} me-1"></i>
                            {{ $formTemplate->isLocked() ? __('ui.locked') : __('ui.read_only') }}
                        </span>
                    @endcan
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center py-5"><div class="text-muted">{{ __('ui.no_fields') }}</div></td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
