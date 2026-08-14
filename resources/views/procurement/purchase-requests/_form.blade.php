<div class="mb-4">
    <label class="form-label">{{ __('procurement.purchase_request.purpose') }}</label>
    <textarea name="purpose" class="form-control" rows="4" required>{{ old('purpose', $purchaseRequest->purpose ?? '') }}</textarea>
    @include('partials.form_error', ['field' => 'purpose'])
</div>

<div class="row g-3 mb-4">
    <div class="col-md-5">
        <label class="form-label">{{ __('procurement.purchase_request.required_date') }}</label>
        <input
            type="date"
            name="required_date"
            class="form-control"
            value="{{ old('required_date', isset($purchaseRequest) && $purchaseRequest->required_date ? $purchaseRequest->required_date->format('Y-m-d') : '') }}"
        >
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="mb-0">{{ __('procurement.purchase_request.items') }}</h5>
    <button type="button" class="btn btn-sm btn-light border" data-pr-add-line>
        <i class="bi bi-plus-lg"></i>
        {{ __('procurement.purchase_request.add_line') }}
    </button>
</div>

@php
    $oldLines = old(
        'items',
        isset($purchaseRequest)
            ? $purchaseRequest->items->map(fn ($line) => [
                'item_id' => $line->item_id,
                'quantity' => $line->requested_quantity,
                'estimated_unit_cost' => $line->estimated_unit_cost,
                'note' => $line->note,
            ])->toArray()
            : [[
                'item_id' => '',
                'quantity' => 1,
                'estimated_unit_cost' => 0,
                'note' => '',
            ]]
    );
@endphp

<div class="table-responsive">
    <table class="table erp-table align-middle">
        <thead>
            <tr>
                <th style="min-width: 260px">{{ __('procurement.purchase_request.item') }}</th>
                <th>{{ __('procurement.purchase_request.quantity') }}</th>
                <th>{{ __('procurement.purchase_request.estimated_unit_cost') }}</th>
                <th>{{ __('procurement.purchase_request.note') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody data-pr-lines>
            @foreach($oldLines as $index => $line)
                <tr data-pr-line>
                    <td>
                        <select class="form-select" name="items[{{ $index }}][item_id]" required>
                            <option value="">-- Chọn vật tư --</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" @selected((string) $line['item_id'] === (string) $item->id)>
                                    {{ $item->sku }} · {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input
                            class="form-control"
                            type="number"
                            min="0.001"
                            step="0.001"
                            name="items[{{ $index }}][quantity]"
                            value="{{ $line['quantity'] }}"
                            required
                        >
                    </td>
                    <td>
                        <input
                            class="form-control"
                            type="number"
                            min="0"
                            step="1"
                            name="items[{{ $index }}][estimated_unit_cost]"
                            value="{{ $line['estimated_unit_cost'] }}"
                            required
                        >
                    </td>
                    <td>
                        <input
                            class="form-control"
                            name="items[{{ $index }}][note]"
                            value="{{ $line['note'] ?? '' }}"
                        >
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-light border" data-pr-remove-line>
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@include('partials.form_error', ['field' => 'items'])

<template id="purchase-request-line-template">
    <tr data-pr-line>
        <td>
            <select class="form-select" name="__NAME__[item_id]" required>
                <option value="">-- Chọn vật tư --</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->sku }} · {{ $item->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input class="form-control" type="number" min="0.001" step="0.001" name="__NAME__[quantity]" value="1" required></td>
        <td><input class="form-control" type="number" min="0" step="1" name="__NAME__[estimated_unit_cost]" value="0" required></td>
        <td><input class="form-control" name="__NAME__[note]"></td>
        <td>
            <button type="button" class="btn btn-sm btn-light border" data-pr-remove-line>
                <i class="bi bi-x-lg"></i>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
    <script src="{{ asset('js/purchase-request-form.js') }}" defer></script>
@endpush
