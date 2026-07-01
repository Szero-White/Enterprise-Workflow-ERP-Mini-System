@extends('layouts.app')

@section('page_title', __('menu.departments'))
@section('page_eyebrow', __('menu.admin').' / '.__('menu.departments'))

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">{{ __('menu.departments') }}</h2>
        <p class="text-muted mb-0">Quản lý cơ cấu phòng ban để tổ chức người dùng và tuyến duyệt rõ ràng.</p>
    </div>
    <a href="{{ route('admin.departments.create') }}" class="btn btn-primary rounded-3">
        <i class="bi bi-plus-circle me-2"></i>Tạo phòng ban
    </a>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">{{ __('ui.no') }}</th>
            <th>{{ __('ui.name') }}</th>
            <th>{{ __('ui.entity_code') }}</th>
            <th>{{ __('ui.description') }}</th>
            <th width="180">{{ __('ui.action') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($departments as $department)
            <tr>
                <td class="text-muted fw-semibold">{{ $departments->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ $department->name }}</td>
                <td><code>{{ $department->code }}</code></td>
                <td>{{ $department->description ?: '-' }}</td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-sm btn-outline-primary">{{ __('ui.edit') }}</a>
                        <form action="{{ route('admin.departments.destroy', $department) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_delete_department') }}')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">{{ __('ui.delete') }}</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center py-5">
                    <div class="text-muted">{{ __('ui.no_departments') }}</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $departments->links() }}
</div>
@endsection
