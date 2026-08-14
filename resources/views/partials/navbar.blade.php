<div class="erp-navbar d-flex align-items-center justify-content-between gap-3">
    @php($unreadNotifications = auth()->user()->systemNotifications()->unread()->count())
    <div class="min-w-0">
        <p class="erp-page-subtitle">@yield('page_eyebrow', 'Enterprise Commerce ERP')</p>
        <h1 class="erp-page-title text-truncate">@yield('page_title', 'Tổng quan')</h1>
    </div>

    <div class="d-flex align-items-center gap-2 gap-md-3">
        <button type="button" class="btn btn-light border erp-icon-button" data-theme-toggle title="Đổi giao diện sáng/tối">
            <i class="bi bi-circle-half"></i>
        </button>
        <a href="{{ route('notifications.index') }}" class="btn btn-light border erp-icon-button position-relative" aria-label="Thông báo">
            <i class="bi bi-bell"></i>
            @if($unreadNotifications > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">
                    {{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}
                </span>
            @endif
        </a>
        <div class="d-none d-md-block text-end">
            <div class="fw-semibold small">{{ auth()->user()->name }}</div>
            <div class="small text-muted">{{ auth()->user()->department?->name ?? 'Không thuộc phòng ban' }}</div>
        </div>
        <div class="erp-avatar">
            {{ \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->filter()->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->take(2)->implode('') }}
        </div>
    </div>
</div>
