@extends('layouts.app')
@section('page_title', __('sales.create.title'))
@section('page_eyebrow', __('sales.eyebrow'))
@section('content')
<form method="POST" action="{{ route('sales.orders.store') }}" id="order-form">@csrf
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="content-card p-3 p-lg-4 mb-3">
                <h2 class="erp-section-title mb-3">{{ __('sales.create.order_information') }}</h2>
                <div class="row g-3">
                    <div class="col-md-5"><label class="form-label erp-required">{{ __('sales.create.customer') }}</label><select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required><option value="">{{ __('sales.create.select_customer') }}</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected((string)old('customer_id') === (string)$customer->id)>{{ $customer->code }} - {{ $customer->name }}</option>@endforeach</select>@error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4"><label class="form-label erp-required">{{ __('sales.create.warehouse') }}</label><select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required><option value="">{{ __('sales.create.select_warehouse') }}</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected((string)old('warehouse_id') === (string)$warehouse->id)>{{ $warehouse->code }} - {{ $warehouse->name }}</option>@endforeach</select>@error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-3"><label class="form-label erp-required">{{ __('sales.create.order_date') }}</label><input type="date" name="order_date" class="form-control" value="{{ old('order_date', now()->toDateString()) }}" required></div>
                    <div class="col-12"><label class="form-label">{{ __('sales.create.notes') }}</label><textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea></div>
                </div>
            </div>

            <div class="content-card overflow-hidden">
                <div class="p-3 p-lg-4 border-bottom d-flex justify-content-between align-items-center gap-3"><div><h2 class="erp-section-title">{{ __('sales.create.products') }}</h2><p class="erp-section-subtitle">{{ __('sales.create.products_description') }}</p></div><button type="button" class="btn btn-sm btn-outline-primary" id="add-row"><i class="bi bi-plus-lg me-1"></i>{{ __('sales.create.add_line') }}</button></div>
                <div class="table-responsive"><table class="table align-middle"><thead><tr><th style="min-width:300px">{{ __('sales.create.products') }}</th><th style="width:140px">{{ __('sales.create.quantity') }}</th><th style="width:180px">{{ __('sales.create.unit_price') }}</th><th style="width:180px">{{ __('sales.create.line_total') }}</th><th style="width:60px"></th></tr></thead><tbody id="order-items"></tbody></table></div>
                @error('items')<div class="alert alert-danger m-3">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-xl-4">
            <div class="content-card p-3 p-lg-4 position-sticky" style="top:100px">
                <h2 class="erp-section-title mb-3">{{ __('sales.create.payment_summary') }}</h2>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">{{ __('sales.create.subtotal') }}</span><strong id="subtotal-view">0 ₫</strong></div>
                <div class="mb-3"><label class="form-label">{{ __('sales.create.discount') }}</label><input type="number" min="0" step="0.01" name="discount_amount" id="discount" class="form-control" value="{{ old('discount_amount',0) }}"></div>
                <div class="border-top pt-3 d-flex justify-content-between align-items-center"><span class="fw-semibold">{{ __('sales.create.total') }}</span><span class="fs-4 fw-bold" id="total-view">0 ₫</span></div>
                <div class="alert alert-info small mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>{{ __('sales.create.draft_notice', ['status' => __('sales.status.draft')]) }}</div>
                <button class="btn btn-primary w-100 mt-3">{{ __('sales.create.save_draft') }}</button>
                <a href="{{ route('sales.orders.index') }}" class="btn btn-light border w-100 mt-2">{{ __('sales.create.cancel') }}</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
const products = @json($products->map(fn($product) => ['id'=>$product->id,'sku'=>$product->sku,'name'=>$product->name,'price'=>(float)$product->sale_price,'unit'=>$product->unit])->values());
const oldItems = @json(old('items', [['product_id'=>'','quantity'=>1]]));
const tbody = document.getElementById('order-items');
const selectProductLabel = @json(__('sales.create.select_product'));
const catalogPriceAria = @json(__('sales.create.catalog_price_aria'));
const money = value => new Intl.NumberFormat('vi-VN').format(Math.max(0, Number(value || 0))) + ' ₫';

function productOptions(selected='') {
    return `<option value="">${selectProductLabel}</option>` + products.map(p => `<option value="${p.id}" ${String(p.id)===String(selected)?'selected':''}>${p.sku} - ${p.name}</option>`).join('');
}
function addRow(item={}) {
    const index = tbody.children.length;
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><select class="form-select product-select" name="items[${index}][product_id]" required>${productOptions(item.product_id || '')}</select></td>
        <td><input class="form-control quantity-input" type="number" min="0.001" step="0.001" name="items[${index}][quantity]" value="${item.quantity || 1}" required></td>
        <td><input class="form-control price-input" type="number" min="0" step="0.01" value="" readonly tabindex="-1" aria-label="${catalogPriceAria}"></td>
        <td class="fw-semibold line-total">0 ₫</td>
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>`;
    tbody.appendChild(row);
    const select = row.querySelector('.product-select');
    if (!row.querySelector('.price-input').value && select.value) {
        const p = products.find(product => String(product.id) === String(select.value));
        if (p) row.querySelector('.price-input').value = p.price;
    }
    recalc();
}
function reindex() {
    [...tbody.children].forEach((row,index) => {
        row.querySelector('.product-select').name = `items[${index}][product_id]`;
        row.querySelector('.quantity-input').name = `items[${index}][quantity]`;
    });
}
function recalc() {
    let subtotal = 0;
    [...tbody.children].forEach(row => {
        const qty = Number(row.querySelector('.quantity-input').value || 0);
        const price = Number(row.querySelector('.price-input').value || 0);
        const line = qty * price;
        subtotal += line;
        row.querySelector('.line-total').textContent = money(line);
    });
    const discount = Number(document.getElementById('discount').value || 0);
    document.getElementById('subtotal-view').textContent = money(subtotal);
    document.getElementById('total-view').textContent = money(subtotal - discount);
}

document.getElementById('add-row').addEventListener('click', () => addRow({quantity:1}));
tbody.addEventListener('change', event => {
    if (event.target.matches('.product-select')) {
        const row = event.target.closest('tr');
        const p = products.find(product => String(product.id) === String(event.target.value));
        if (p) row.querySelector('.price-input').value = p.price;
    }
    recalc();
});
tbody.addEventListener('input', recalc);
tbody.addEventListener('click', event => {
    const button = event.target.closest('.remove-row');
    if (!button) return;
    if (tbody.children.length === 1) return;
    button.closest('tr').remove(); reindex(); recalc();
});
document.getElementById('discount').addEventListener('input', recalc);
oldItems.forEach(addRow);
</script>
@endpush
