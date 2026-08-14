document.addEventListener('DOMContentLoaded', () => {
    const linesContainer = document.querySelector('[data-pr-lines]');
    const template = document.querySelector('#purchase-request-line-template');
    const addButton = document.querySelector('[data-pr-add-line]');

    if (!linesContainer || !template || !addButton) {
        return;
    }

    const reindexLines = () => {
        linesContainer.querySelectorAll('[data-pr-line]').forEach((row, index) => {
            row.querySelectorAll('[name]').forEach((field) => {
                field.name = field.name.replace(/items\[\d+\]/, `items[${index}]`);
            });
        });
    };

    const bindRemoveButtons = () => {
        linesContainer.querySelectorAll('[data-pr-remove-line]').forEach((button) => {
            button.onclick = () => {
                if (linesContainer.querySelectorAll('[data-pr-line]').length === 1) {
                    return;
                }

                button.closest('[data-pr-line]')?.remove();
                reindexLines();
            };
        });
    };

    addButton.addEventListener('click', () => {
        const index = linesContainer.querySelectorAll('[data-pr-line]').length;
        const html = template.innerHTML.replaceAll('__NAME__', `items[${index}]`);
        linesContainer.insertAdjacentHTML('beforeend', html);
        bindRemoveButtons();
    });

    bindRemoveButtons();
});
