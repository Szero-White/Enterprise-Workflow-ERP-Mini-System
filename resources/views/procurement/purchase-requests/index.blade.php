@extends('layouts.app')

@section('page_title', __('procurement.purchase_request.index_title'))
@section('page_eyebrow', __('procurement.eyebrow'))

@section('content')
    <x-erp.page-header
        :title="__('procurement.purchase_request.index_title')"
        :eyebrow="__('procurement.eyebrow')"
        :description="__('procurement.purchase_request.index_description')"
    >
        @can('create', \App\Models\PurchaseRequest::class)
            <x-slot:actions>
                <a class="btn btn-primary" href="{{ route('procurement.purchase-requests.create') }}">
                    <i class="bi bi-plus-lg"></i>
                    {{ __('procurement.purchase_request.create_title') }}
                </a>
            </x-slot:actions>
        @endcan
    </x-erp.page-header>

    <x-erp.panel>
        <form method="GET" class="row g-2 mb-4">
            <div class="col-lg-5">
                <input
                    name="q"
                    class="form-control"
                    value="{{ request('q') }}"
                    placeholder="{{ __('procurement.purchase_request.search') }}"
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
                        <th>{{ __('procurement.purchase_request.code') }}</th>
                        <th>{{ __('procurement.purchase_request.requester') }}</th>
                        <th>{{ __('procurement.purchase_request.estimated_total') }}</th>
                        <th>{{ __('procurement.purchase_request.workflow_status') }}</th>
                        <th>{{ __('procurement.purchase_request.procurement_status') }}</th>
                        <th class="text-end">{{ __('ui.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseRequests as $purchaseRequest)
                        <tr>
                            <td>
                                <span class="erp-record-code">{{ $purchaseRequest->workflowRequest->request_code }}</span>
                                <div class="erp-record-secondary">{{ $purchaseRequest->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>{{ $purchaseRequest->workflowRequest->creator?->name ?? '-' }}</td>
                            <td>{{ number_format((int) $purchaseRequest->estimated_total, 0, ',', '.') }} ₫</td>
                            <td>@include('partials.status_badge', ['status' => $purchaseRequest->workflowRequest->status])</td>
                            <td><span class="badge text-bg-light border">{{ $purchaseRequest->status->label() }}</span></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-light border" href="{{ route('procurement.purchase-requests.show', $purchaseRequest) }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-erp.empty-state icon="bi-cart-check" :title="__('procurement.purchase_request.empty')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchaseRequests->hasPages())
            <div class="erp-pagination">{{ $purchaseRequests->links() }}</div>
        @endif
    </x-erp.panel>
@endsection
