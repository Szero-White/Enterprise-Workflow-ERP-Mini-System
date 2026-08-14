@extends('layouts.app')
@section('page_title', __('crm.index_title'))
@section('page_eyebrow', __('crm.eyebrow'))
@section('content')
<x-erp.page-header :title="__('crm.index_title')" :eyebrow="__('crm.eyebrow')" :description="__('crm.index_description')">
    <x-slot:actions>
        <a href="{{ route('crm.customers.create') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i>{{ __('crm.add') }}</a>
    </x-slot:actions>
</x-erp.page-header>

<div class="erp-table-card">
    <div class="erp-table-toolbar">
        <form method="GET" class="erp-filter-bar">
            <div class="erp-filter-search">
                <i class="bi bi-search"></i>
                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('crm.search_placeholder') }}">
            </div>
            <button class="btn btn-light border">{{ __('crm.search') }}</button>
            @if(request()->filled('q'))<a href="{{ route('crm.customers.index') }}" class="btn btn-light border">{{ __('crm.clear_filter') }}</a>@endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>{{ __('crm.customer') }}</th><th>{{ __('crm.contact') }}</th><th>{{ __('crm.company') }}</th><th>{{ __('crm.orders') }}</th><th>{{ __('crm.status') }}</th><th class="text-end">{{ __('crm.actions') }}</th></tr></thead>
            <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="erp-customer-avatar">{{ \Illuminate\Support\Str::of($customer->name)->explode(' ')->filter()->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->take(2)->implode('') }}</div>
                            <div class="min-w-0">
                                <div class="erp-record-primary text-truncate">{{ $customer->name }}</div>
                                <div class="erp-record-secondary"><span class="erp-record-code">{{ $customer->code }}</span>@if($customer->address) · {{ \Illuminate\Support\Str::limit($customer->address, 34) }}@endif</div>
                            </div>
                        </div>
                    </td>
                    <td><div>{{ $customer->phone ?: '-' }}</div><div class="erp-record-secondary">{{ $customer->email ?: '-' }}</div></td>
                    <td>{{ $customer->company_name ?: '-' }}</td>
                    <td><span class="badge rounded-pill text-bg-light border">{{ number_format($customer->sales_orders_count) }}</span></td>
                    <td><span class="badge rounded-pill {{ $customer->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $customer->is_active ? __('crm.active') : __('crm.inactive') }}</span></td>
                    <td class="text-end">
                        <div class="erp-row-actions">
                            <a href="{{ route('crm.customers.edit', $customer) }}" class="btn btn-sm btn-light border erp-action-btn" title="{{ __('crm.edit') }}"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('crm.customers.destroy', $customer) }}" onsubmit="return confirm(@js(__('crm.confirm_delete')))">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger erp-action-btn" title="{{ __('crm.delete') }}"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-erp.empty-state icon="bi-people" :title="__('crm.empty')" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())<div class="erp-pagination">{{ $customers->links() }}</div>@endif
</div>
@endsection
