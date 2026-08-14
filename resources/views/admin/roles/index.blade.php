@extends('layouts.app')

@section('page_title', __('menu.roles'))
@section('page_eyebrow', __('menu.admin').' / '.__('menu.roles'))

@section('content')
<x-erp.page-header
    :title="__('menu.roles')"
    :eyebrow="__('menu.admin')"
    :description="__('ui.roles_description')"
>
    <x-slot:actions>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i>{{ __('ui.create_role') }}
        </a>
    </x-slot:actions>
</x-erp.page-header>

<div class="erp-table-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
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
                    <td class="text-muted">{{ $roles->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="erp-record-primary">{{ trans()->has('ui.roles.'.$role->key) ? __('ui.roles.'.$role->key) : $role->name }}</span>
                            @if($role->isSystemRole())
                                <span class="badge rounded-pill text-bg-light border"><i class="bi bi-lock-fill me-1"></i>{{ __('ui.system_role') }}</span>
                            @endif
                        </div>
                    </td>
                    <td><code class="erp-record-code">{{ $role->key }}</code></td>
                    <td>{{ $role->description ?: '-' }}</td>
                    <td>
                        <div class="erp-row-actions">
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-light border erp-action-btn" title="{{ __('ui.edit') }}">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @unless($role->isSystemRole())
                                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" data-confirm="{{ __('ui.confirm_delete_role') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger erp-action-btn" title="{{ __('ui.delete') }}"><i class="bi bi-trash"></i></button>
                                </form>
                            @endunless
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-erp.empty-state icon="bi-shield-lock" :title="__('ui.no_roles')" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="erp-pagination">{{ $roles->links() }}</div>
</div>
@endsection
