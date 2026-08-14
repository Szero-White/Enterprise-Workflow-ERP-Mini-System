@extends('layouts.app')

@section('page_title', __('auth.login_title'))

@section('content')
<div class="erp-login-shell">
    <div class="row g-0 align-items-stretch">
        <div class="col-xl-7">
            <section class="erp-login-brand-panel h-100">
                <div class="erp-login-brand-panel__content">
                    <div class="erp-login-logo">
                        <div class="erp-login-logo__mark"><i class="bi bi-layers-fill"></i></div>
                        <div>
                            <div class="erp-login-logo__name">{{ __('layout.brand_name') }}</div>
                            <div class="erp-login-logo__meta">{{ __('auth.brand_tagline') }}</div>
                        </div>
                    </div>

                    <div class="erp-login-kicker">{{ __('auth.kicker') }}</div>
                    <h1 class="erp-login-title">{{ __('auth.hero_title') }}</h1>
                    <p class="erp-login-description">{{ __('auth.hero_description') }}</p>
                </div>

                <div class="erp-login-feature-grid">
                    <article class="erp-login-feature">
                        <i class="bi bi-receipt-cutoff erp-login-feature__icon"></i>
                        <div class="erp-login-feature__title">{{ __('auth.feature_sales') }}</div>
                        <div class="erp-login-feature__text">{{ __('auth.feature_sales_text') }}</div>
                    </article>
                    <article class="erp-login-feature">
                        <i class="bi bi-boxes erp-login-feature__icon"></i>
                        <div class="erp-login-feature__title">{{ __('auth.feature_inventory') }}</div>
                        <div class="erp-login-feature__text">{{ __('auth.feature_inventory_text') }}</div>
                    </article>
                    <article class="erp-login-feature">
                        <i class="bi bi-bezier2 erp-login-feature__icon"></i>
                        <div class="erp-login-feature__title">{{ __('auth.feature_workflow') }}</div>
                        <div class="erp-login-feature__text">{{ __('auth.feature_workflow_text') }}</div>
                    </article>
                </div>
            </section>
        </div>

        <div class="col-xl-5">
            <section class="erp-login-form-panel h-100">
                <div class="erp-login-form">
                    <div class="erp-login-form__eyebrow">{{ __('auth.secure_access') }}</div>
                    <h2 class="erp-login-form__title">{{ __('auth.login_title') }}</h2>
                    <p class="erp-login-form__subtitle">{{ __('auth.login_subtitle') }}</p>

                    <div class="erp-demo-credential">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <div class="erp-demo-credential__label">{{ __('auth.demo_account') }}</div>
                                <div class="erp-demo-credential__value">admin@example.com · password</div>
                            </div>
                            <span class="badge rounded-pill text-bg-primary-subtle text-primary-emphasis px-3 py-2">{{ __('ui.roles.admin') }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('login.post') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="email">{{ __('ui.email') }}</label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                class="form-control form-control-lg @error('email') is-invalid @enderror"
                                value="{{ old('email', 'admin@example.com') }}"
                                autocomplete="email"
                                required
                                autofocus
                            >
                            @include('partials.form_error', ['field' => 'email'])
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">{{ __('ui.password') }}</label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                class="form-control form-control-lg @error('password') is-invalid @enderror"
                                value="password"
                                autocomplete="current-password"
                                required
                            >
                            @include('partials.form_error', ['field' => 'password'])
                        </div>

                        <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
                                <label class="form-check-label" for="remember">{{ __('auth.remember') }}</label>
                            </div>
                            <span class="small text-muted">{{ __('auth.local_demo') }}</span>
                        </div>

                        <button class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-arrow-right-circle"></i>
                            {{ __('auth.login_button') }}
                        </button>
                    </form>

                    <div class="erp-login-footer">{{ __('auth.footer') }}</div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
