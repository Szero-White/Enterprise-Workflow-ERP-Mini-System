@extends('layouts.app')

@section('page_title', __('menu.users'))
@section('page_eyebrow', __('menu.admin').' / '.__('menu.users'))

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">{{ __('menu.users') }}</h2>
        <p class="text-muted mb-0">Quản lý tài khoản nhân viên, phòng ban và phân quyền truy cập.</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary rounded-3">
        <i class="bi bi-plus-circle me-2"></i>Tạo người dùng
    </a>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">{{ __('ui.no') }}</th>
            <th>{{ __('ui.name') }}</th>
            <th>{{ __('ui.email') }}</th>
            <th>{{ __('ui.department') }}</th>
            <th>{{ __('ui.role') }}</th>
            <th>{{ __('ui.status') }}</th>
            <th width="180">{{ __('ui.action') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td class="text-muted fw-semibold">{{ $users->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->department?->name ?? '-' }}</td>
                <td>{{ $user->role ? (trans()->has('ui.roles.'.$user->role->key) ? __('ui.roles.'.$user->role->key) : $user->role->name) : '-' }}</td>
                <td>@include('partials.boolean_badge', ['value' => $user->is_active])</td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">{{ __('ui.edit') }}</a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('{{ __('ui.confirm_delete_user') }}')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">{{ __('ui.delete') }}</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="text-muted">{{ __('ui.no_users') }}</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $users->links() }}
</div>
@endsection
