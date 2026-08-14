@extends('layouts.app')
@section('page_title', __('catalog.product.index_title'))
@section('page_eyebrow', __('catalog.eyebrow'))
@section('content')
<x-erp.page-header
    :title="__('catalog.product.index_title')"
    :eyebrow="__('catalog.eyebrow')"
    :description="__('catalog.product.index_description')"
>
    <x-slot:actions>
        <a href="{{ route('catalog.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i>{{ __('catalog.product.add') }}
        </a>
    </x-slot:actions>
</x-erp.page-header>

<div class="erp-table-card">
    <div class="erp-table-toolbar">
        <form method="GET" class="erp-filter-bar">
            <div class="erp-filter-search">
                <i class="bi bi-search"></i>
                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('catalog.product.search_placeholder') }}">
            </div>
            <select class="form-select" name="category_id">
                <option value="">{{ __('catalog.product.all_categories') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string)request('category_id') === (string)$category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select class="form-select" name="status">
                <option value="">{{ __('catalog.product.all_statuses') }}</option>
                <option value="active" @selected(request('status') === 'active')>{{ __('catalog.product.active') }}</option>
                <option value="inactive" @selected(request('status') === 'inactive')>{{ __('catalog.product.inactive') }}</option>
            </select>
            <button class="btn btn-light border"><i class="bi bi-funnel"></i>{{ __('catalog.product.filter') }}</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>{{ __('catalog.product.index_title') }}</th>
                    <th>{{ __('catalog.product.category') }}</th>
                    <th>{{ __('catalog.product.cost_price') }}</th>
                    <th>{{ __('catalog.product.sale_price') }}</th>
                    <th>{{ __('catalog.product.stock_alert') }}</th>
                    <th>{{ __('catalog.product.status') }}</th>
                    <th class="text-end">{{ __('catalog.product.actions') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td>
                        <div class="erp-product-cell">
                            <div class="erp-product-thumb"><i class="bi bi-box-seam"></i></div>
                            <div class="min-w-0">
                                <div class="erp-record-primary text-truncate">{{ $product->name }}</div>
                                <div class="erp-record-secondary"><span class="erp-record-code">{{ $product->sku }}</span> · {{ __('catalog.product.unit_short') }} {{ $product->unit }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $product->category?->name ?? '-' }}</td>
                    <td class="erp-money">{{ number_format((float)$product->cost_price, 0, ',', '.') }} ₫</td>
                    <td class="erp-money">{{ number_format((float)$product->sale_price, 0, ',', '.') }} ₫</td>
                    <td>{{ rtrim(rtrim(number_format((float)$product->reorder_level, 3, '.', ''), '0'), '.') }}</td>
                    <td>
                        <span class="badge rounded-pill {{ $product->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ $product->is_active ? __('catalog.product.active') : __('catalog.product.inactive') }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="erp-row-actions">
                            <a href="{{ route('catalog.products.edit', $product) }}" class="btn btn-sm btn-light border erp-action-btn" title="{{ __('catalog.product.edit') }}">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('catalog.products.destroy', $product) }}" onsubmit="return confirm(@js(__('catalog.product.confirm_delete')))">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger erp-action-btn" title="{{ __('catalog.product.delete') }}"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <x-erp.empty-state icon="bi-box-seam" :title="__('catalog.product.empty')">
                            <x-slot:action><a href="{{ route('catalog.products.create') }}" class="btn btn-sm btn-primary">{{ __('catalog.product.add') }}</a></x-slot:action>
                        </x-erp.empty-state>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
        <div class="erp-pagination">{{ $products->links() }}</div>
    @endif
</div>
@endsection
