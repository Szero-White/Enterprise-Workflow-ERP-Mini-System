@extends('layouts.app')

@section('page_title', __('menu.notifications'))
@section('page_eyebrow', 'Tài khoản')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">{{ __('menu.notifications') }}</h2>
        <p class="text-muted mb-0">Theo dõi cập nhật đơn và các nhiệm vụ duyệt được giao cho bạn.</p>
    </div>
    <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button class="btn btn-outline-primary rounded-3">
            <i class="bi bi-check2-all me-1"></i> {{ __('ui.mark_all_as_read') }}
        </button>
    </form>
</div>

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
