(() => {
    const root = document.documentElement;
    const body = document.body;

    const setTheme = (theme) => {
        root.dataset.theme = theme;
        localStorage.setItem('erp-theme', theme);
        window.dispatchEvent(new CustomEvent('erp:theme-changed', { detail: { theme } }));
    };

    document.addEventListener('click', (event) => {
        const themeToggle = event.target.closest('[data-theme-toggle]');
        if (themeToggle) {
            setTheme(root.dataset.theme === 'dark' ? 'light' : 'dark');
            return;
        }

        if (event.target.closest('[data-sidebar-open]')) {
            body.classList.add('erp-sidebar-open');
            return;
        }

        if (event.target.closest('[data-sidebar-close]')) {
            body.classList.remove('erp-sidebar-open');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            body.classList.remove('erp-sidebar-open');
        }
    });

    window.matchMedia('(min-width: 992px)').addEventListener('change', (event) => {
        if (event.matches) body.classList.remove('erp-sidebar-open');
    });
})();
