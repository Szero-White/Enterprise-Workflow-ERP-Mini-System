@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="content-card p-4">
                <h3 class="mb-1">Enterprise Workflow</h3>
                <p class="text-muted mb-4">Đăng nhập hệ thống ERP Mini</p>
                @include('partials.flash')
                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', 'admin@example.com') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" value="password" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <button class="btn btn-primary w-100">Login</button>
                </form>
                <div class="small text-muted mt-3">
                    Demo: admin@example.com / password
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
