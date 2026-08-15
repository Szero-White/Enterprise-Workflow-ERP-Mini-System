const demoLogin = document.querySelector('[data-demo-login]');

if (demoLogin) {
    const emailInput = document.querySelector('#email');
    const passwordInput = document.querySelector('#password');
    const selectedRole = demoLogin.querySelector('[data-demo-selected-role]');
    const accountButtons = demoLogin.querySelectorAll('[data-demo-account]');

    accountButtons.forEach((button) => {
        button.addEventListener('click', () => {
            if (emailInput) {
                emailInput.value = button.dataset.demoEmail || '';
            }

            if (passwordInput) {
                passwordInput.value = button.dataset.demoPassword || '';
            }

            if (selectedRole) {
                selectedRole.textContent = button.dataset.demoRole || '';
            }

            accountButtons.forEach((accountButton) => accountButton.classList.remove('is-active'));
            button.classList.add('is-active');
            button.closest('details')?.removeAttribute('open');
            emailInput?.focus();
        });
    });
}
