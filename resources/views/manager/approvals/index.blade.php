@extends('layouts.app')

@section('page_title', __('menu.pending_approvals'))
@section('page_eyebrow', __('ui.approval_pending_eyebrow'))

@section('content')
<x-erp.page-header :title="__('menu.pending_approvals')" :eyebrow="__('ui.approval')" :description="__('ui.approval_pending_description')" />

<x-erp.approval-filters
    :route="route('manager.approvals.index')"
    :filters="$filters"
    :form-templates="$formTemplates"
/>

<div class="content-card table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">{{ __('ui.no') }}</th>
            <th>{{ __('ui.request_code') }}</th>
            <th>{{ __('ui.request_type') }}</th>
            <th>{{ __('ui.requester') }}</th>
            <th>{{ __('ui.status') }}</th>
            <th>{{ __('ui.current_approval_step') }}</th>
            <th>{{ __('ui.submitted_at') }}</th>
            <th width="150">{{ __('ui.action') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($requests as $item)
            <tr>
                <td class="text-muted fw-semibold">{{ $requests->firstItem() + $loop->index }}</td>
                <td><span class="erp-record-code">{{ $item->request_code }}</span></td>
                <td>
                    <div class="erp-record-primary">{{ $item->formTemplate?->name ?? '-' }}</div>
                    @if($item->formTemplate)
                        <div class="erp-record-secondary">v{{ $item->formTemplate->version }}</div>
                    @endif
                </td>
                <td>
                    <div class="erp-record-primary">{{ $item->creator?->name ?? '-' }}</div>
                    @if($item->creator?->email)
                        <div class="erp-record-secondary">{{ $item->creator->email }}</div>
                    @endif
                </td>
                <td>@include('partials.status_badge', ['status' => $item->status])</td>
                <td>{{ $item->currentStep?->step_name ?? '-' }}</td>
                <td>{{ ($item->submitted_at ?? $item->created_at)->format('d/m/Y H:i') }}</td>
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
