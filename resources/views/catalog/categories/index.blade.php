@extends('layouts.app')
@section('page_title', __('catalog.category.index_title'))
@section('page_eyebrow', __('catalog.eyebrow'))
@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <form class="erp-filter-bar" method="GET">
        <input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('catalog.category.search_placeholder') }}">
        <button class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>{{ __('catalog.category.search') }}</button>
        @if(request()->filled('q'))
            <a href="{{ route('catalog.categories.index') }}" class="btn btn-light border">{{ __('catalog.category.clear_filter') }}</a>
        @endif
    </form>
    <a href="{{ route('catalog.categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>{{ __('catalog.category.add') }}</a>
</div>
<div class="content-card overflow-hidden">
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
                    <td class="fw-semibold">{{ $category->code }}</td>
                    <td><div class="fw-semibold">{{ $category->name }}</div><div class="small text-muted text-truncate" style="max-width:420px">{{ $category->description }}</div></td>
                    <td>{{ $category->products_count }}</td>
                    <td><span class="badge rounded-pill {{ $category->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $category->is_active ? __('catalog.category.active') : __('catalog.category.inactive') }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('catalog.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">{{ __('catalog.category.edit') }}</a>
                        <form method="POST" action="{{ route('catalog.categories.destroy', $category) }}" class="d-inline" onsubmit="return confirm(@js(__('catalog.category.confirm_delete')))">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">{{ __('catalog.category.delete') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="erp-empty"><i class="bi bi-tags"></i>{{ __('catalog.category.empty') }}</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())<div class="p-3 border-top">{{ $categories->links() }}</div>@endif
</div>
@endsection
