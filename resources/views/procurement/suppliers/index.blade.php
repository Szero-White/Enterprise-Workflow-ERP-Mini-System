@extends('layouts.app')

@section('page_title', __('procurement.supplier.index_title'))
@section('page_eyebrow', __('procurement.eyebrow'))

@section('content')
    <x-erp.page-header
        :title="__('procurement.supplier.index_title')"
        :eyebrow="__('procurement.eyebrow')"
        :description="__('procurement.supplier.index_description')"
    >
        <x-slot:actions>
            <a class="btn btn-primary" href="{{ route('procurement.suppliers.create') }}">
                <i class="bi bi-plus-lg"></i>
                {{ __('procurement.supplier.add') }}
            </a>
        </x-slot:actions>
    </x-erp.page-header>

    <x-erp.panel>
        <form method="GET" class="row g-2 mb-4">
            <div class="col-lg-5">
                <input
                    name="q"
                    class="form-control"
                    value="{{ request('q') }}"
                    placeholder="{{ __('procurement.supplier.search') }}"
                >
            </div>
            <div class="col-auto">
                <button class="btn btn-light border">
                    <i class="bi bi-search"></i>
                    {{ __('ui.search') }}
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table erp-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('procurement.supplier.code') }}</th>
                        <th>{{ __('procurement.supplier.name') }}</th>
                        <th>{{ __('procurement.supplier.contact_name') }}</th>
                        <th>{{ __('procurement.supplier.payment_terms') }}</th>
                        <th class="text-end">{{ __('procurement.supplier.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td><span class="erp-record-code">{{ $supplier->code }}</span></td>
                            <td>
                                <div class="erp-record-primary">{{ $supplier->name }}</div>
                                <div class="erp-record-secondary">{{ $supplier->tax_code ?: '-' }}</div>
                            </td>
                            <td>
                                {{ $supplier->contact_name ?: '-' }}
                                <div class="small text-muted">{{ $supplier->phone ?: '-' }}</div>
                            </td>
                            <td>{{ $supplier->payment_terms ?: '-' }}</td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a
                                        class="btn btn-sm btn-light border"
                                        href="{{ route('procurement.suppliers.edit', $supplier) }}"
                                        aria-label="{{ __('ui.edit') }}"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('procurement.suppliers.destroy', $supplier) }}"
                                        onsubmit="return confirm('Xóa nhà cung cấp này?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" aria-label="{{ __('ui.delete') }}">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-erp.empty-state icon="bi-truck" :title="__('procurement.supplier.empty')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
            <div class="erp-pagination">{{ $suppliers->links() }}</div>
        @endif
    </x-erp.panel>
@endsection
