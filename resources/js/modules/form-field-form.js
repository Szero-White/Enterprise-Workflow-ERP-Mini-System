document.addEventListener('DOMContentLoaded', () => {
    const fieldType = document.querySelector('[data-form-field-type]');
    const optionsBox = document.querySelector('[data-form-field-options]');
    const conditionEnabled = document.querySelector('[data-condition-enabled]');
    const conditionConfig = document.querySelector('[data-condition-config]');
    const conditionOperator = document.querySelector('[data-condition-operator]');
    const conditionValueBox = document.querySelector('[data-condition-value-box]');

    if (fieldType && optionsBox) {
        const syncOptionsVisibility = () => {
            optionsBox.hidden = fieldType.value !== 'select';
        };

        fieldType.addEventListener('change', syncOptionsVisibility);
        syncOptionsVisibility();
    }

    if (!conditionEnabled || !conditionConfig) {
        return;
    }

    const syncConditionConfig = () => {
        const enabled = conditionEnabled.checked;
        conditionConfig.hidden = !enabled;

        conditionConfig.querySelectorAll('select, input').forEach((control) => {
            control.disabled = !enabled;
        });
    };

    const syncConditionValue = () => {
        if (!conditionOperator || !conditionValueBox) {
            return;
        }

        const needsValue = ['equals', 'not_equals'].includes(conditionOperator.value);
        conditionValueBox.hidden = !needsValue;

        conditionValueBox.querySelectorAll('input').forEach((control) => {
            control.disabled = !conditionEnabled.checked || !needsValue;
        });
    };

    conditionEnabled.addEventListener('change', () => {
        syncConditionConfig();
        syncConditionValue();
    });
    conditionOperator?.addEventListener('change', syncConditionValue);

    syncConditionConfig();
    syncConditionValue();
});
