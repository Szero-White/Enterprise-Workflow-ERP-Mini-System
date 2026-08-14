@extends('layouts.app')

@section('page_title', __('menu.pending_approvals'))
@section('page_eyebrow', __('ui.approval_pending_eyebrow'))

@section('content')
<x-erp.page-header :title="__('menu.pending_approvals')" :eyebrow="__('ui.approval')" :description="__('ui.approval_pending_description')" />

<div class="content-card p-3 mb-3">
    <form method="GET" class="row g-2">
        <div class="col-md-3"><input name="keyword" class="form-control" placeholder="{{ __('ui.request_code') }}" value="{{ request('keyword') }}"></div>
        <div class="col-md-2"><input name="creator_id" class="form-control" placeholder="{{ __('ui.creator_id') }}" value="{{ request('creator_id') }}"></div>
        <div class="col-md-2"><input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div>
        <div class="col-md-2"><input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-outline-primary">{{ __('ui.filter') }}</button><a href="{{ route('manager.approvals.index') }}" class="btn btn-light border">{{ __('ui.reset') }}</a></div>
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
            <th>{{ __('ui.step') }}</th>
            <th>{{ __('ui.created') }}</th>
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
                <td>{{ $item->currentStep?->step_name ?? '-' }}</td>
                <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                <td><a href="{{ route('manager.approvals.show', $item) }}" class="btn btn-sm btn-primary">{{ __('ui.review') }}</a></td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="text-muted">{{ __('messages.no_pending_approvals') }}</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $requests->links() }}
</div>
@endsection
