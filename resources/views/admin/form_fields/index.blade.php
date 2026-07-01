@extends('layouts.app')

@section('page_title', 'Trường biểu mẫu')
@section('page_eyebrow', __('menu.admin').' / '.__('menu.form_templates'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Trường biểu mẫu: {{ $formTemplate->name }}</h2>
    <a href="{{ route('admin.form-templates.fields.create', $formTemplate) }}" class="btn btn-primary">Thêm trường</a>
</div>
<div class="content-card p-3 table-responsive">
<table class="table align-middle">
<thead><tr><th>{{ __('ui.order') }}</th><th>{{ __('ui.label') }}</th><th>{{ __('ui.key') }}</th><th>{{ __('ui.type') }}</th><th>{{ __('ui.required') }}</th><th>{{ __('ui.options') }}</th><th width="160">{{ __('ui.action') }}</th></tr></thead>
<tbody>
@forelse($fields as $field)
<tr>
<td>{{ $field->sort_order }}</td><td>{{ $field->label }}</td><td>{{ $field->field_key }}</td><td>{{ $field->field_type }}</td><td>{{ $field->is_required ? __('status.required') : __('status.optional') }}</td><td>{{ is_array($field->options) ? implode(', ', $field->options) : '-' }}</td>
<td><a href="{{ route('admin.form-templates.fields.edit', [$formTemplate, $field]) }}" class="btn btn-sm btn-warning">{{ __('ui.edit') }}</a>
<form action="{{ route('admin.form-templates.fields.destroy', [$formTemplate, $field]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('ui.confirm_delete_field') }}')">@csrf @method('DELETE') <button class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button></form></td>
</tr>
@empty<tr><td colspan="7" class="text-center text-muted">{{ __('ui.no_fields') }}</td></tr>@endforelse
</tbody></table>
{{ $fields->links() }}
</div>
@endsection
