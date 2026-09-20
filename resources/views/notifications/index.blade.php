@extends('layouts.app')

@section('page_title', __('menu.notifications'))
@section('page_eyebrow', __('ui.account'))

@section('content')
<x-erp.page-header :title="__('menu.notifications')" :eyebrow="__('ui.account')" :description="__('ui.notifications_description')">
    <x-slot:actions>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button class="btn btn-light border">
                <i class="bi bi-check2-all"></i>
                {{ __('ui.mark_all_as_read') }}
            </button>
        </form>
    </x-slot:actions>
</x-erp.page-header>

<div class="content-card p-0 overflow-hidden erp-notifications-list">
    @forelse($notifications as $notification)
        @if(! $notification->read_at)
            <form method="POST" action="{{ route('notifications.read', $notification) }}" class="erp-notification-form">
                @csrf
        @endif

        <article class="erp-notification-item {{ $notification->read_at ? 'is-read' : 'is-unread' }}">
            @if(! $notification->read_at)
                <button
                    type="submit"
                    class="erp-notification-item__hit-area"
                    title="{{ __('ui.mark_as_read') }}"
                    aria-label="{{ __('ui.mark_as_read') }}"
                ></button>
            @endif

            <div class="erp-notification-item__main">
                <div class="erp-notification-item__meta-row">
                    @if(! $notification->read_at)
                        <span class="erp-notification-item__unread-dot" aria-hidden="true"></span>
                        <span class="badge text-bg-primary rounded-pill">{{ __('ui.new') }}</span>
                    @endif
                    <span class="badge text-bg-light border rounded-pill">
                        {{ trans()->has('ui.notification_type.'.$notification->type) ? __('ui.notification_type.'.$notification->type) : str_replace('_', ' ', $notification->type) }}
                    </span>
                </div>

                <h3 class="erp-notification-item__title">{{ $notification->title }}</h3>
                <p class="erp-notification-item__message">{{ $notification->message }}</p>

                <div class="erp-notification-item__meta">
                    <span><i class="bi bi-clock"></i>{{ $notification->created_at->format('d/m/Y H:i') }}</span>
                    @if(data_get($notification->data, 'request_code'))
                        <span><i class="bi bi-file-earmark-text"></i>{{ data_get($notification->data, 'request_code') }}</span>
                    @endif
                </div>
            </div>

            <div class="erp-notification-item__actions">
                @if(data_get($notification->data, 'purchase_request_id') && auth()->user()->hasRole(['procurement', 'admin']))
                    <a
                        href="{{ route('procurement.purchase-requests.show', data_get($notification->data, 'purchase_request_id')) }}"
                        class="erp-icon-action"
                        title="{{ __('ui.open_related_request') }}"
                        aria-label="{{ __('ui.open_related_request') }}"
                    >
                        <i class="bi bi-box-arrow-up-right"></i>
                        <span class="visually-hidden">{{ __('ui.open_related_request') }}</span>
                    </a>
                @endif

                @if(data_get($notification->data, 'action') === 'review_ready_assets' && auth()->user()->hasRole(['asset_manager', 'admin']))
                    <a
                        href="{{ route('assets.index', ['status' => \App\Enums\AssetStatus::Available->value]) }}"
                        class="erp-icon-action"
                        title="{{ __('ui.open_ready_assets') }}"
                        aria-label="{{ __('ui.open_ready_assets') }}"
                    >
                        <i class="bi bi-box-arrow-up-right"></i>
                        <span class="visually-hidden">{{ __('ui.open_ready_assets') }}</span>
                    </a>
                @endif

                @if($notification->read_at)
                    <form method="POST" action="{{ route('notifications.unread', $notification) }}">
                        @csrf
                        <button
                            type="submit"
                            class="erp-icon-action"
                            title="{{ __('ui.mark_as_unread') }}"
                            aria-label="{{ __('ui.mark_as_unread') }}"
                        >
                            <i class="bi bi-envelope"></i>
                            <span class="visually-hidden">{{ __('ui.mark_as_unread') }}</span>
                        </button>
                    </form>
                @else
                    <button
                        type="submit"
                        class="erp-icon-action erp-icon-action--primary"
                        title="{{ __('ui.mark_as_read') }}"
                        aria-label="{{ __('ui.mark_as_read') }}"
                    >
                        <i class="bi bi-envelope-open"></i>
                        <span class="visually-hidden">{{ __('ui.mark_as_read') }}</span>
                    </button>
                @endif
            </div>
        </article>

        @if(! $notification->read_at)
            </form>
        @endif
    @empty
        <div class="p-4 text-muted">{{ __('ui.no_notifications') }}</div>
    @endforelse
</div>

<div class="mt-3">
    {{ $notifications->links() }}
</div>
@endsection
