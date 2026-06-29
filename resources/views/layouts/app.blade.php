<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'ERP Mini') - {{ config('app.name', 'Enterprise Workflow ERP Mini') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --erp-sidebar-bg: linear-gradient(180deg, #0f172a 0%, #172554 100%);
            --erp-sidebar-text: #cbd5e1;
            --erp-sidebar-active: rgba(255, 255, 255, 0.14);
            --erp-surface: #ffffff;
            --erp-text: #0f172a;
            --erp-text-muted: #64748b;
            --erp-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
            --erp-radius: 18px;
        }

        body {
            min-height: 100vh;
            color: var(--erp-text);
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.08), transparent 28%),
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 24%),
                linear-gradient(180deg, #eef4ff 0%, #f8fafc 48%, #edf2f7 100%);
        }

        .erp-shell {
            min-height: 100vh;
        }

        .erp-sidebar {
            min-height: 100vh;
            background: var(--erp-sidebar-bg);
            color: var(--erp-sidebar-text);
            box-shadow: 18px 0 40px rgba(15, 23, 42, 0.14);
        }

        .erp-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            text-decoration: none;
        }

        .erp-brand-badge {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.14);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18);
        }

        .erp-user-card {
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(8px);
        }

        .erp-sidebar-section {
            margin-top: 1.5rem;
            margin-bottom: 0.65rem;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: rgba(226, 232, 240, 0.62);
            text-transform: uppercase;
        }

        .erp-sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            color: var(--erp-sidebar-text);
            text-decoration: none;
            transition: all 0.18s ease;
        }

        .erp-sidebar-link:hover,
        .erp-sidebar-link.active {
            color: #fff;
            background: var(--erp-sidebar-active);
            transform: translateX(2px);
        }

        .erp-main {
            min-width: 0;
        }

        .erp-navbar {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: calc(var(--erp-radius) + 2px);
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(16px);
            box-shadow: var(--erp-shadow);
        }

        .content-card {
            border: 1px solid rgba(148, 163, 184, 0.14);
            border-radius: var(--erp-radius);
            background: var(--erp-surface);
            box-shadow: var(--erp-shadow);
        }

        .erp-page-title {
            margin: 0;
            font-size: clamp(1.5rem, 2vw, 2rem);
            font-weight: 700;
        }

        .erp-page-subtitle {
            color: var(--erp-text-muted);
        }

        .erp-breadcrumb {
            margin-bottom: 0;
        }

        .erp-breadcrumb .breadcrumb-item,
        .erp-breadcrumb .breadcrumb-item a {
            color: var(--erp-text-muted);
            text-decoration: none;
        }

        .erp-auth-wrapper {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 1.25rem;
        }

        .erp-auth-card {
            width: min(1440px, 100%);
        }

        .table > :not(caption) > * > * {
            padding-top: 0.9rem;
            padding-bottom: 0.9rem;
        }

        .erp-section-title {
            margin-bottom: 0.35rem;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .erp-section-subtitle {
            color: var(--erp-text-muted);
        }

        .erp-form-card {
            max-width: 900px;
        }

        .erp-required::after {
            content: " *";
            color: #dc3545;
        }

        .erp-form-hint {
            margin-top: 0.35rem;
            font-size: 0.875rem;
            color: var(--erp-text-muted);
        }

        @media (max-width: 991.98px) {
            .erp-sidebar {
                min-height: auto;
            }
        }
    </style>
</head>
<body>
@auth
    <div class="container-fluid erp-shell">
        <div class="row flex-nowrap">
            @include('partials.sidebar')
            <main class="col-lg-10 ms-lg-auto erp-main px-3 px-lg-4 py-3 py-lg-4">
                @include('partials.navbar')
                <section class="mt-4">
                    @include('partials.alerts')
                    @include('partials.breadcrumb')
                    @yield('content')
                </section>
            </main>
        </div>
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
@stack('scripts')
</body>
</html>
