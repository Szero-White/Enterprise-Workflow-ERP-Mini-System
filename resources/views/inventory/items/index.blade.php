@extends('layouts.app')
@section('page_title', __('items.item.index_title'))
@section('page_eyebrow', __('items.eyebrow'))
@section('content')
<x-erp.page-header
    :title="__('items.item.index_title')"
    :eyebrow="__('items.eyebrow')"
    :description="__('items.item.index_description')"
>
    <x-slot:actions>
        <a href="{{ route('inventory.items.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i>{{ __('items.item.add') }}</a>
    </x-slot:actions>
</x-erp.page-header>

<x-erp.panel>
    <form class="row g-2 align-items-end mb-4" method="GET">
        <div class="col-lg-4">
            <label class="form-label">{{ __('items.item.name') }}</label>
            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('items.item.search_placeholder') }}">
        </div>
        <div class="col-lg-3">
            <label class="form-label">{{ __('items.item.category') }}</label>
            <select class="form-select" name="category_id">
                <option value="">{{ __('items.item.all_categories') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2">
            <label class="form-label">{{ __('items.item.status') }}</label>
            <select class="form-select" name="status">
                <option value="">{{ __('items.item.all_statuses') }}</option>
                <option value="active" @selected(request('status') === 'active')>{{ __('items.item.active') }}</option>
                <option value="inactive" @selected(request('status') === 'inactive')>{{ __('items.item.inactive') }}</option>
            </select>
        </div>
        <div class="col-lg-3 d-flex gap-2">
            <button class="btn btn-light border"><i class="bi bi-funnel"></i>{{ __('items.item.filter') }}</button>
            @if(request()->hasAny(['q', 'category_id', 'status']))
                <a href="{{ route('inventory.items.index') }}" class="btn btn-light border">{{ __('items.category.clear_filter') }}</a>
            @endif
        </div>
    </form>

    <div class="table-responsive">
        <table class="table erp-table align-middle mb-0">
            <thead>
                <tr>
                    <th>{{ __('items.item.index_title') }}</th>
                    <th>{{ __('items.item.category') }}</th>
                    <th>{{ __('items.item.cost_price') }}</th>
                    <th>{{ __('items.item.stock_alert') }}</th>
                    <th>{{ __('items.item.status') }}</th>
                    <th class="text-end">{{ __('items.item.actions') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    <td>
                        <div class="erp-item-cell">
                            <div class="erp-item-thumb"><i class="bi bi-box-seam"></i></div>
                            <div class="min-w-0">
                                <div class="erp-record-primary text-truncate">{{ $item->name }}</div>
                                <div class="erp-record-secondary"><span class="erp-record-code">{{ $item->sku }}</span> · {{ __('items.item.unit_short') }} {{ $item->unit }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $item->category?->name ?? __('items.item.uncategorized') }}</td>
                    <td class="erp-money">{{ number_format((float) $item->cost_price, 0, ',', '.') }} ₫</td>
                    <td>{{ rtrim(rtrim(number_format((float) $item->reorder_level, 3, '.', ''), '0'), '.') }}</td>
                    <td>
                        <span class="badge rounded-pill {{ $item->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ $item->is_active ? __('items.item.active') : __('items.item.inactive') }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('inventory.items.edit', $item) }}" class="btn btn-sm btn-light border erp-action-btn" title="{{ __('items.item.edit') }}"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('inventory.items.destroy', $item) }}" onsubmit="return confirm(@js(__('items.item.confirm_delete')))">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger erp-action-btn" title="{{ __('items.item.delete') }}"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <x-erp.empty-state icon="bi-box-seam" :title="__('items.item.empty')">
                            <x-slot:action><a href="{{ route('inventory.items.create') }}" class="btn btn-sm btn-primary">{{ __('items.item.add') }}</a></x-slot:action>
                        </x-erp.empty-state>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($items->hasPages())
        <div class="erp-pagination">{{ $items->links() }}</div>
    @endif
</x-erp.panel>
@endsection
