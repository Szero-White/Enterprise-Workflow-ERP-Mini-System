@extends('layouts.app')
@section('page_title', __('catalog.product.index_title'))
@section('page_eyebrow', __('catalog.eyebrow'))
@section('content')
<div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-3">
    <form method="GET" class="erp-filter-bar">
        <input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('catalog.product.search_placeholder') }}">
        <select class="form-select" name="category_id"><option value="">{{ __('catalog.product.all_categories') }}</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)request('category_id') === (string)$category->id)>{{ $category->name }}</option>@endforeach</select>
        <select class="form-select" name="status"><option value="">{{ __('catalog.product.all_statuses') }}</option><option value="active" @selected(request('status')==='active')>{{ __('catalog.product.active') }}</option><option value="inactive" @selected(request('status')==='inactive')>{{ __('catalog.product.inactive') }}</option></select>
        <button class="btn btn-outline-secondary">{{ __('catalog.product.filter') }}</button>
    </form>
    <a href="{{ route('catalog.products.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>{{ __('catalog.product.add') }}</a>
</div>
<div class="content-card overflow-hidden">
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>{{ __('catalog.product.sku') }}</th><th>{{ __('catalog.product.index_title') }}</th><th>{{ __('catalog.product.category') }}</th><th>{{ __('catalog.product.cost_price') }}</th><th>{{ __('catalog.product.sale_price') }}</th><th>{{ __('catalog.product.stock_alert') }}</th><th>{{ __('catalog.product.status') }}</th><th class="text-end">{{ __('catalog.product.actions') }}</th></tr></thead><tbody>
    @forelse($products as $product)<tr>
        <td class="fw-semibold">{{ $product->sku }}</td><td><div class="fw-semibold">{{ $product->name }}</div><div class="small text-muted">{{ __('catalog.product.unit_short') }}: {{ $product->unit }}</div></td><td>{{ $product->category?->name ?? '-' }}</td>
        <td class="erp-money">{{ number_format((float)$product->cost_price,0,',','.') }} ₫</td><td class="erp-money">{{ number_format((float)$product->sale_price,0,',','.') }} ₫</td><td>{{ rtrim(rtrim(number_format((float)$product->reorder_level,3,'.',''),'0'),'.') }}</td>
        <td><span class="badge rounded-pill {{ $product->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $product->is_active ? __('catalog.product.active') : __('catalog.product.inactive') }}</span></td>
        <td class="text-end"><a href="{{ route('catalog.products.edit',$product) }}" class="btn btn-sm btn-outline-primary">{{ __('catalog.product.edit') }}</a><form method="POST" action="{{ route('catalog.products.destroy',$product) }}" class="d-inline" onsubmit="return confirm(@js(__('catalog.product.confirm_delete')))">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">{{ __('catalog.product.delete') }}</button></form></td>
    </tr>@empty<tr><td colspan="8"><div class="erp-empty"><i class="bi bi-box-seam"></i>{{ __('catalog.product.empty') }}</div></td></tr>@endforelse
    </tbody></table></div>
    @if($products->hasPages())<div class="p-3 border-top">{{ $products->links() }}</div>@endif
</div>
@endsection
