@extends('layouts.app')

@section('page_title', __('ui.form_fields_title'))
@section('page_eyebrow', __('menu.admin').' / '.__('menu.form_templates'))

@section('content')
<x-erp.page-header
    :title="$formTemplate->name"
    :eyebrow="__('menu.form_templates')"
    :description="__('ui.form_fields_description')"
>
    <x-slot:actions>
        <a href="{{ route('admin.form-templates.fields.create', $formTemplate) }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i>{{ __('ui.create_form_field') }}</a>
    </x-slot:actions>
</x-erp.page-header>

<div class="erp-table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>{{ __('ui.order') }}</th><th>{{ __('ui.label') }}</th><th>{{ __('ui.key') }}</th><th>{{ __('ui.type') }}</th><th>{{ __('ui.required') }}</th><th>{{ __('ui.options') }}</th><th width="160">{{ __('ui.action') }}</th></tr></thead>
            <tbody>
            @forelse($fields as $field)
                <tr>
                    <td><span class="erp-order-chip">{{ $field->sort_order }}</span></td>
                    <td><span class="erp-record-primary">{{ $field->label }}</span></td>
                    <td><code class="erp-record-code">{{ $field->field_key }}</code></td>
                    <td>{{ $field->field_type }}</td>
                    <td>@include('partials.boolean_badge', ['value' => $field->is_required, 'trueLabel' => __('status.required'), 'falseLabel' => __('status.optional')])</td>
                    <td>{{ is_array($field->options) ? implode(', ', $field->options) : '-' }}</td>
                    <td>
                        <div class="erp-row-actions">
                            <a href="{{ route('admin.form-templates.fields.edit', [$formTemplate, $field]) }}" class="btn btn-sm btn-light border erp-action-btn" title="{{ __('ui.edit') }}"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.form-templates.fields.destroy', [$formTemplate, $field]) }}" method="POST" data-confirm="{{ __('ui.confirm_delete_field') }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger erp-action-btn" title="{{ __('ui.delete') }}"><i class="bi bi-trash"></i></button></form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><x-erp.empty-state icon="bi-ui-checks-grid" :title="__('ui.no_fields')" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="erp-pagination">{{ $fields->links() }}</div>
</div>
@endsection
