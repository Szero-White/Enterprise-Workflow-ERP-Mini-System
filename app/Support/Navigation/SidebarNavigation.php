<?php

namespace App\Support\Navigation;

use App\Models\User;

class SidebarNavigation
{
    /**
     * Build role-aware navigation for the authenticated workspace.
     *
     * @return array<int, array{title:string, items:array<int, array{label:string, route:string, active:array<int,string>, icon:string}>}>
     */
    public function for(User $user): array
    {
        $groups = [
            $this->group(__('menu.overview'), [
                $this->item(__('menu.dashboard'), 'dashboard', ['dashboard'], 'bi-grid-1x2-fill'),
                $this->item(__('menu.notifications'), 'notifications.index', ['notifications.*'], 'bi-bell-fill'),
            ]),
        ];

        if ($user->hasRole(['admin', 'manager'])) {
            $groups[] = $this->group(__('menu.business'), [
                $this->item(__('menu.sales_orders'), 'sales.orders.index', ['sales.orders.*'], 'bi-receipt-cutoff'),
                $this->item(__('menu.customers'), 'crm.customers.index', ['crm.customers.*'], 'bi-person-vcard-fill'),
            ]);

            $groups[] = $this->group(__('menu.catalog_inventory'), [
                $this->item(__('menu.products'), 'catalog.products.index', ['catalog.products.*'], 'bi-box-seam-fill'),
                $this->item(__('menu.product_categories'), 'catalog.categories.index', ['catalog.categories.*'], 'bi-tags-fill'),
                $this->item(__('menu.inventory_stocks'), 'inventory.stocks.index', ['inventory.stocks.*', 'inventory.receipts.*'], 'bi-boxes'),
                $this->item(__('menu.warehouses'), 'inventory.warehouses.index', ['inventory.warehouses.*'], 'bi-buildings-fill'),
            ]);
        }

        if ($user->hasRole('admin')) {
            $groups[] = $this->group(__('menu.system_admin'), [
                $this->item(__('menu.users'), 'admin.users.index', ['admin.users.*'], 'bi-people-fill'),
                $this->item(__('menu.roles'), 'admin.roles.index', ['admin.roles.*'], 'bi-shield-lock-fill'),
                $this->item(__('menu.departments'), 'admin.departments.index', ['admin.departments.*'], 'bi-diagram-3-fill'),
                $this->item(__('menu.dynamic_forms'), 'admin.form-templates.index', ['admin.form-templates.*'], 'bi-ui-checks-grid'),
                $this->item(__('menu.workflow_templates'), 'admin.workflow-templates.index', ['admin.workflow-templates.*'], 'bi-bezier2'),
                $this->item(__('menu.audit_logs'), 'admin.audit-logs.index', ['admin.audit-logs.*'], 'bi-clock-history'),
            ]);
        }

        if ($user->hasRole(['employee', 'admin'])) {
            $groups[] = $this->group(__('menu.internal_requests'), [
                $this->item(__('menu.create_request'), 'employee.requests.select-template', [
                    'employee.requests.select-template',
                    'employee.requests.create',
                    'employee.requests.store',
                ], 'bi-file-earmark-plus-fill'),
                $this->item(__('menu.my_requests'), 'employee.requests.index', [
                    'employee.requests.index',
                    'employee.requests.show',
                    'employee.requests.edit',
                    'employee.requests.update',
                ], 'bi-folder2-open'),
            ]);
        }

        if ($user->hasRole(['manager', 'hr', 'director', 'admin'])) {
            $groups[] = $this->group(__('menu.approval'), [
                $this->item(__('menu.pending_approvals'), 'manager.approvals.index', [
                    'manager.approvals.index',
                    'manager.approvals.show',
                    'manager.approvals.approve',
                    'manager.approvals.reject',
                    'manager.approvals.return',
                ], 'bi-hourglass-split'),
                $this->item(__('menu.approval_history'), 'manager.approvals.history', ['manager.approvals.history'], 'bi-list-check'),
            ]);
        }

        return $groups;
    }

    /** @param array<int, array{label:string, route:string, active:array<int,string>, icon:string}> $items */
    private function group(string $title, array $items): array
    {
        return compact('title', 'items');
    }

    /** @param array<int, string> $active */
    private function item(string $label, string $route, array $active, string $icon): array
    {
        return compact('label', 'route', 'active', 'icon');
    }
}
