@extends('layouts.app')

@section('page_title', __('menu.approval_history'))
@section('page_eyebrow', 'Lịch sử duyệt')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">{{ __('menu.approval_history') }}</h2>
        <p class="text-muted mb-0">Lịch sử các đơn bạn đã thực hiện hành động (duệt, từ chối, trả về).</p>
    </div>
</div>

<div class="content-card p-3 mb-3">
    <form method="GET" class="row g-2">
        <div class="col-md-3"><input name="keyword" class="form-control" placeholder="{{ __('ui.request_code') }}" value="{{ request('keyword') }}"></div>
        <div class="col-md-2"><input name="creator_id" class="form-control" placeholder="{{ __('ui.creator_id') }}" value="{{ request('creator_id') }}"></div>
        <div class="col-md-2"><input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div>
        <div class="col-md-2"><input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div>
        <div class="col-md-2">
            <select name="action" class="form-select">
                <option value="">{{ __('ui.all_actions') }}</option>
                <option value="approve" {{ request('action') == 'approve' ? 'selected' : '' }}>Duyệt</option>
                <option value="reject" {{ request('action') == 'reject' ? 'selected' : '' }}>Từ chối</option>
                <option value="return" {{ request('action') == 'return' ? 'selected' : '' }}>Trả về</option>
            </select>
        </div>
        <div class="col-md-1 d-flex gap-2"><button class="btn btn-outline-primary">{{ __('ui.filter') }}</button><a href="{{ route('manager.approvals.history') }}" class="btn btn-light border">{{ __('ui.reset') }}</a></div>
    </form>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">{{ __('ui.no') }}</th>
            <th>{{ __('ui.code') }}</th>
            <th>{{ __('ui.form') }}</th>
            <th>{{ __('ui.creator') }}</th>
            <th>{{ __('ui.status') }}</th>
            <th>{{ __('ui.your_action') }}</th>
            <th>{{ __('ui.action_date') }}</th>
            <th width="150">{{ __('ui.action') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($requests as $item)
            <tr>
                <td class="text-muted fw-semibold">{{ $requests->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ $item->request_code }}</td>
                <td>{{ $item->formTemplate?->name ?? '-' }}</td>
                <td>{{ $item->creator?->name ?? '-' }}</td>
                <td>@include('partials.status_badge', ['status' => $item->status])</td>
                <td>
                    @php
                        $userHistory = $item->histories->firstWhere('actor_id', auth()->id());
                    @endphp
                    @if($userHistory)
                        @if($userHistory->action == 'approve')
                            <span class="badge bg-success">Duyệt</span>
                        @elseif($userHistory->action == 'reject')
                            <span class="badge bg-danger">Từ chối</span>
                        @elseif($userHistory->action == 'return')
                            <span class="badge bg-warning">Trả về</span>
                        @endif
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>
                    @php
                        $userHistory = $item->histories->firstWhere('actor_id', auth()->id());
                    @endphp
                    @if($userHistory)
                        {{ $userHistory->acted_at ? $userHistory->acted_at->format('d/m/Y H:i') : $userHistory->created_at->format('d/m/Y H:i') }}
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td><a href="{{ route('manager.approvals.show', $item) }}" class="btn btn-sm btn-primary">Xem chi tiết</a></td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="text-muted">{{ __('messages.no_approval_history') }}</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $requests->links() }}
</div>
@endsection
