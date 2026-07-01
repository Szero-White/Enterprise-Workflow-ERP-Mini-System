@extends('layouts.app')

@section('page_title', __('menu.form_templates'))
@section('page_eyebrow', __('menu.admin').' / '.__('menu.form_templates'))

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">{{ __('menu.form_templates') }}</h2>
        <p class="text-muted mb-0">Chuẩn bị các biểu mẫu động để nhân viên gửi đơn theo đúng nghiệp vụ.</p>
    </div>
    <a href="{{ route('admin.form-templates.create') }}" class="btn btn-primary rounded-3">
        <i class="bi bi-plus-circle me-2"></i>Tạo biểu mẫu
    </a>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">{{ __('ui.no') }}</th>
            <th>{{ __('ui.name') }}</th>
            <th>{{ __('ui.entity_code') }}</th>
            <th>Trường</th>
            <th>{{ __('ui.status') }}</th>
            <th width="220">{{ __('ui.action') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($templates as $template)
            <tr>
                <td class="text-muted fw-semibold">{{ $templates->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ $template->name }}</td>
                <td><code>{{ $template->code }}</code></td>
                <td>{{ $template->fields_count }}</td>
                <td>@include('partials.boolean_badge', ['value' => $template->is_active])</td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.form-templates.show', $template) }}" class="btn btn-sm btn-outline-secondary">{{ __('ui.view') }}</a>
                        <a href="{{ route('admin.form-templates.edit', $template) }}" class="btn btn-sm btn-outline-primary">{{ __('ui.edit') }}</a>
                        <form action="{{ route('admin.form-templates.destroy', $template) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_delete_form_template') }}')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">{{ __('ui.delete') }}</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="text-muted">{{ __('ui.no_form_templates') }}</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $templates->links() }}
</div>
@endsection
