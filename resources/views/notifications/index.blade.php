@extends('layouts.app')

@section('page_title', __('menu.notifications'))
@section('page_eyebrow', __('ui.account'))

@section('content')
<x-erp.page-header :title="__('menu.notifications')" :eyebrow="__('ui.account')" :description="__('ui.notifications_description')">
    <x-slot:actions>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button class="btn btn-light border"><i class="bi bi-check2-all"></i>{{ __('ui.mark_all_as_read') }}</button>
        </form>
    </x-slot:actions>
</x-erp.page-header>

<div class="content-card p-0 overflow-hidden">
    @forelse($notifications as $notification)
        <div class="p-3 p-lg-4 border-bottom {{ $notification->read_at ? '' : 'bg-primary-subtle' }}">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        @if(! $notification->read_at)
                            <span class="badge text-bg-primary rounded-pill">{{ __('ui.new') }}</span>
                        @endif
                        <span class="badge text-bg-light border rounded-pill">
                            {{ trans()->has('ui.notification_type.'.$notification->type) ? __('ui.notification_type.'.$notification->type) : str_replace('_', ' ', $notification->type) }}
                        </span>
                    </div>
                    <h3 class="h6 mb-1">{{ $notification->title }}</h3>
                    <p class="mb-2 text-muted">{{ $notification->message }}</p>
                    <div class="small text-muted">
                        {{ $notification->created_at->format('d/m/Y H:i') }}
                        @if(data_get($notification->data, 'request_code'))
                            &middot; {{ __('ui.request') }} {{ data_get($notification->data, 'request_code') }}
                        @endif
                    </div>
                </div>
                @if(! $notification->read_at)
                    <form method="POST" action="{{ route('notifications.read', $notification) }}" class="align-self-lg-center">
                        @csrf
                        <button class="btn btn-sm btn-primary rounded-3">{{ __('ui.mark_as_read') }}</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="p-4 text-muted">{{ __('ui.no_notifications') }}</div>
    @endforelse
</div>

<div class="mt-3">
    {{ $notifications->links() }}
</div>
@endsection
