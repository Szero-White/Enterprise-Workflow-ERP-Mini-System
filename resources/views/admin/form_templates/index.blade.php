@extends('layouts.app')

@section('page_title', __('menu.form_templates'))
@section('page_eyebrow', __('menu.admin').' / '.__('menu.form_templates'))

@section('content')
<x-erp.page-header :title="__('menu.form_templates')" :eyebrow="__('menu.admin')" :description="__('ui.form_templates_description')">
    <x-slot:actions>
        <a href="{{ route('admin.form-templates.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i>{{ __('ui.create_form_template') }}</a>
    </x-slot:actions>
</x-erp.page-header>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">{{ __('ui.no') }}</th>
            <th>{{ __('ui.name') }}</th>
            <th>{{ __('ui.entity_code') }}</th>
            <th>{{ __('ui.version') }}</th>
            <th>{{ __('ui.fields') }}</th>
            <th>{{ __('ui.status') }}</th>
            <th>{{ __('ui.configuration_state') }}</th>
            <th width="280">{{ __('ui.action') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($templates as $template)
            <tr>
                <td class="text-muted fw-semibold">{{ $templates->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ $template->name }}</td>
                <td><code>{{ $template->code }}</code></td>
                <td><span class="badge text-bg-light border">v{{ $template->version }}</span></td>
                <td>{{ $template->fields_count }}</td>
                <td>@include('partials.boolean_badge', ['value' => $template->is_active])</td>
                <td>
                    @if($template->isLocked())
                        <span class="badge text-bg-secondary"><i class="bi bi-lock-fill me-1"></i>{{ __('ui.locked') }}</span>
                    @else
                        <span class="badge text-bg-light border">{{ __('ui.editable') }}</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.form-templates.show', $template) }}" class="btn btn-sm btn-outline-secondary">{{ __('ui.view') }}</a>
                        @if(! $template->isLocked() && ! $template->is_active)
                            <a href="{{ route('admin.form-templates.edit', $template) }}" class="btn btn-sm btn-outline-primary">{{ __('ui.edit') }}</a>
                        @endif
                        <form action="{{ route('admin.form-templates.clone-version', $template) }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-outline-primary">{{ __('ui.clone_version') }}</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center py-5"><div class="text-muted">{{ __('ui.no_form_templates') }}</div></td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $templates->links() }}
</div>
@endsection
