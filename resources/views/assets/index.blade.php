@extends('layouts.app')

@section('page_title', __('assets.index_title'))
@section('page_eyebrow', __('assets.eyebrow'))

@section('content')
    <x-erp.page-header
        :title="__('assets.index_title')"
        :eyebrow="__('assets.eyebrow')"
        :description="__('assets.index_description')"
    />

    <x-erp.panel>
        <form method="GET" class="row g-2 mb-4">
            <div class="col-lg-5">
                <input name="q" class="form-control" value="{{ request('q') }}" placeholder="{{ __('assets.search_placeholder') }}">
            </div>
            <div class="col-lg-3">
                <select name="status" class="form-select">
                    <option value="">{{ __('assets.all_statuses') }}</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-light border">
                    <i class="bi bi-funnel"></i>
                    {{ __('assets.filter') }}
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table erp-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('assets.asset_code') }}</th>
                        <th>{{ __('assets.item') }}</th>
                        <th>{{ __('assets.status_label') }}</th>
                        <th>{{ __('assets.warehouse') }}</th>
                        <th>{{ __('assets.holder') }}</th>
                        <th class="text-end">{{ __('assets.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                        <tr>
                            <td>
                                <span class="erp-record-code">{{ $asset->asset_code }}</span>
                                <div class="erp-record-secondary">{{ $asset->serial_number ?: __('assets.serial_missing') }}</div>
                            </td>
                            <td>
                                <div class="erp-record-primary">{{ $asset->item->name }}</div>
                                <div class="erp-record-secondary">{{ $asset->item->sku }}</div>
                            </td>
                            <td>
                                <span class="badge text-bg-light border">{{ $asset->status->label() }}</span>
                                <div class="erp-record-secondary mt-1">{{ $asset->condition->label() }}</div>
                            </td>
                            <td>{{ $asset->warehouse?->name ?? '-' }}</td>
                            <td>{{ $asset->activeAssignment?->assignee?->name ?? '-' }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-light border" href="{{ route('assets.show', $asset) }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-erp.empty-state icon="bi-laptop" :title="__('assets.empty')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assets->hasPages())
            <div class="erp-pagination">{{ $assets->links() }}</div>
        @endif
    </x-erp.panel>
@endsection
