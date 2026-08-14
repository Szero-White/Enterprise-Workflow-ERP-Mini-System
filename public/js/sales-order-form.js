(() => {
    const dataNode = document.getElementById('sales-order-form-data');
    const tbody = document.getElementById('order-items');
    const addButton = document.getElementById('add-row');
    const discountInput = document.getElementById('discount');

    if (!dataNode || !tbody || !addButton || !discountInput) return;

    const config = JSON.parse(dataNode.textContent);
    const products = Array.isArray(config.products) ? config.products : [];
    const initialItems = Array.isArray(config.items) && config.items.length
        ? config.items
        : [{ product_id: '', quantity: 1 }];

    const money = (value) => `${new Intl.NumberFormat('vi-VN').format(Math.max(0, Number(value || 0)))} ₫`;
    const findProduct = (id) => products.find((product) => String(product.id) === String(id));

    const createProductSelect = (selectedId) => {
        const select = document.createElement('select');
        select.className = 'form-select product-select';
        select.required = true;

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = config.labels.selectProduct;
        select.appendChild(placeholder);

        products.forEach((product) => {
            const option = document.createElement('option');
            option.value = product.id;
            option.textContent = `${product.sku} - ${product.name}`;
            option.selected = String(product.id) === String(selectedId || '');
            select.appendChild(option);
        });

        return select;
    };

    const createQuantityInput = (quantity) => {
        const input = document.createElement('input');
        input.className = 'form-control quantity-input';
        input.type = 'number';
        input.min = '0.001';
        input.step = '0.001';
        input.required = true;
        input.value = quantity || 1;
        return input;
    };

    const createOrderRow = (item = {}) => {
        const row = document.createElement('tr');
        const productCell = document.createElement('td');
        const quantityCell = document.createElement('td');
        const priceCell = document.createElement('td');
        const totalCell = document.createElement('td');
        const actionCell = document.createElement('td');

        productCell.appendChild(createProductSelect(item.product_id));
        quantityCell.appendChild(createQuantityInput(item.quantity));

        priceCell.className = 'erp-money price-view';
        totalCell.className = 'erp-order-line-total line-total';

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn btn-sm btn-outline-danger erp-action-btn remove-row';
        removeButton.title = config.labels.removeLine;
        removeButton.setAttribute('aria-label', config.labels.removeLine);
        removeButton.innerHTML = '<i class="bi bi-trash3"></i>';
        actionCell.appendChild(removeButton);

        row.append(productCell, quantityCell, priceCell, totalCell, actionCell);
        tbody.appendChild(row);
    };

    const reindexRows = () => {
        [...tbody.children].forEach((row, index) => {
            row.querySelector('.product-select').name = `items[${index}][product_id]`;
            row.querySelector('.quantity-input').name = `items[${index}][quantity]`;
        });
    };

    const recalculate = () => {
        let subtotal = 0;

        [...tbody.children].forEach((row) => {
            const product = findProduct(row.querySelector('.product-select').value);
            const quantity = Number(row.querySelector('.quantity-input').value || 0);
            const price = Number(product?.price || 0);
            const lineTotal = quantity * price;

            subtotal += lineTotal;
            row.querySelector('.price-view').textContent = money(price);
            row.querySelector('.line-total').textContent = money(lineTotal);
        });

        const discount = Number(discountInput.value || 0);
        document.getElementById('subtotal-view').textContent = money(subtotal);
        document.getElementById('total-view').textContent = money(subtotal - discount);
    };

    const addRow = (item = { quantity: 1 }) => {
        createOrderRow(item);
        reindexRows();
        recalculate();
    };

    addButton.addEventListener('click', () => addRow());
    discountInput.addEventListener('input', recalculate);

    tbody.addEventListener('change', (event) => {
        if (event.target.matches('.product-select')) recalculate();
    });

    tbody.addEventListener('input', (event) => {
        if (event.target.matches('.quantity-input')) recalculate();
    });

    tbody.addEventListener('click', (event) => {
        const button = event.target.closest('.remove-row');
        if (!button || tbody.children.length === 1) return;

        button.closest('tr').remove();
        reindexRows();
        recalculate();
    });

    initialItems.forEach(addRow);
})();
