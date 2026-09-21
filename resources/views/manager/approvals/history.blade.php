@extends('layouts.app')

@section('page_title', __('menu.approval_history'))
@section('page_eyebrow', __('ui.approval_history_eyebrow'))

@section('content')
<x-erp.page-header :title="__('menu.approval_history')" :eyebrow="__('ui.approval')" :description="__('ui.approval_history_description')" />

<x-erp.approval-filters
    :route="route('manager.approvals.history')"
    :filters="$filters"
    :form-templates="$formTemplates"
    :history="true"
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
            <th>{{ __('ui.your_action') }}</th>
            <th>{{ __('ui.processed_at') }}</th>
            <th width="150">{{ __('ui.action') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($histories as $history)
            @php($workflowRequest = $history->workflowRequest)
            <tr>
                <td class="text-muted fw-semibold">{{ $histories->firstItem() + $loop->index }}</td>
                <td><span class="erp-record-code">{{ $workflowRequest?->request_code ?? '-' }}</span></td>
                <td>
                    <div class="erp-record-primary">{{ $workflowRequest?->formTemplate?->name ?? '-' }}</div>
                    @if($workflowRequest?->formTemplate)
                        <div class="erp-record-secondary">v{{ $workflowRequest->formTemplate->version }}</div>
                    @endif
                </td>
                <td>
                    <div class="erp-record-primary">{{ $workflowRequest?->creator?->name ?? '-' }}</div>
                    @if($workflowRequest?->creator?->email)
                        <div class="erp-record-secondary">{{ $workflowRequest->creator->email }}</div>
                    @endif
                </td>
                <td>
                    @if($workflowRequest)
                        @include('partials.status_badge', ['status' => $workflowRequest->status])
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>
                    @if($history->action === \App\Models\ApprovalHistory::ACTION_APPROVE)
                        <span class="badge text-bg-success erp-status-badge"><i class="bi bi-check-circle-fill"></i><span>{{ __('ui.approve') }}</span></span>
                    @elseif($history->action === \App\Models\ApprovalHistory::ACTION_REJECT)
                        <span class="badge text-bg-danger erp-status-badge"><i class="bi bi-x-circle-fill"></i><span>{{ __('ui.reject') }}</span></span>
                    @elseif($history->action === \App\Models\ApprovalHistory::ACTION_RETURN)
                        <span class="badge text-bg-warning erp-status-badge"><i class="bi bi-arrow-counterclockwise"></i><span>{{ __('ui.return') }}</span></span>
                    @endif
                    @if($history->step?->step_name)
                        <div class="erp-record-secondary mt-1">{{ $history->step->step_name }}</div>
                    @endif
                </td>
                <td>{{ ($history->acted_at ?? $history->created_at)->format('d/m/Y H:i') }}</td>
                <td>
                    @if($workflowRequest)
                        <a href="{{ route('manager.approvals.show', $workflowRequest) }}" class="btn btn-sm btn-primary">{{ __('ui.view_detail') }}</a>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
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

    {{ $histories->links() }}
</div>
@endsection
