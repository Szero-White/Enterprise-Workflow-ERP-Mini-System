@extends('layouts.app')
@section('page_title', __('items.category.index_title'))
@section('page_eyebrow', __('items.eyebrow'))
@section('content')
<x-erp.page-header
    :title="__('items.category.index_title')"
    :eyebrow="__('items.eyebrow')"
    :description="__('items.category.index_description')"
>
    <x-slot:actions>
        <a href="{{ route('inventory.item-categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i>{{ __('items.category.add') }}</a>
    </x-slot:actions>
</x-erp.page-header>

<x-erp.panel>
    <form class="row g-2 align-items-end mb-4" method="GET">
        <div class="col-lg-5">
            <label class="form-label">{{ __('items.category.name') }}</label>
            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('items.category.search_placeholder') }}">
        </div>
        <div class="col-lg-7 d-flex gap-2">
            <button class="btn btn-light border">{{ __('items.category.search') }}</button>
            @if(request('q'))
                <a href="{{ route('inventory.item-categories.index') }}" class="btn btn-light border">{{ __('items.category.clear_filter') }}</a>
            @endif
        </div>
    </form>

    <div class="table-responsive">
        <table class="table erp-table align-middle mb-0">
            <thead><tr><th>{{ __('items.category.code') }}</th><th>{{ __('items.category.name') }}</th><th>{{ __('items.category.items') }}</th><th>{{ __('items.category.status') }}</th><th class="text-end">{{ __('items.category.actions') }}</th></tr></thead>
            <tbody>
            @forelse($categories as $category)
                <tr>
                    <td><span class="erp-record-code">{{ $category->code }}</span></td>
                    <td>
                        <div class="erp-record-primary">{{ $category->name }}</div>
                        @if($category->description)<div class="erp-record-secondary text-truncate">{{ $category->description }}</div>@endif
                    </td>
                    <td><span class="badge rounded-pill text-bg-light border">{{ number_format($category->items_count) }}</span></td>
                    <td><span class="badge rounded-pill {{ $category->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $category->is_active ? __('items.category.active') : __('items.category.inactive') }}</span></td>
                    <td>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('inventory.item-categories.edit', $category) }}" class="btn btn-sm btn-light border erp-action-btn" title="{{ __('items.category.edit') }}"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('inventory.item-categories.destroy', $category) }}" data-confirm="{{ __('items.category.confirm_delete') }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger erp-action-btn" title="{{ __('items.category.delete') }}"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-erp.empty-state icon="bi-tags" :title="__('items.category.empty')" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())<div class="erp-pagination">{{ $categories->links() }}</div>@endif
</x-erp.panel>
@endsection
