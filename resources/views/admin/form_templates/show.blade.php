@extends('layouts.app')

@section('page_title', 'Form Template Detail')
@section('page_eyebrow', 'Admin / Form Templates')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">{{ $formTemplate->name }}</h2>
        <p class="text-muted mb-0">Code: <code>{{ $formTemplate->code }}</code></p>
    </div>
    <a href="{{ route('admin.form-templates.fields.create', $formTemplate) }}" class="btn btn-primary rounded-3">
        <i class="bi bi-plus-circle me-2"></i>Add Field
    </a>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">No.</th>
            <th>Order</th>
            <th>Label</th>
            <th>Key</th>
            <th>Type</th>
            <th>Required</th>
            <th>Options</th>
            <th width="180">Action</th>
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
                <td>@include('partials.boolean_badge', ['value' => $field->is_required, 'trueLabel' => 'Required', 'falseLabel' => 'Optional'])</td>
                <td>{{ is_array($field->options) ? implode(', ', $field->options) : '-' }}</td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.form-templates.fields.edit', [$formTemplate, $field]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('admin.form-templates.fields.destroy', [$formTemplate, $field]) }}" method="POST" onsubmit="return confirm('Delete this field?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="text-muted">No fields found.</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
