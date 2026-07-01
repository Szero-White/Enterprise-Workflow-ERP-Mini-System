@extends('layouts.app')

@section('page_title', __('menu.roles'))
@section('page_eyebrow', __('menu.admin').' / '.__('menu.roles'))

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">{{ __('menu.roles') }}</h2>
        <p class="text-muted mb-0">Quản lý các vai trò dùng cho phân quyền và quy trình duyệt.</p>
    </div>
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary rounded-3">
        <i class="bi bi-plus-circle me-2"></i>Tạo vai trò
    </a>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">{{ __('ui.no') }}</th>
            <th>{{ __('ui.name') }}</th>
            <th>{{ __('ui.key') }}</th>
            <th>{{ __('ui.description') }}</th>
            <th width="180">{{ __('ui.action') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($roles as $role)
            <tr>
                <td class="text-muted fw-semibold">{{ $roles->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ trans()->has('ui.roles.'.$role->key) ? __('ui.roles.'.$role->key) : $role->name }}</td>
                <td><code>{{ $role->key }}</code></td>
                <td>{{ $role->description ?: '-' }}</td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">{{ __('ui.edit') }}</a>
                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_delete_role') }}')">
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
                    <div class="text-muted">{{ __('ui.no_roles') }}</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $roles->links() }}
</div>
@endsection
