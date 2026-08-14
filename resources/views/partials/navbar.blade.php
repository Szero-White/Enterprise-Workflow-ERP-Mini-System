@php
    $user = auth()->user();
    $unreadNotifications = $user->systemNotifications()->unread()->count();
@endphp

<header class="erp-topbar">
    <div class="erp-topbar__context min-w-0">
        <button type="button" class="erp-icon-btn d-lg-none" data-sidebar-open aria-label="{{ __('layout.open_menu') }}">
            <i class="bi bi-list"></i>
        </button>
        <div class="min-w-0">
            <div class="erp-topbar__eyebrow text-truncate">@yield('page_eyebrow', 'Enterprise Commerce ERP')</div>
            <div class="erp-topbar__title text-truncate">@yield('page_title', __('ui.dashboard'))</div>
        </div>
    </div>

    <div class="erp-topbar__actions">
        @if($user->hasRole(['admin', 'manager']))
            <div class="dropdown d-none d-sm-block">
                <button class="btn btn-primary erp-create-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ __('layout.quick_create') }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end erp-quick-menu">
                    <div class="erp-quick-menu__heading">{{ __('layout.common_actions') }}</div>
                    <a class="dropdown-item erp-quick-menu__item" href="{{ route('sales.orders.create') }}">
                        <span class="erp-quick-menu__icon bg-primary-subtle text-primary"><i class="bi bi-receipt-cutoff"></i></span>
                        <span><strong>{{ __('layout.create_sales_order') }}</strong><small>{{ __('layout.create_sales_order_hint') }}</small></span>
                    </a>
                    <a class="dropdown-item erp-quick-menu__item" href="{{ route('inventory.receipts.create') }}">
                        <span class="erp-quick-menu__icon bg-success-subtle text-success"><i class="bi bi-box-arrow-in-down"></i></span>
                        <span><strong>{{ __('layout.receive_stock') }}</strong><small>{{ __('layout.receive_stock_hint') }}</small></span>
                    </a>
                    <a class="dropdown-item erp-quick-menu__item" href="{{ route('catalog.products.create') }}">
                        <span class="erp-quick-menu__icon bg-info-subtle text-info"><i class="bi bi-box-seam"></i></span>
                        <span><strong>{{ __('layout.create_product') }}</strong><small>{{ __('layout.create_product_hint') }}</small></span>
                    </a>
                    <a class="dropdown-item erp-quick-menu__item" href="{{ route('crm.customers.create') }}">
                        <span class="erp-quick-menu__icon bg-warning-subtle text-warning"><i class="bi bi-person-plus"></i></span>
                        <span><strong>{{ __('layout.create_customer') }}</strong><small>{{ __('layout.create_customer_hint') }}</small></span>
                    </a>
                </div>
            </div>
        @endif

        <button type="button" class="erp-icon-btn" data-theme-toggle title="{{ __('layout.toggle_theme') }}" aria-label="{{ __('layout.toggle_theme') }}">
            <i class="bi bi-sun-fill erp-theme-icon--light"></i>
            <i class="bi bi-moon-stars-fill erp-theme-icon--dark"></i>
        </button>

        <a href="{{ route('notifications.index') }}" class="erp-icon-btn position-relative" aria-label="{{ __('layout.notifications') }}">
            <i class="bi bi-bell"></i>
            @if($unreadNotifications > 0)
                <span class="erp-notification-dot">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
            @endif
        </a>

        <div class="dropdown">
            <button class="erp-profile-trigger dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="erp-avatar">
                    {{ \Illuminate\Support\Str::of($user->name)->explode(' ')->filter()->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->take(2)->implode('') }}
                </span>
                <span class="erp-profile-trigger__copy d-none d-md-block">
                    <span class="erp-profile-trigger__name text-truncate">{{ $user->name }}</span>
                    <span class="erp-profile-trigger__meta text-truncate">{{ $user->department?->name ?? __('ui.no_department') }}</span>
                </span>
                <i class="bi bi-chevron-down erp-profile-trigger__chevron d-none d-md-inline"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end erp-profile-menu">
                <div class="erp-profile-menu__header">
                    <div class="fw-semibold text-truncate">{{ $user->name }}</div>
                    <div class="small text-muted text-truncate">{{ $user->email }}</div>
                    <div class="d-flex gap-2 mt-2 flex-wrap">
                        <span class="badge rounded-pill text-bg-primary-subtle text-primary-emphasis">{{ $user->role?->name ?? __('ui.no_role') }}</span>
                        @if($user->department)
                            <span class="badge rounded-pill text-bg-light border">{{ $user->department->code }}</span>
                        @endif
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="dropdown-item text-danger d-flex align-items-center gap-2" type="submit">
                        <i class="bi bi-box-arrow-right"></i>{{ __('menu.logout') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
