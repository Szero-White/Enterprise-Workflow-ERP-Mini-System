(() => {
    const typeSelect = document.querySelector('[data-approver-type]');
    if (!typeSelect) return;

    const fields = [...document.querySelectorAll('[data-approver-field]')];

    const sync = () => {
        fields.forEach((field) => {
            const active = field.dataset.approverField === typeSelect.value;
            field.hidden = !active;

            field.querySelectorAll('select, input').forEach((control) => {
                control.disabled = !active;
            });
        });
    };

    typeSelect.addEventListener('change', sync);
    sync();
})();
