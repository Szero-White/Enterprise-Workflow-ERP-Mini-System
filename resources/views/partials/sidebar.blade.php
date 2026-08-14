@php
    $user = auth()->user();
    $menuGroups = [
        [
            'title' => __('menu.overview'),
            'items' => [
                ['label' => __('menu.dashboard'), 'route' => 'dashboard', 'active' => ['dashboard'], 'icon' => 'bi-grid-1x2-fill'],
                ['label' => __('menu.notifications'), 'route' => 'notifications.index', 'active' => ['notifications.*'], 'icon' => 'bi-bell-fill'],
            ],
        ],
    ];

    if ($user->hasRole(['admin', 'manager'])) {
        $menuGroups[] = [
            'title' => __('menu.business'),
            'items' => [
                ['label' => __('menu.sales_orders'), 'route' => 'sales.orders.index', 'active' => ['sales.orders.*'], 'icon' => 'bi-receipt-cutoff'],
                ['label' => __('menu.customers'), 'route' => 'crm.customers.index', 'active' => ['crm.customers.*'], 'icon' => 'bi-person-vcard-fill'],
            ],
        ];

        $menuGroups[] = [
            'title' => __('menu.catalog_inventory'),
            'items' => [
                ['label' => __('menu.products'), 'route' => 'catalog.products.index', 'active' => ['catalog.products.*'], 'icon' => 'bi-box-seam-fill'],
                ['label' => __('menu.product_categories'), 'route' => 'catalog.categories.index', 'active' => ['catalog.categories.*'], 'icon' => 'bi-tags-fill'],
                ['label' => __('menu.inventory_stocks'), 'route' => 'inventory.stocks.index', 'active' => ['inventory.stocks.*', 'inventory.receipts.*'], 'icon' => 'bi-boxes'],
                ['label' => __('menu.warehouses'), 'route' => 'inventory.warehouses.index', 'active' => ['inventory.warehouses.*'], 'icon' => 'bi-buildings-fill'],
            ],
        ];
    }

    if ($user->hasRole('admin')) {
        $menuGroups[] = [
            'title' => __('menu.system_admin'),
            'items' => [
                ['label' => __('menu.users'), 'route' => 'admin.users.index', 'active' => ['admin.users.*'], 'icon' => 'bi-people-fill'],
                ['label' => __('menu.roles'), 'route' => 'admin.roles.index', 'active' => ['admin.roles.*'], 'icon' => 'bi-shield-lock-fill'],
                ['label' => __('menu.departments'), 'route' => 'admin.departments.index', 'active' => ['admin.departments.*'], 'icon' => 'bi-diagram-3-fill'],
                ['label' => __('menu.dynamic_forms'), 'route' => 'admin.form-templates.index', 'active' => ['admin.form-templates.*'], 'icon' => 'bi-ui-checks-grid'],
                ['label' => __('menu.workflow_templates'), 'route' => 'admin.workflow-templates.index', 'active' => ['admin.workflow-templates.*'], 'icon' => 'bi-bezier2'],
                ['label' => __('menu.audit_logs'), 'route' => 'admin.audit-logs.index', 'active' => ['admin.audit-logs.*'], 'icon' => 'bi-clock-history'],
            ],
        ];
    }

    if ($user->hasRole(['employee', 'admin'])) {
        $menuGroups[] = [
            'title' => __('menu.internal_requests'),
            'items' => [
                ['label' => __('menu.create_request'), 'route' => 'employee.requests.select-template', 'active' => ['employee.requests.select-template', 'employee.requests.create', 'employee.requests.store'], 'icon' => 'bi-file-earmark-plus-fill'],
                ['label' => __('menu.my_requests'), 'route' => 'employee.requests.index', 'active' => ['employee.requests.index', 'employee.requests.show', 'employee.requests.edit', 'employee.requests.update'], 'icon' => 'bi-folder2-open'],
            ],
        ];
    }

    if ($user->hasRole(['manager', 'hr', 'director', 'admin'])) {
        $menuGroups[] = [
            'title' => __('menu.approval'),
            'items' => [
                ['label' => __('menu.pending_approvals'), 'route' => 'manager.approvals.index', 'active' => ['manager.approvals.index', 'manager.approvals.show', 'manager.approvals.approve', 'manager.approvals.reject', 'manager.approvals.return'], 'icon' => 'bi-hourglass-split'],
                ['label' => __('menu.approval_history'), 'route' => 'manager.approvals.history', 'active' => ['manager.approvals.history'], 'icon' => 'bi-list-check'],
            ],
        ];
    }
@endphp

<aside class="erp-sidebar">
    <div class="p-3 p-xl-4">
        <a href="{{ route('dashboard') }}" class="erp-brand mb-4">
            <span class="erp-brand-badge">EC</span>
            <span>
                <span class="d-block erp-brand-title">ERP Commerce</span>
                <span class="small text-white-50">Sales · Stock · Workflow</span>
            </span>
        </a>

        <div class="erp-user-card p-3 mb-2">
            <div class="fw-semibold text-white text-truncate">{{ $user->name }}</div>
            <div class="small text-white-50 text-truncate">{{ $user->email }}</div>
            <div class="d-flex gap-2 flex-wrap mt-2">
                <span class="badge rounded-pill text-bg-light">{{ $user->role?->name ?? __('ui.no_role') }}</span>
                @if($user->department)
                    <span class="badge rounded-pill border border-secondary text-light">{{ $user->department->code }}</span>
                @endif
            </div>
        </div>

        @foreach ($menuGroups as $group)
            <div class="erp-sidebar-section">{{ $group['title'] }}</div>
            <nav>
                @foreach ($group['items'] as $item)
                    <a href="{{ route($item['route']) }}" class="erp-sidebar-link {{ request()->routeIs(...$item['active']) ? 'active' : '' }}">
                        <i class="bi {{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        @endforeach

        <form action="{{ route('logout') }}" method="POST" class="mt-4 pt-2 border-top border-secondary border-opacity-25">
            @csrf
            <button class="btn btn-outline-light w-100 mt-3">
                <i class="bi bi-box-arrow-right me-2"></i>{{ __('menu.logout') }}
            </button>
        </form>
    </div>
</aside>
