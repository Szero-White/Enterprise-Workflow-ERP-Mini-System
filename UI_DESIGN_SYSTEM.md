# ERP UI Design System

The interface follows a lightweight product-UI system for an internal commerce ERP. The goal is to feel like a modern SaaS workspace rather than a dense admin template while preserving the information density required for operations.

## Product principles

- Use whitespace and hierarchy before adding borders, shadows or heavy typography.
- Keep headings short and use medium/semibold weights; avoid oversized marketing copy inside operational screens.
- Reserve the primary color for actions, active navigation and important signals.
- Prefer light neutral surfaces and soft status colors. Dark mode remains available but is not the default visual language.
- Tables are optimized for scanning: normal-case headers, restrained metadata, clear primary fields and compact actions.
- Forms keep labels readable, inputs calm and action panels predictable.
- Login is a product entry point, not a marketing landing page: one short value proposition, a subtle workspace preview and a focused form.

## Main UI layers

### Design tokens

`public/css/erp.css` is the entrypoint. Styles are split by responsibility under `public/css/erp/`:

- `tokens.css`: color, spacing, radius, shadow and typography tokens.
- `shell.css`: sidebar, topbar, workspace shell and profile navigation.
- `components.css`: page headers, buttons, panels, tables, forms, badges and shared UI.
- `pages.css`: dashboard, login and sales-order composer patterns.
- `theme.css`: dark-theme adjustments only.
- `responsive.css`: responsive behavior and mobile navigation.

### Shared Blade components

- `resources/views/components/erp/page-header.blade.php`
- `resources/views/components/erp/panel.blade.php`
- `resources/views/components/erp/metric-card.blade.php`
- `resources/views/components/erp/empty-state.blade.php`
- `resources/views/components/erp/form-shell.blade.php`

Use these before creating page-specific copies of the same patterns.

### Navigation

`App\Support\Navigation\SidebarNavigation` builds role-aware navigation. Blade only renders the structure; role and route decisions stay outside the view.

### Frontend behavior

- `public/js/erp-shell.js`: theme switching and responsive sidebar.
- `public/js/dashboard.js`: dashboard revenue chart.
- `public/js/sales-order-form.js`: sales-order line editor and presentation totals.

Business calculations remain authoritative on the backend.

## Clean-code rules for UI work

- Do not query business data from Blade views.
- Do not duplicate role-based navigation arrays in views.
- Avoid inline styles; extend the design system instead.
- Avoid long inline JavaScript in Blade; pass server data through JSON and keep interaction code in dedicated JS files.
- Do not trust client-side prices or totals.
- Add translation keys instead of hard-coding new product copy.
- Prefer composition of existing components over one-off markup.
- Keep page-specific CSS in `pages.css`; reusable rules belong in `components.css`.

## Storefront boundary

The authenticated ERP is an internal operations product. A future customer-facing storefront should use a separate presentation boundary while reusing the same catalog/order domain services. Marketing and checkout concerns should not leak into ERP administration screens.
