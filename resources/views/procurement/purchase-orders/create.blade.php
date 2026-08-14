@extends('layouts.app')

@section('page_title', __('procurement.purchase_order.create_title'))
@section('page_eyebrow', __('procurement.eyebrow'))

@section('content')
    <x-erp.page-header
        :title="__('procurement.purchase_order.create_title')"
        :eyebrow="$purchaseRequest->workflowRequest->request_code"
        :description="$purchaseRequest->purpose"
    />

    <form method="POST" action="{{ route('procurement.purchase-orders.store', $purchaseRequest) }}">
        @csrf
        <div class="erp-form-layout">
            <section class="erp-form-section">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('procurement.purchase_order.supplier') }}</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">{{ __('procurement.purchase_order.select_supplier') }}</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                    {{ $supplier->code }} · {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('procurement.purchase_order.warehouse') }}</label>
                        <select name="warehouse_id" class="form-select" required>
                            <option value="">{{ __('procurement.purchase_order.select_warehouse') }}</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>
                                    {{ $warehouse->code }} · {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">{{ __('procurement.purchase_order.expected_date') }}</label>
                        <input type="date" name="expected_date" class="form-control" value="{{ old('expected_date') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('procurement.purchase_order.note') }}</label>
                        <textarea name="note" class="form-control" rows="3">{{ old('note') }}</textarea>
                    </div>
                </div>

                <h5 class="mb-3">{{ __('procurement.purchase_order.items') }}</h5>
                <div class="table-responsive">
                    <table class="table erp-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('procurement.purchase_request.item') }}</th>
                                <th>{{ __('procurement.purchase_order.requested_quantity') }}</th>
                                <th>{{ __('procurement.purchase_order.estimated_unit_cost') }}</th>
                                <th>{{ __('procurement.purchase_order.unit_cost') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseRequest->items as $index => $line)
                                <tr>
                                    <td>
                                        <div class="erp-record-primary">{{ $line->item_name }}</div>
                                        <div class="erp-record-secondary">{{ $line->item_sku }}</div>
                                        <input
                                            type="hidden"
                                            name="lines[{{ $index }}][purchase_request_item_id]"
                                            value="{{ $line->id }}"
                                        >
                                    </td>
                                    <td>{{ $line->requested_quantity }} {{ $line->unit }}</td>
                                    <td>{{ number_format((float) $line->estimated_unit_cost, 0, ',', '.') }} ₫</td>
                                    <td>
                                        <input
                                            class="form-control"
                                            type="number"
                                            min="0"
                                            step="1"
                                            name="lines[{{ $index }}][unit_cost]"
                                            value="{{ old('lines.'.$index.'.unit_cost', $line->estimated_unit_cost) }}"
                                            required
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
                    <div class="erp-form-actions-card__title">{{ __('procurement.purchase_order.create_title') }}</div>
                    <div class="erp-form-actions-card__hint">
                        {{ __('procurement.purchase_order.draft_hint') }}
                    </div>
                    <button class="btn btn-primary w-100">{{ __('procurement.purchase_order.create') }}</button>
                    <a class="btn btn-light border w-100 mt-2" href="{{ route('procurement.purchase-requests.show', $purchaseRequest) }}">
                        {{ __('ui.cancel') }}
                    </a>
                </div>
            </aside>
        </div>
    </form>
@endsection
