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
        @if($formTemplate->is_active)
            <form action="{{ route('admin.form-templates.deactivate', $formTemplate) }}" method="POST">
                @csrf
                <button class="btn btn-outline-secondary"><i class="bi bi-pause-circle"></i>{{ __('ui.deactivate') }}</button>
            </form>
        @else
            <form action="{{ route('admin.form-templates.activate', $formTemplate) }}" method="POST">
                @csrf
                <button class="btn btn-primary"><i class="bi bi-play-circle"></i>{{ __('ui.activate') }}</button>
            </form>
        @endif

        <form action="{{ route('admin.form-templates.clone-version', $formTemplate) }}" method="POST">
            @csrf
            <button class="btn btn-outline-primary"><i class="bi bi-files"></i>{{ __('ui.clone_version') }}</button>
        </form>

        @if(! $formTemplate->isLocked() && ! $formTemplate->is_active)
            <a href="{{ route('admin.form-templates.edit', $formTemplate) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i>{{ __('ui.edit') }}</a>
            <a href="{{ route('admin.form-templates.fields.create', $formTemplate) }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i>{{ __('ui.add_field') }}</a>
        @endif

        @if(! $formTemplate->isLocked() && ! $formTemplate->is_active)
            <form action="{{ route('admin.form-templates.destroy', $formTemplate) }}" method="POST" data-confirm="{{ __('ui.confirm_delete_form_template') }}">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger"><i class="bi bi-trash"></i>{{ __('ui.delete') }}</button>
            </form>
        @endif
    </x-slot:actions>
</x-erp.page-header>

@if($formTemplate->isLocked())
    <div class="alert alert-secondary d-flex gap-2 align-items-start" role="alert">
        <i class="bi bi-lock-fill mt-1"></i>
        <div>{{ __('ui.configuration_locked_hint') }}</div>
    </div>
@elseif($formTemplate->is_active)
    <div class="alert alert-info d-flex gap-2 align-items-start" role="alert">
        <i class="bi bi-info-circle mt-1"></i>
        <div>{{ __('ui.configuration_active_hint') }}</div>
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
                <td class="fw-semibold">{{ $field->label }}</td>
                <td><code>{{ $field->field_key }}</code></td>
                <td>{{ $field->field_type }}</td>
                <td>@include('partials.boolean_badge', ['value' => $field->is_required, 'trueLabel' => __('status.required'), 'falseLabel' => __('status.optional')])</td>
                <td>{{ is_array($field->options) ? implode(', ', $field->options) : '-' }}</td>
                <td>
                    @if(! $formTemplate->isLocked() && ! $formTemplate->is_active)
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.form-templates.fields.edit', [$formTemplate, $field]) }}" class="btn btn-sm btn-outline-primary">{{ __('ui.edit') }}</a>
                            <form action="{{ route('admin.form-templates.fields.destroy', [$formTemplate, $field]) }}" method="POST" data-confirm="{{ __('ui.confirm_delete_field') }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('ui.delete') }}</button>
                            </form>
                        </div>
                    @else
                        <span class="text-muted small"><i class="bi bi-lock me-1"></i>{{ __('ui.locked') }}</span>
                    @endif
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
