document.addEventListener('submit', (event) => {
    const form = event.target.closest('form');

    if (!form) {
        return;
    }

    const submitterMessage = event.submitter?.dataset.confirm?.trim();
    const formMessage = form.dataset.confirm?.trim();
    const message = submitterMessage || formMessage;

    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});
