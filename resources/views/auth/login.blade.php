@extends('layouts.app')

@section('page_title', __('auth.login_title'))

@section('content')
@php
    $demoAccounts = collect(config('demo.accounts', []));
    $defaultDemoEmail = (string) config('demo.default_email', 'employee@example.com');
    $defaultDemoAccount = $demoAccounts->firstWhere('email', $defaultDemoEmail) ?? $demoAccounts->first();
@endphp
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

                    <div class="erp-login-capabilities" aria-label="{{ __('auth.capabilities') }}">
                        <span class="erp-login-capability"><i class="bi bi-ui-checks-grid"></i>{{ __('auth.feature_requests') }}</span>
                        <span class="erp-login-capability"><i class="bi bi-cart-check"></i>{{ __('auth.feature_inventory') }}</span>
                        <span class="erp-login-capability"><i class="bi bi-laptop"></i>{{ __('auth.feature_workflow') }}</span>
                    </div>
                </div>

                <div>
                    <div class="erp-login-preview" aria-hidden="true">
                        <div class="erp-login-preview__top">
                            <span class="erp-login-preview__label">{{ __('auth.preview_title') }}</span>
                            <span class="erp-login-preview__status">{{ __('auth.preview_status') }}</span>
                        </div>
                        <div class="erp-login-preview__grid">
                            <div class="erp-login-preview__chart">
                                <div class="erp-login-preview__chart-title">{{ __('auth.preview_workflow') }}</div>
                                <div class="erp-login-preview__bars"><span></span><span></span><span></span><span></span><span></span><span></span></div>
                            </div>
                            <div class="erp-login-preview__activity">
                                <div class="erp-login-preview__activity-title">{{ __('auth.preview_activity') }}</div>
                                <div class="erp-login-preview__activity-lines">
                                    <div class="erp-login-preview__activity-line"><span></span></div>
                                    <div class="erp-login-preview__activity-line"><span></span></div>
                                    <div class="erp-login-preview__activity-line"><span></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="erp-login-brand-footer">{{ __('auth.footer') }}</div>
                </div>
            </section>
        </div>

        <div class="col-xl-5">
            <section class="erp-login-form-panel h-100">
                <div class="erp-login-form">
                    <div class="erp-login-form__eyebrow">{{ __('auth.secure_access') }}</div>
                    <h2 class="erp-login-form__title">{{ __('auth.login_title') }}</h2>
                    <p class="erp-login-form__subtitle">{{ __('auth.login_subtitle') }}</p>

                    @if(config('demo.enabled'))
                        <div class="erp-demo-access" data-demo-login>
                            <div class="erp-demo-access__header">
                                <span class="erp-demo-access__badge"><i class="bi bi-play-circle"></i>{{ __('auth.public_demo_title') }}</span>
                                <span class="erp-demo-access__ready"><i class="bi bi-check-circle-fill"></i>{{ __('auth.demo_ready') }}</span>
                            </div>

                            <p class="erp-demo-access__hint">{{ __('auth.public_demo_hint') }}</p>

                            <div class="erp-demo-access__flow">
                                <span>{{ __('auth.demo_flow_label') }}</span>
                                <strong>{{ __('auth.demo_flow') }}</strong>
                            </div>

                            <div class="erp-demo-access__credentials">
                                <div>
                                    <span>{{ __('auth.demo_selected_role') }}</span>
                                    <strong data-demo-selected-role>{{ $defaultDemoAccount['role'] ?? __('auth.demo_account') }}</strong>
                                    <small>{{ $defaultDemoAccount['email'] ?? $defaultDemoEmail }}</small>
                                </div>
                                <div>
                                    <span>{{ __('auth.demo_password') }}</span>
                                    <code>{{ config('demo.password') }}</code>
                                </div>
                            </div>

                            <details class="erp-demo-roles">
                                <summary>{{ __('auth.demo_switch_role') }}</summary>
                                <div class="erp-demo-roles__grid">
                                    @foreach($demoAccounts as $account)
                                        <button
                                            type="button"
                                            class="erp-demo-role {{ $account['email'] === ($defaultDemoAccount['email'] ?? null) ? 'is-active' : '' }}"
                                            data-demo-account
                                            data-demo-email="{{ $account['email'] }}"
                                            data-demo-role="{{ $account['role'] }}"
                                            data-demo-password="{{ config('demo.password') }}"
                                        >
                                            <span>{{ $account['role'] }}</span>
                                            <small>{{ $account['email'] }}</small>
                                        </button>
                                    @endforeach
                                </div>
                            </details>
                        </div>
                    @elseif(app()->environment('local', 'testing'))
                        <div class="erp-demo-credential">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div class="min-w-0">
                                    <div class="erp-demo-credential__label">{{ __('auth.demo_account') }}</div>
                                    <div class="erp-demo-credential__value text-truncate">admin@example.com · password</div>
                                </div>
                                <i class="bi bi-shield-lock text-muted" aria-hidden="true"></i>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.post') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="email">{{ __('ui.email') }}</label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', config('demo.enabled') ? $defaultDemoEmail : (app()->environment('local', 'testing') ? 'admin@example.com' : '')) }}"
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
                                class="form-control @error('password') is-invalid @enderror"
                                value="{{ config('demo.enabled') ? config('demo.password') : '' }}"
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
                            {{ __('auth.login_button') }}
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </form>

                    <div class="erp-login-footer">{{ __('auth.form_footer') }}</div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
