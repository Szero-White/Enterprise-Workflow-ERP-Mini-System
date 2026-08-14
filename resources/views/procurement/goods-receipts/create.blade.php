@extends('layouts.app')

@section('page_title', __('procurement.goods_receipt.create_title'))
@section('page_eyebrow', __('procurement.eyebrow'))

@section('content')
    <x-erp.page-header
        :title="__('procurement.goods_receipt.create_title')"
        :eyebrow="$purchaseOrder->po_number"
        :description="$purchaseOrder->supplier->name.' → '.$purchaseOrder->warehouse->name"
    />

    <form method="POST" action="{{ route('procurement.goods-receipts.store', $purchaseOrder) }}">
        @csrf
        <div class="erp-form-layout">
            <section class="erp-form-section">
                <div class="row g-3 mb-4">
                    <div class="col-md-5">
                        <label class="form-label">{{ __('procurement.goods_receipt.received_at') }}</label>
                        <input
                            type="datetime-local"
                            name="received_at"
                            class="form-control"
                            value="{{ old('received_at', now()->format('Y-m-d\\TH:i')) }}"
                            required
                        >
                    </div>
                    <div class="col-md-7">
                        <label class="form-label">{{ __('procurement.goods_receipt.supplier_reference') }}</label>
                        <input name="supplier_reference" class="form-control" value="{{ old('supplier_reference') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('procurement.purchase_order.note') }}</label>
                        <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table erp-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('procurement.goods_receipt.item') }}</th>
                                <th>{{ __('procurement.goods_receipt.ordered_quantity') }}</th>
                                <th>{{ __('procurement.goods_receipt.received_quantity') }}</th>
                                <th>{{ __('procurement.goods_receipt.remaining_quantity') }}</th>
                                <th>{{ __('procurement.goods_receipt.quantity') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $index => $line)
                                <tr>
                                    <td>
                                        <div class="erp-record-primary">{{ $line->item_name }}</div>
                                        <div class="erp-record-secondary">{{ $line->item_sku }}</div>
                                        <input
                                            type="hidden"
                                            name="lines[{{ $index }}][purchase_order_item_id]"
                                            value="{{ $line->id }}"
                                        >
                                    </td>
                                    <td>{{ $line->ordered_quantity }} {{ $line->unit }}</td>
                                    <td>{{ $line->received_quantity }} {{ $line->unit }}</td>
                                    <td>{{ $line->outstanding_quantity }} {{ $line->unit }}</td>
                                    <td>
                                        <input
                                            class="form-control"
                                            name="lines[{{ $index }}][quantity]"
                                            type="number"
                                            step="0.001"
                                            min="0"
                                            max="{{ $line->outstanding_quantity }}"
                                            value="{{ old('lines.'.$index.'.quantity', 0) }}"
                                        >
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @include('partials.form_error', ['field' => 'lines'])
            </section>

            <aside class="erp-form-aside">
                <div class="erp-form-actions-card">
                    <div class="erp-form-actions-card__title">{{ __('procurement.goods_receipt.create_title') }}</div>
                    <div class="erp-form-actions-card__hint">
                        {{ __('procurement.goods_receipt.transaction_hint') }}
                    </div>
                    <button class="btn btn-primary w-100">{{ __('procurement.goods_receipt.post') }}</button>
                    <a class="btn btn-light border w-100 mt-2" href="{{ route('procurement.purchase-orders.show', $purchaseOrder) }}">
                        {{ __('ui.cancel') }}
                    </a>
                </div>
            </aside>
        </div>
    </form>
@endsection
