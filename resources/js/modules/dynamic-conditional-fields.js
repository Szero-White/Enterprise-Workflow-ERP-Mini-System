const conditionMatches = (container, form) => {
    const sourceKey = container.dataset.conditionFieldKey;
    const operator = container.dataset.conditionOperator;

    if (!sourceKey || !operator) {
        return true;
    }

    const sourceContainer = form.querySelector(`[data-dynamic-field][data-field-key="${sourceKey}"]`);
    if (sourceContainer?.hidden) {
        return false;
    }

    const source = form.elements.namedItem(sourceKey);
    const sourceValue = source && !source.disabled ? String(source.value ?? '') : '';
    const expected = String(container.dataset.conditionValue ?? '');

    switch (operator) {
        case 'equals':
            return sourceValue === expected;
        case 'not_equals':
            return sourceValue !== expected;
        case 'filled':
            return sourceValue.trim() !== '';
        case 'empty':
            return sourceValue.trim() === '';
        default:
            return false;
    }
};

const initializeConditionalFields = (form) => {
    const fields = [...form.querySelectorAll('[data-dynamic-field]')];

    if (!fields.some((field) => field.dataset.conditionFieldKey)) {
        return;
    }

    const sync = () => {
        // Multiple passes make chained dependencies deterministic when an upstream
        // field becomes hidden and therefore disables a downstream condition source.
        for (let pass = 0; pass < fields.length; pass += 1) {
            fields.forEach((container) => {
                const visible = conditionMatches(container, form);
                container.hidden = !visible;

                container.querySelectorAll('input, select, textarea').forEach((control) => {
                    control.disabled = !visible;
                    control.required = visible && container.dataset.required === '1';
                });
            });
        }
    };

    form.addEventListener('change', sync);
    form.addEventListener('input', sync);
    sync();
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-dynamic-form]').forEach(initializeConditionalFields);
});
