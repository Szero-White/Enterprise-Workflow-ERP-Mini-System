@extends('layouts.app')
@section('page_title', __('catalog.category.index_title'))
@section('page_eyebrow', __('catalog.eyebrow'))
@section('content')
<x-erp.page-header
    :title="__('catalog.category.index_title')"
    :eyebrow="__('catalog.eyebrow')"
    :description="__('catalog.category.index_description')"
>
    <x-slot:actions>
        <a href="{{ route('catalog.categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i>{{ __('catalog.category.add') }}</a>
    </x-slot:actions>
</x-erp.page-header>

<div class="erp-table-card">
    <div class="erp-table-toolbar">
        <form class="erp-filter-bar" method="GET">
            <div class="erp-filter-search">
                <i class="bi bi-search"></i>
                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('catalog.category.search_placeholder') }}">
            </div>
            <button class="btn btn-light border">{{ __('catalog.category.search') }}</button>
            @if(request()->filled('q'))
                <a href="{{ route('catalog.categories.index') }}" class="btn btn-light border">{{ __('catalog.category.clear_filter') }}</a>
            @endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>{{ __('catalog.category.code') }}</th>
                    <th>{{ __('catalog.category.name') }}</th>
                    <th>{{ __('catalog.category.products') }}</th>
                    <th>{{ __('catalog.category.status') }}</th>
                    <th class="text-end">{{ __('catalog.category.actions') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse($categories as $category)
                <tr>
                    <td><span class="erp-record-code">{{ $category->code }}</span></td>
                    <td>
                        <div class="erp-record-primary">{{ $category->name }}</div>
                        <div class="erp-record-secondary text-truncate erp-description-truncate">{{ $category->description ?: '-' }}</div>
                    </td>
                    <td><span class="badge rounded-pill text-bg-light border">{{ number_format($category->products_count) }}</span></td>
                    <td><span class="badge rounded-pill {{ $category->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $category->is_active ? __('catalog.category.active') : __('catalog.category.inactive') }}</span></td>
                    <td class="text-end">
                        <div class="erp-row-actions">
                            <a href="{{ route('catalog.categories.edit', $category) }}" class="btn btn-sm btn-light border erp-action-btn" title="{{ __('catalog.category.edit') }}"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('catalog.categories.destroy', $category) }}" onsubmit="return confirm(@js(__('catalog.category.confirm_delete')))">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger erp-action-btn" title="{{ __('catalog.category.delete') }}"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-erp.empty-state icon="bi-tags" :title="__('catalog.category.empty')" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())<div class="erp-pagination">{{ $categories->links() }}</div>@endif
</div>
@endsection
