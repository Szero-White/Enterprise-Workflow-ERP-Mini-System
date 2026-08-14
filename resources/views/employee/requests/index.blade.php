@extends('layouts.app')

@section('page_title', __('menu.my_requests'))
@section('page_eyebrow', __('ui.my_requests_tracking'))

@section('content')
<x-erp.page-header :title="__('menu.my_requests')" :eyebrow="__('ui.my_requests_eyebrow')" :description="__('ui.my_requests_description')">
    <x-slot:actions>
        <a href="{{ route('employee.requests.select-template') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i>{{ __('ui.create_request') }}</a>
    </x-slot:actions>
</x-erp.page-header>

<div class="content-card p-3 mb-3">
    <form method="GET" class="row g-2">
        <div class="col-md-3">
            <input name="keyword" class="form-control" placeholder="{{ __('ui.request_code') }}" value="{{ request('keyword') }}">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">{{ __('ui.all_statuses') }}</option>
                @foreach(\App\Models\WorkflowRequest::statuses() as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
        </div>
        <div class="col-md-2">
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-outline-primary">{{ __('ui.filter') }}</button>
            <a href="{{ route('employee.requests.index') }}" class="btn btn-light border">{{ __('ui.reset') }}</a>
        </div>
    </form>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">{{ __('ui.no') }}</th>
            <th>{{ __('ui.code') }}</th>
            <th>{{ __('ui.form') }}</th>
            <th>{{ __('ui.status') }}</th>
            <th>{{ __('ui.step') }}</th>
            <th>{{ __('ui.created') }}</th>
            <th width="180">{{ __('ui.action') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($requests as $item)
            <tr>
                <td class="text-muted fw-semibold">{{ $requests->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ $item->request_code }}</td>
                <td>{{ $item->formTemplate?->name ?? '-' }}</td>
                <td>@include('partials.status_badge', ['status' => $item->status])</td>
                <td>{{ $item->currentStep?->step_name ?? '-' }}</td>
                <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('employee.requests.show', $item) }}" class="btn btn-sm btn-outline-primary">{{ __('ui.view') }}</a>
                        @if($item->status === \App\Models\WorkflowRequest::STATUS_RETURNED)
                            <a href="{{ route('employee.requests.edit', $item) }}" class="btn btn-sm btn-warning">{{ __('ui.edit') }}</a>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="text-muted">{{ __('ui.no_requests') }}</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $requests->links() }}
</div>
@endsection
