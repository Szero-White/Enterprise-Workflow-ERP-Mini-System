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

    if ($user->hasRole('admin')) {
        $menuGroups[] = [
            'title' => __('menu.admin'),
            'items' => [
                ['label' => __('menu.users'), 'route' => 'admin.users.index', 'active' => ['admin.users.*'], 'icon' => 'bi-people-fill'],
                ['label' => __('menu.roles'), 'route' => 'admin.roles.index', 'active' => ['admin.roles.*'], 'icon' => 'bi-shield-lock-fill'],
                ['label' => __('menu.departments'), 'route' => 'admin.departments.index', 'active' => ['admin.departments.*'], 'icon' => 'bi-diagram-3-fill'],
                ['label' => __('menu.form_templates'), 'route' => 'admin.form-templates.index', 'active' => ['admin.form-templates.*'], 'icon' => 'bi-ui-checks-grid'],
                ['label' => __('menu.workflow_templates'), 'route' => 'admin.workflow-templates.index', 'active' => ['admin.workflow-templates.*'], 'icon' => 'bi-bezier2'],
                ['label' => __('menu.audit_logs'), 'route' => 'admin.audit-logs.index', 'active' => ['admin.audit-logs.*'], 'icon' => 'bi-clock-history'],
            ],
        ];
    }

    if ($user->hasRole(['employee', 'admin'])) {
        $menuGroups[] = [
            'title' => __('menu.employee'),
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
                ['label' => __('menu.pending_approvals'), 'route' => 'manager.approvals.index', 'active' => ['manager.approvals.index'], 'icon' => 'bi-hourglass-split'],
                ['label' => __('menu.approval_history'), 'route' => 'manager.approvals.index', 'active' => ['manager.approvals.show', 'manager.approvals.approve', 'manager.approvals.reject', 'manager.approvals.return'], 'icon' => 'bi-list-check'],
            ],
        ];
    }
@endphp

<aside class="col-lg-2 px-0 erp-sidebar">
    <div class="p-3 p-lg-4">
        <a href="{{ route('dashboard') }}" class="erp-brand mb-4">
            <span class="erp-brand-badge">EW</span>
            <span>
                <span class="d-block fw-semibold">ERP Mini</span>
                <span class="small text-white-50">Enterprise Workflow</span>
            </span>
        </a>

        <div class="erp-user-card p-3 mb-4">
            <div class="fw-semibold text-white">{{ $user->name }}</div>
            <div class="small text-white-50">{{ $user->email }}</div>
            <div class="small mt-2">
                <span class="badge text-bg-light">
                    {{ $user->role ? (trans()->has('ui.roles.'.$user->role->key) ? __('ui.roles.'.$user->role->key) : $user->role->name) : __('ui.no_role') }}
                </span>
            </div>
        </div>

        @foreach ($menuGroups as $group)
            <div class="erp-sidebar-section">{{ $group['title'] }}</div>
            <nav class="d-grid gap-1">
                @foreach ($group['items'] as $item)
                    <a href="{{ route($item['route']) }}" class="erp-sidebar-link {{ request()->routeIs(...$item['active']) ? 'active' : '' }}">
                        <i class="bi {{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        @endforeach

        <form action="{{ route('logout') }}" method="POST" class="mt-4 pt-2">
            @csrf
            <button class="btn btn-outline-light w-100 rounded-4">
                <i class="bi bi-box-arrow-right me-2"></i>{{ __('menu.logout') }}
            </button>
        </form>
    </div>
</aside>
