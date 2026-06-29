<div class="erp-navbar px-3 px-lg-4 py-3">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <p class="erp-page-subtitle mb-1">@yield('page_eyebrow', 'He thong quan tri noi bo')</p>
            <h1 class="erp-page-title">@yield('page_title', 'Enterprise Workflow ERP Mini')</h1>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="text-end">
                <div class="fw-semibold">{{ auth()->user()->name }}</div>
                <div class="small text-muted">{{ auth()->user()->department?->name ?? 'Chua co phong ban' }}</div>
            </div>
            <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center fw-bold" style="width: 46px; height: 46px;">
                {{ \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->take(2)->implode('') }}
            </div>
        </div>
    </div>
</div>
