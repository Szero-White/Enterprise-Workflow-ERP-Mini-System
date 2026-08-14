@extends('layouts.app')

@section('page_title', $asset->asset_code)
@section('page_eyebrow', __('assets.eyebrow'))

@section('content')
    <x-erp.page-header
        :title="$asset->asset_code"
        :eyebrow="__('assets.show_title')"
        :description="$asset->item->sku.' · '.$asset->item->name"
    >
        @if(auth()->user()->hasRole(['asset_manager', 'admin']))
            <x-slot:actions>
                @if($asset->status === \App\Enums\AssetStatus::Available)
                    <a class="btn btn-primary" href="{{ route('assets.assignments.create', $asset) }}">
                        <i class="bi bi-person-check"></i>
                        {{ __('assets.assign') }}
                    </a>
                @elseif($asset->status === \App\Enums\AssetStatus::Assigned && $asset->activeAssignment)
                    <a class="btn btn-primary" href="{{ route('assets.returns.create', $asset->activeAssignment) }}">
                        <i class="bi bi-box-arrow-in-left"></i>
                        {{ __('assets.return') }}
                    </a>
                @elseif($asset->status === \App\Enums\AssetStatus::Maintenance)
                    <form method="POST" action="{{ route('assets.maintenance.complete', $asset) }}">
                        @csrf
                        <button class="btn btn-primary">
                            <i class="bi bi-tools"></i>
                            {{ __('assets.maintenance_complete') }}
                        </button>
                    </form>
                @endif
                <a class="btn btn-light border" href="{{ route('assets.edit', $asset) }}">
                    <i class="bi bi-pencil"></i>
                    {{ __('assets.edit') }}
                </a>
            </x-slot:actions>
        @endif
    </x-erp.page-header>

    <div class="row g-3">
        <div class="col-lg-7">
            <x-erp.panel :title="__('assets.show_title')">
                <dl class="row mb-0">
                    <dt class="col-sm-4">{{ __('assets.item') }}</dt>
                    <dd class="col-sm-8">{{ $asset->item->name }} <span class="text-muted">({{ $asset->item->sku }})</span></dd>

                    <dt class="col-sm-4">{{ __('assets.serial_number') }}</dt>
                    <dd class="col-sm-8">{{ $asset->serial_number ?: '-' }}</dd>

                    <dt class="col-sm-4">{{ __('assets.status_label') }}</dt>
                    <dd class="col-sm-8"><span class="badge text-bg-light border">{{ $asset->status->label() }}</span></dd>

                    <dt class="col-sm-4">{{ __('assets.condition_label') }}</dt>
                    <dd class="col-sm-8">{{ $asset->condition->label() }}</dd>

                    <dt class="col-sm-4">{{ __('assets.warehouse') }}</dt>
                    <dd class="col-sm-8">{{ $asset->warehouse?->name ?? '-' }}</dd>

                    <dt class="col-sm-4">{{ __('assets.acquired_at') }}</dt>
                    <dd class="col-sm-8">{{ $asset->acquired_at->format('d/m/Y') }}</dd>

                    <dt class="col-sm-4">{{ __('assets.acquisition_cost') }}</dt>
                    <dd class="col-sm-8">{{ number_format((float) $asset->acquisition_cost, 0, ',', '.') }} ₫</dd>

                    <dt class="col-sm-4">{{ __('assets.source_receipt') }}</dt>
                    <dd class="col-sm-8">
                        @if($asset->sourceReceiptItem?->goodsReceipt)
                            @if(auth()->user()->hasRole(['procurement', 'admin']))
                                <a href="{{ route('procurement.goods-receipts.show', $asset->sourceReceiptItem->goodsReceipt) }}">
                                    {{ $asset->sourceReceiptItem->goodsReceipt->receipt_number }}
                                </a>
                            @else
                                {{ $asset->sourceReceiptItem->goodsReceipt->receipt_number }}
                            @endif
                        @else
                            -
                        @endif
                    </dd>

                    <dt class="col-sm-4">{{ __('assets.note') }}</dt>
                    <dd class="col-sm-8">{{ $asset->note ?: '-' }}</dd>
                </dl>
            </x-erp.panel>
        </div>

        <div class="col-lg-5">
            <x-erp.panel :title="__('assets.assignment.history')">
                <div class="d-flex flex-column gap-3">
                    @forelse($asset->assignments->sortByDesc('assigned_at') as $assignment)
                        <div class="border rounded-3 p-3">
                            <div class="d-flex justify-content-between gap-2">
                                <div>
                                    <div class="erp-record-primary">{{ $assignment->assignee->name }}</div>
                                    <div class="erp-record-secondary">{{ $assignment->assigned_at->format('d/m/Y H:i') }}</div>
                                </div>
                                <span class="badge text-bg-light border align-self-start">
                                    {{ $assignment->returnRecord ? __('assets.assignment.returned') : __('assets.assignment.active') }}
                                </span>
                            </div>
                            <div class="small text-muted mt-2">
                                {{ __('assets.assignment.source_warehouse') }}: {{ $assignment->sourceWarehouse->name }}
                            </div>
                            @if($assignment->returnRecord)
                                <div class="small text-muted mt-1">
                                    {{ __('assets.return_form.warehouse') }}: {{ $assignment->returnRecord->warehouse->name }} · {{ $assignment->returnRecord->returned_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <x-erp.empty-state icon="bi-person-check" :title="__('assets.assignment.history_empty')" />
                    @endforelse
                </div>
            </x-erp.panel>
        </div>
    </div>
@endsection
