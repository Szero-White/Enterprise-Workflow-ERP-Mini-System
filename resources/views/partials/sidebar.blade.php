@php
    $user = auth()->user();
    $menuGroups = app(\App\Support\Navigation\SidebarNavigation::class)->for($user);
@endphp

<aside class="erp-sidebar" id="erp-sidebar" aria-label="{{ __('layout.main_navigation') }}">
    <div class="erp-sidebar__header">
        <a href="{{ route('dashboard') }}" class="erp-brand" aria-label="Enterprise Workflow & Operations">
            <span class="erp-brand__mark"><i class="bi bi-layers-fill"></i></span>
            <span class="erp-brand__copy">
                <span class="erp-brand__name">{{ __('layout.brand_name') }}</span>
                <span class="erp-brand__meta">{{ __('layout.brand_meta') }}</span>
            </span>
        </a>
        <button type="button" class="erp-sidebar__close d-lg-none" data-sidebar-close aria-label="{{ __('layout.close_menu') }}">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="erp-workspace-card">
        <div class="erp-workspace-card__icon"><i class="bi bi-grid-1x2"></i></div>
        <div class="min-w-0 flex-grow-1">
            <div class="erp-workspace-card__name text-truncate">{{ __('layout.workspace_name') }}</div>
            <div class="erp-workspace-card__meta text-truncate">{{ __('layout.system_ready') }}</div>
        </div>
        <span class="erp-live-dot" aria-hidden="true"></span>
    </div>

    <div class="erp-sidebar__scroll">
        @foreach ($menuGroups as $group)
            <section class="erp-nav-group">
                <div class="erp-nav-group__label">{{ $group['title'] }}</div>
                <nav class="erp-nav-list">
                    @foreach ($group['items'] as $item)
                        @php($isActive = request()->routeIs(...$item['active']))
                        <a
                            href="{{ route($item['route']) }}"
                            class="erp-nav-link {{ $isActive ? 'is-active' : '' }}"
                            @if($isActive) aria-current="page" @endif
                        >
                            <span class="erp-nav-link__icon"><i class="bi {{ $item['icon'] }}"></i></span>
                            <span class="erp-nav-link__label">{{ $item['label'] }}</span>
                            @if($isActive)<span class="erp-nav-link__active-dot"></span>@endif
                        </a>
                    @endforeach
                </nav>
            </section>
        @endforeach
    </div>

    <div class="erp-sidebar__footer">
        <div class="erp-sidebar-user">
            <div class="erp-avatar erp-avatar--sidebar">
                {{ \Illuminate\Support\Str::of($user->name)->explode(' ')->filter()->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->take(2)->implode('') }}
            </div>
            <div class="min-w-0 flex-grow-1">
                <div class="erp-sidebar-user__name text-truncate">{{ $user->name }}</div>
                <div class="erp-sidebar-user__role text-truncate">{{ $user->role?->name ?? __('ui.no_role') }}</div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="erp-sidebar-user__logout" type="submit" title="{{ __('menu.logout') }}" aria-label="{{ __('menu.logout') }}">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
<div class="erp-sidebar-backdrop" data-sidebar-close></div>
