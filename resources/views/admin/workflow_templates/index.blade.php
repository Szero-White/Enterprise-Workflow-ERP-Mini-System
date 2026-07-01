@extends('layouts.app')

@section('page_title', __('menu.workflow_templates'))
@section('page_eyebrow', __('menu.admin').' / '.__('menu.workflow_templates'))

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">{{ __('menu.workflow_templates') }}</h2>
        <p class="text-muted mb-0">Gắn các bước duyệt vào từng biểu mẫu theo đúng quy trình kiểm soát.</p>
    </div>
    <a href="{{ route('admin.workflow-templates.create') }}" class="btn btn-primary rounded-3">
        <i class="bi bi-plus-circle me-2"></i>Tạo quy trình
    </a>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">{{ __('ui.no') }}</th>
            <th>{{ __('ui.name') }}</th>
            <th>{{ __('ui.form') }}</th>
            <th>Bước</th>
            <th>{{ __('ui.status') }}</th>
            <th width="220">{{ __('ui.action') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($workflows as $workflow)
            <tr>
                <td class="text-muted fw-semibold">{{ $workflows->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ $workflow->name }}</td>
                <td>{{ $workflow->formTemplate?->name ?? '-' }}</td>
                <td>{{ $workflow->steps_count }}</td>
                <td>@include('partials.boolean_badge', ['value' => $workflow->is_active])</td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.workflow-templates.show', $workflow) }}" class="btn btn-sm btn-outline-secondary">{{ __('ui.view') }}</a>
                        <a href="{{ route('admin.workflow-templates.edit', $workflow) }}" class="btn btn-sm btn-outline-primary">{{ __('ui.edit') }}</a>
                        <form action="{{ route('admin.workflow-templates.destroy', $workflow) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_delete_workflow') }}')">
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
                    <div class="text-muted">{{ __('ui.no_workflows') }}</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $workflows->links() }}
</div>
@endsection
