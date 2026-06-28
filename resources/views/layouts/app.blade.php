<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Enterprise Workflow ERP Mini') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f6f7fb; }
        .sidebar { min-height: 100vh; background: #172033; }
        .sidebar a { color: #d8deea; text-decoration: none; display: block; padding: 10px 14px; border-radius: 8px; }
        .sidebar a:hover, .sidebar a.active { background: #25324d; color: #fff; }
        .content-card { background: #fff; border-radius: 14px; box-shadow: 0 8px 24px rgba(15,23,42,.06); }
        .badge-status { text-transform: uppercase; }
    </style>
</head>
<body>
@auth
<div class="container-fluid">
    <div class="row">
        <aside class="col-md-3 col-lg-2 sidebar p-3">
            <h5 class="text-white mb-4">ERP Mini</h5>
            <div class="small text-white-50 mb-3">
                {{ auth()->user()->name }}<br>
                Role: {{ auth()->user()->role?->name }}
            </div>

            <a href="{{ route('dashboard') }}">Dashboard</a>

            @if(auth()->user()->hasRole('admin'))
                <div class="text-white-50 small mt-3 mb-1">Admin</div>
                <a href="{{ route('admin.users.index') }}">Users</a>
                <a href="{{ route('admin.roles.index') }}">Roles</a>
                <a href="{{ route('admin.departments.index') }}">Departments</a>
                <a href="{{ route('admin.form-templates.index') }}">Form Templates</a>
                <a href="{{ route('admin.workflow-templates.index') }}">Workflows</a>
                <a href="{{ route('admin.audit-logs.index') }}">Audit Logs</a>
            @endif

            @if(auth()->user()->hasRole(['employee', 'admin']))
                <div class="text-white-50 small mt-3 mb-1">Employee</div>
                <a href="{{ route('employee.requests.index') }}">My Requests</a>
                <a href="{{ route('employee.requests.select-template') }}">Create Request</a>
            @endif

            @if(auth()->user()->hasRole(['manager', 'hr', 'director', 'admin']))
                <div class="text-white-50 small mt-3 mb-1">Approval</div>
                <a href="{{ route('manager.approvals.index') }}">Pending Approvals</a>
            @endif

            <form action="{{ route('logout') }}" method="POST" class="mt-4">
                @csrf
                <button class="btn btn-outline-light btn-sm w-100">Logout</button>
            </form>
        </aside>
        <main class="col-md-9 col-lg-10 p-4">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>
@else
    @yield('content')
@endauth
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
