@php
    $user = auth()->user();
    $menuGroups = [
        [
            'title' => 'Tong quan',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => ['dashboard'], 'icon' => 'bi-grid-1x2-fill'],
            ],
        ],
    ];

    if ($user->hasRole('admin')) {
        $menuGroups[] = [
            'title' => 'Admin',
            'items' => [
                ['label' => 'Nguoi dung', 'route' => 'admin.users.index', 'active' => ['admin.users.*'], 'icon' => 'bi-people-fill'],
                ['label' => 'Vai tro', 'route' => 'admin.roles.index', 'active' => ['admin.roles.*'], 'icon' => 'bi-shield-lock-fill'],
                ['label' => 'Phong ban', 'route' => 'admin.departments.index', 'active' => ['admin.departments.*'], 'icon' => 'bi-diagram-3-fill'],
                ['label' => 'Bieu mau', 'route' => 'admin.form-templates.index', 'active' => ['admin.form-templates.*'], 'icon' => 'bi-ui-checks-grid'],
                ['label' => 'Workflow', 'route' => 'admin.workflow-templates.index', 'active' => ['admin.workflow-templates.*'], 'icon' => 'bi-bezier2'],
                ['label' => 'Audit Logs', 'route' => 'admin.audit-logs.index', 'active' => ['admin.audit-logs.*'], 'icon' => 'bi-clock-history'],
            ],
        ];
    }

    if ($user->hasRole(['employee', 'admin'])) {
        $menuGroups[] = [
            'title' => 'Nhan vien',
            'items' => [
                ['label' => 'Tao don', 'route' => 'employee.requests.select-template', 'active' => ['employee.requests.select-template', 'employee.requests.create', 'employee.requests.store'], 'icon' => 'bi-file-earmark-plus-fill'],
                ['label' => 'Don cua toi', 'route' => 'employee.requests.index', 'active' => ['employee.requests.index', 'employee.requests.show', 'employee.requests.edit', 'employee.requests.update'], 'icon' => 'bi-folder2-open'],
            ],
        ];
    }

    if ($user->hasRole(['manager', 'hr', 'director', 'admin'])) {
        $menuGroups[] = [
            'title' => 'Phe duyet',
            'items' => [
                ['label' => 'Cho duyet', 'route' => 'manager.approvals.index', 'active' => ['manager.approvals.index'], 'icon' => 'bi-hourglass-split'],
                ['label' => 'Lich su duyet', 'route' => 'manager.approvals.index', 'active' => ['manager.approvals.show', 'manager.approvals.approve', 'manager.approvals.reject', 'manager.approvals.return'], 'icon' => 'bi-list-check'],
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
                <span class="badge text-bg-light">{{ $user->role?->name ?? 'No role' }}</span>
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
                <i class="bi bi-box-arrow-right me-2"></i>Dang xuat
            </button>
        </form>
    </div>
</aside>
