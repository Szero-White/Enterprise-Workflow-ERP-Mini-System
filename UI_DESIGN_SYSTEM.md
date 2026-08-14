# ERP UI Design System

This project uses a small application-specific design system instead of spreading page-specific inline styles across Blade templates.

## Goals

- Keep the ERP dense enough for operational work without looking like a default admin template.
- Separate visual concerns from business logic.
- Reuse page headers, panels, metric cards, form shells, empty states, navigation and interaction scripts.
- Keep the implementation compatible with Laravel Blade + Bootstrap 5; no heavy frontend framework is required.
- Support desktop, tablet, mobile sidebar behavior and light/dark themes.

## Main UI layers

### Design tokens

`public/css/erp.css` is the small entrypoint. Styles are split by responsibility under `public/css/erp/`: `tokens.css`, `shell.css`, `components.css`, `pages.css`, `theme.css`, and `responsive.css`. Design tokens use `--erp-*` CSS custom properties.

### Shared Blade components

- `resources/views/components/erp/page-header.blade.php`
- `resources/views/components/erp/panel.blade.php`
- `resources/views/components/erp/metric-card.blade.php`
- `resources/views/components/erp/empty-state.blade.php`
- `resources/views/components/erp/form-shell.blade.php`

Use these before creating another page-specific card/header implementation.

### Navigation

`App\Support\Navigation\SidebarNavigation` builds role-aware navigation. The sidebar Blade template is responsible only for rendering it.

### Frontend behavior

- `public/js/erp-shell.js`: theme switching and responsive sidebar.
- `public/js/dashboard.js`: dashboard revenue chart.
- `public/js/sales-order-form.js`: sales-order line editor and totals.

Business calculations remain authoritative on the backend. Frontend calculations are presentation only.

## Visual hierarchy

1. Topbar identifies the current context and exposes global actions.
2. Page header explains the task and contains primary actions.
3. Metric cards expose high-signal information only.
4. Panels group one responsibility per surface.
5. Tables prioritize scanning: strong primary field, muted secondary metadata, compact actions.
6. Forms use a content area plus a sticky action summary on wide screens.

## Clean-code rules for UI work

- Do not add business queries to Blade views.
- Do not duplicate role-based navigation arrays in views.
- Avoid inline styles; extend the design system instead.
- Avoid long inline JavaScript in Blade; pass server data through JSON and keep interaction logic in dedicated JS files.
- Do not trust client-side prices or totals. The server remains the source of truth.
- Add translation keys instead of hard-coding new product UI copy.
- New screens should first attempt to compose existing components before introducing a new component.

## Storefront boundary

The authenticated ERP is an internal operations product. A future customer-facing storefront should be implemented as a separate presentation boundary (public routes/controllers/views or a dedicated frontend) while reusing the same catalog/order domain services where appropriate. This prevents marketing UI concerns from leaking into ERP administration screens.
