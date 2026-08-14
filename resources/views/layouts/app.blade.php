<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f4f7fb">
    <title>@yield('page_title', 'Workflow Operations') - {{ config('app.name', 'Enterprise Workflow & Operations') }}</title>

    <script>
        (() => {
            const savedTheme = localStorage.getItem('erp-theme');
            document.documentElement.dataset.theme = savedTheme || 'light';
        })();
    </script>

    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/erp.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="@auth erp-app-body @else erp-auth-body @endauth">
@auth
    <div class="erp-shell">
        @include('partials.sidebar')
        <div class="erp-main">
            @include('partials.navbar')
            <main class="erp-content" id="main-content">
                <div class="erp-content__inner">
                    @include('partials.alerts')
                    @include('partials.breadcrumb')
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
@else
    <main class="erp-auth-wrapper">
        <div class="erp-auth-card">
            @include('partials.alerts')
            @yield('content')
        </div>
    </main>
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/erp-shell.js') }}"></script>
@stack('scripts')
</body>
</html>
