const STORAGE_KEY = 'erp.sidebar.scrollTop';

const getSidebarScroll = () => document.querySelector('.erp-sidebar__scroll');

const storePosition = (sidebar) => {
    try {
        sessionStorage.setItem(STORAGE_KEY, String(sidebar.scrollTop));
    } catch {
        // Ignore storage errors in restrictive/private browser contexts.
    }
};

const initializeSidebarScrollState = () => {
    const sidebar = getSidebarScroll();

    if (! sidebar) {
        try {
            sessionStorage.removeItem(STORAGE_KEY);
        } catch {
            // Ignore storage errors.
        }

        return;
    }

    let frameRequested = false;

    sidebar.addEventListener('scroll', () => {
        if (frameRequested) {
            return;
        }

        frameRequested = true;

        requestAnimationFrame(() => {
            storePosition(sidebar);
            frameRequested = false;
        });
    }, { passive: true });

    sidebar.addEventListener('click', (event) => {
        if (event.target.closest('a')) {
            storePosition(sidebar);
        }
    });

    window.addEventListener('pagehide', () => {
        storePosition(sidebar);
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeSidebarScrollState, { once: true });
} else {
    initializeSidebarScrollState();
}
