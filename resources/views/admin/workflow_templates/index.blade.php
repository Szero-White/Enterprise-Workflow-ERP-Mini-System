@extends('layouts.app')

@section('page_title', __('menu.workflow_templates'))
@section('page_eyebrow', __('menu.admin').' / '.__('menu.workflow_templates'))

@section('content')
<x-erp.page-header :title="__('menu.workflow_templates')" :eyebrow="__('menu.admin')" description="Cấu hình các tuyến phê duyệt và chiến lược người duyệt cho từng biểu mẫu.">
    <x-slot:actions>
        <a href="{{ route('admin.workflow-templates.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i>Tạo quy trình</a>
    </x-slot:actions>
</x-erp.page-header>

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
