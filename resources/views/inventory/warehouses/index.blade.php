@extends('layouts.app')
@section('page_title', __('inventory.warehouse.index_title'))
@section('page_eyebrow', __('inventory.eyebrow'))
@section('content')
<x-erp.page-header :title="__('inventory.warehouse.index_title')" :eyebrow="__('inventory.eyebrow')" :description="__('inventory.warehouse.index_description')">
    <x-slot:actions>
        <a href="{{ route('inventory.warehouses.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i>{{ __('inventory.warehouse.add') }}</a>
    </x-slot:actions>
</x-erp.page-header>

<div class="erp-table-card">
    <div class="erp-table-toolbar">
        <form method="GET" class="erp-filter-bar">
            <div class="erp-filter-search">
                <i class="bi bi-search"></i>
                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('inventory.warehouse.search_placeholder') }}">
            </div>
            <button class="btn btn-light border">{{ __('inventory.warehouse.search') }}</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>{{ __('inventory.warehouse.code') }}</th><th>{{ __('inventory.warehouse.name') }}</th><th>{{ __('inventory.warehouse.address') }}</th><th>{{ __('inventory.warehouse.tracked_skus') }}</th><th>{{ __('inventory.warehouse.status') }}</th><th class="text-end">{{ __('inventory.warehouse.actions') }}</th></tr></thead>
            <tbody>
            @forelse($warehouses as $warehouse)
                <tr>
                    <td><span class="erp-record-code">{{ $warehouse->code }}</span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="erp-item-thumb"><i class="bi bi-buildings"></i></div>
                            <div class="erp-record-primary">{{ $warehouse->name }}</div>
                        </div>
                    </td>
                    <td>{{ $warehouse->address ?: '-' }}</td>
                    <td><span class="badge rounded-pill text-bg-light border">{{ number_format($warehouse->stocks_count) }}</span></td>
                    <td><span class="badge rounded-pill {{ $warehouse->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $warehouse->is_active ? __('inventory.warehouse.active') : __('inventory.warehouse.inactive') }}</span></td>
                    <td class="text-end">
                        <div class="erp-row-actions">
                            <a href="{{ route('inventory.warehouses.edit', $warehouse) }}" class="btn btn-sm btn-light border erp-action-btn" title="{{ __('inventory.warehouse.edit') }}"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('inventory.warehouses.destroy', $warehouse) }}" data-confirm="{{ __('inventory.warehouse.confirm_delete') }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger erp-action-btn" title="{{ __('inventory.warehouse.delete') }}"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-erp.empty-state icon="bi-buildings" :title="__('inventory.warehouse.empty')" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($warehouses->hasPages())<div class="erp-pagination">{{ $warehouses->links() }}</div>@endif
</div>
@endsection
