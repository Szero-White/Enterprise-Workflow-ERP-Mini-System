<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'ERP Commerce') - {{ config('app.name', 'Enterprise Commerce ERP') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/erp.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
@auth
    <div class="erp-shell">
        @include('partials.sidebar')
        <main class="erp-main">
            @include('partials.navbar')
            <section class="erp-content">
                @include('partials.alerts')
                @include('partials.breadcrumb')
                @yield('content')
            </section>
        </main>
    </div>
@else
    <div class="erp-auth-wrapper">
        <div class="erp-auth-card">
            @include('partials.alerts')
            @yield('content')
        </div>
    </div>
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (() => {
        const root = document.documentElement;
        const savedTheme = localStorage.getItem('erp-theme');
        if (savedTheme) root.dataset.theme = savedTheme;

        document.addEventListener('click', (event) => {
            const toggle = event.target.closest('[data-theme-toggle]');
            if (!toggle) return;
            const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
            root.dataset.theme = next;
            localStorage.setItem('erp-theme', next);
        });
    })();
</script>
@stack('scripts')
</body>
</html>
