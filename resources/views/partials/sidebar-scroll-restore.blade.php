<script>
(() => {
    const storageKey = 'erp.sidebar.scrollTop';
    const sidebar = document.querySelector('.erp-sidebar__scroll');

    if (! sidebar) {
        return;
    }

    let storedPosition = null;

    try {
        const value = sessionStorage.getItem(storageKey);

        if (value !== null) {
            const position = Number.parseInt(value, 10);

            if (Number.isFinite(position) && position >= 0) {
                storedPosition = position;
            }
        }
    } catch {
        // Ignore storage errors in restrictive/private browser contexts.
    }

    if (storedPosition !== null) {
        sidebar.scrollTop = storedPosition;

        return;
    }

    const activeLink = sidebar.querySelector('.erp-nav-link.is-active');

    if (activeLink) {
        activeLink.scrollIntoView({
            block: 'nearest',
            inline: 'nearest',
        });
    }
})();
</script>
