document.addEventListener('DOMContentLoaded', () => {
    const fieldType = document.querySelector('[data-form-field-type]');
    const optionsBox = document.querySelector('[data-form-field-options]');

    if (!fieldType || !optionsBox) {
        return;
    }

    const syncOptionsVisibility = () => {
        optionsBox.hidden = fieldType.value !== 'select';
    };

    fieldType.addEventListener('change', syncOptionsVisibility);
    syncOptionsVisibility();
});
