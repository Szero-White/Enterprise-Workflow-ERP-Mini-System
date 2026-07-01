<div class="erp-navbar px-3 px-lg-4 py-3">
    @php($unreadNotifications = auth()->user()->systemNotifications()->unread()->count())
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <p class="erp-page-subtitle mb-1">@yield('page_eyebrow', __('ui.system_eyebrow'))</p>
            <h1 class="erp-page-title">@yield('page_title', 'Enterprise Workflow ERP Mini')</h1>
        </div>

        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('notifications.index') }}" class="btn btn-light border rounded-circle position-relative d-inline-flex align-items-center justify-content-center" style="width: 46px; height: 46px;" aria-label="{{ __('menu.notifications') }}">
                <i class="bi bi-bell"></i>
                @if($unreadNotifications > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">
                        {{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}
                    </span>
                @endif
            </a>
            <div class="text-end">
                <div class="fw-semibold">{{ auth()->user()->name }}</div>
                <div class="small text-muted">{{ auth()->user()->department?->name ?? __('ui.no_department') }}</div>
            </div>
            <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center fw-bold" style="width: 46px; height: 46px;">
                {{ \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->take(2)->implode('') }}
            </div>
        </div>
    </div>
</div>
