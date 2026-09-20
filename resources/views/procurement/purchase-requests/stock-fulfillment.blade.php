@extends('layouts.app')

@section('page_title', __('procurement.stock_fulfillment.title'))
@section('page_eyebrow', __('procurement.eyebrow'))

@section('content')
    <x-erp.page-header
        :title="__('procurement.stock_fulfillment.title')"
        :eyebrow="$purchaseRequest->workflowRequest->request_code"
        :description="__('procurement.stock_fulfillment.description')"
    />

    <form method="POST" action="{{ route('procurement.purchase-requests.stock-fulfillment.store', [$purchaseRequest, $line]) }}">
        @csrf

        <div class="row g-3">
            <div class="col-xl-8">
                <x-erp.panel :title="__('procurement.stock_fulfillment.reserved_assets')">
                    <div class="alert alert-info small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        {{ __('procurement.stock_fulfillment.assignee_hint') }}
                    </div>

                    <div class="table-responsive">
                        <table class="table erp-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('assets.asset_code') }}</th>
                                    <th>{{ __('assets.serial_number') }}</th>
                                    <th>{{ __('assets.warehouse') }}</th>
                                    <th>{{ __('procurement.stock_fulfillment.assignee') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reservedAssets as $asset)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $asset->asset_code }}</div>
                                            <div class="small text-muted">{{ $asset->status->label() }}</div>
                                        </td>
                                        <td>{{ $asset->serial_number ?: '-' }}</td>
                                        <td>{{ $asset->warehouse?->name ?? '-' }}</td>
                                        <td style="min-width: 240px">
                                            <select name="assignments[{{ $asset->id }}]" class="form-select" required>
                                                <option value="">{{ __('procurement.stock_fulfillment.select_assignee') }}</option>
                                                @foreach($assignees as $assignee)
                                                    <option
                                                        value="{{ $assignee->id }}"
                                                        @selected((string) old('assignments.'.$asset->id, $purchaseRequest->workflowRequest->created_by) === (string) $assignee->id)
                                                    >
                                                        {{ $assignee->name }} · {{ $assignee->email }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('assignments.'.$asset->id)
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-muted">{{ __('procurement.stock_fulfillment.no_reserved_assets') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @error('assignments')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </x-erp.panel>
            </div>

            <div class="col-xl-4">
                <x-erp.panel :title="__('procurement.stock_fulfillment.summary')">
                    <dl class="row mb-3">
                        <dt class="col-5">{{ __('procurement.purchase_request.requester') }}</dt>
                        <dd class="col-7">{{ $purchaseRequest->workflowRequest->creator?->name ?? '-' }}</dd>
                        <dt class="col-5">{{ __('procurement.purchase_request.item') }}</dt>
                        <dd class="col-7">{{ $line->item_name }}</dd>
                        <dt class="col-5">{{ __('procurement.purchase_request.quantity') }}</dt>
                        <dd class="col-7">{{ \App\Support\QuantityFormatter::format($line->requested_quantity) }} {{ $line->unit }}</dd>
                        <dt class="col-5">{{ __('procurement.stock_fulfillment.remaining') }}</dt>
                        <dd class="col-7">{{ $remainingQuantity }}</dd>
                    </dl>

                    <button
                        class="btn btn-primary w-100"
                        @disabled($remainingQuantity < 1 || $reservedAssets->count() !== $remainingQuantity)
                    >
                        <i class="bi bi-person-check"></i>
                        {{ __('procurement.stock_fulfillment.confirm_assignment') }}
                    </button>
                </x-erp.panel>
            </div>
        </div>
    </form>
@endsection
