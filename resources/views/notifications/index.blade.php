@extends('layouts.app')

@inject('notificationPresenter', 'App\Support\Notifications\NotificationPresenter')

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
        @php
            $display = $notificationPresenter->display($notification);
            $actionUrl = $notificationPresenter->actionUrl($notification, auth()->user());
            $details = collect(data_get($display->data, 'details', []))->filter(fn ($item) => filled(data_get($item, 'value')));
        @endphp

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

                <h3 class="erp-notification-item__title">{{ $display->title }}</h3>
                <p class="erp-notification-item__message">{{ $display->message }}</p>

                @if(data_get($display->data, 'requester_name') || $details->isNotEmpty())
                    <div class="erp-notification-context" aria-label="Thông tin liên quan">
                        @if(data_get($display->data, 'requester_name'))
                            <span class="erp-notification-context__item">
                                <strong>{{ __('ui.notification_details.requester') }}:</strong>
                                {{ data_get($display->data, 'requester_name') }}
                            </span>
                        @endif

                        @foreach($details as $detail)
                            <span class="erp-notification-context__item">
                                <strong>{{ data_get($detail, 'label') }}:</strong>
                                {{ data_get($detail, 'value') }}
                            </span>
                        @endforeach
                    </div>
                @endif

                <div class="erp-notification-item__meta">
                    <span><i class="bi bi-clock"></i>{{ $notification->created_at->format('d/m/Y H:i') }}</span>
                    @if(data_get($notification->data, 'request_code'))
                        <span><i class="bi bi-file-earmark-text"></i>{{ __('ui.request_code') }}: {{ data_get($notification->data, 'request_code') }}</span>
                    @endif
                </div>
            </div>

            <div class="erp-notification-item__actions">
                @if($actionUrl)
                    <a href="{{ $actionUrl }}" class="btn btn-sm btn-outline-primary erp-notification-action-btn">
                        {{ $notificationPresenter->actionLabel($notification) }}
                        <i class="bi bi-arrow-right"></i>
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
