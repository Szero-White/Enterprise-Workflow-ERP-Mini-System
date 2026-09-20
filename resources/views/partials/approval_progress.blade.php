@php
    $approvalHistories = $workflowRequest->histories
        ->sortBy(fn ($history) => ($history->acted_at ?? $history->created_at)?->timestamp ?? 0)
        ->values();
    $approvalSteps = $workflowRequest->workflowTemplate?->steps
        ?->sortBy('step_order')
        ->values() ?? collect();
    $approvedByStep = $approvalHistories
        ->where('action', 'approve')
        ->keyBy('workflow_step_id');
    $latestHistory = $approvalHistories->last();
    $latestFeedback = $approvalHistories
        ->reverse()
        ->first(fn ($history) => in_array($history->action, ['return', 'reject'], true) && filled($history->comment));
    $isPending = $workflowRequest->status === \App\Models\WorkflowRequest::STATUS_PENDING;
    $isApproved = $workflowRequest->status === \App\Models\WorkflowRequest::STATUS_APPROVED;
    $isReturned = $workflowRequest->status === \App\Models\WorkflowRequest::STATUS_RETURNED;
    $isRejected = $workflowRequest->status === \App\Models\WorkflowRequest::STATUS_REJECTED;
    $isStockFulfillment = isset($purchaseRequest)
        && $purchaseRequest->fulfillment_route === \App\Enums\PurchaseRequestFulfillmentRoute::Stock;
@endphp

<div class="content-card p-3 p-lg-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 align-items-sm-start mb-3">
        <div>
            <h5 class="mb-1">{{ __('ui.approval_progress') }}</h5>
            <div class="text-muted small">{{ __('ui.approval_progress_description') }}</div>
        </div>
        @include('partials.status_badge', ['status' => $workflowRequest->status])
    </div>

    @if($isPending && $workflowRequest->currentStep)
        <div class="rounded-3 border bg-light p-3 mb-3">
            <div class="small text-muted mb-1">{{ __('ui.current_approval_step') }}</div>
            <div class="fw-semibold">{{ $workflowRequest->currentStep->step_name }}</div>
            <div class="small text-muted mt-1">
                {{ __('ui.waiting_for') }}: {{ $workflowRequest->currentStep->approverLabel() }}
            </div>
        </div>
    @elseif($isApproved)
        <div class="alert alert-success mb-3">
            <div class="fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>{{ __('ui.approval_completed') }}</div>
            <div class="small mt-1">
                {{ $isStockFulfillment ? __('ui.approval_completed_stock_description') : __('ui.approval_completed_description') }}
            </div>
        </div>
    @endif

    @if(($isReturned || $isRejected) && $latestFeedback)
        <div class="alert {{ $isRejected ? 'alert-danger' : 'alert-warning' }} mb-3">
            <div class="fw-semibold mb-1">
                <i class="bi {{ $isRejected ? 'bi-x-circle-fill' : 'bi-arrow-counterclockwise' }} me-1"></i>
                {{ $isRejected ? __('ui.rejection_reason') : __('ui.return_reason') }}
            </div>
            <div class="small mb-2">
                {{ __('ui.feedback_by') }}: <strong>{{ $latestFeedback->actor?->name ?? '-' }}</strong>
                @if($latestFeedback->step)
                    &middot; {{ $latestFeedback->step->step_name }}
                @endif
                &middot; {{ ($latestFeedback->acted_at ?? $latestFeedback->created_at)?->format('d/m/Y H:i') }}
            </div>
            <div>{{ $latestFeedback->comment }}</div>
        </div>
    @endif

    @if($approvalSteps->isNotEmpty())
        <div class="d-flex flex-column gap-2 mb-3">
            @foreach($approvalSteps as $step)
                @php
                    $approval = $approvedByStep->get($step->id);
                    $isCurrentStep = $isPending && (int) $workflowRequest->current_step_id === (int) $step->id;
                    $isFeedbackStep = $latestFeedback && (int) $latestFeedback->workflow_step_id === (int) $step->id;
                    $stepState = $approval
                        ? 'approved'
                        : ($isCurrentStep
                            ? 'pending'
                            : (($isReturned || $isRejected) && $isFeedbackStep
                                ? $workflowRequest->status
                                : ($isApproved && $isStockFulfillment ? 'skipped_stock' : 'waiting')));
                    $stepMeta = match ($stepState) {
                        'approved' => ['icon' => 'bi-check-circle-fill', 'class' => 'text-success', 'label' => __('ui.approved')],
                        'pending' => ['icon' => 'bi-hourglass-split', 'class' => 'text-warning', 'label' => __('ui.approval_waiting')],
                        'returned' => ['icon' => 'bi-arrow-counterclockwise', 'class' => 'text-info', 'label' => __('ui.returned')],
                        'rejected' => ['icon' => 'bi-x-circle-fill', 'class' => 'text-danger', 'label' => __('ui.rejected')],
                        'skipped_stock' => ['icon' => 'bi-skip-forward-fill', 'class' => 'text-muted', 'label' => __('ui.approval_skipped_stock')],
                        default => ['icon' => 'bi-circle', 'class' => 'text-muted', 'label' => __('ui.approval_not_started')],
                    };
                @endphp

                <div class="d-flex gap-3 align-items-start rounded-3 border p-3">
                    <div class="{{ $stepMeta['class'] }} fs-5 lh-1 mt-1">
                        <i class="bi {{ $stepMeta['icon'] }}"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex flex-wrap justify-content-between gap-2">
                            <div class="fw-semibold">{{ $step->step_name }}</div>
                            <span class="small {{ $stepMeta['class'] }}">{{ $stepMeta['label'] }}</span>
                        </div>
                        <div class="small text-muted mt-1">
                            {{ $step->approverLabel() }}
                            @if($approval)
                                &middot; {{ $approval->actor?->name ?? '-' }}
                                &middot; {{ ($approval->acted_at ?? $approval->created_at)?->format('d/m/Y H:i') }}
                            @endif
                        </div>
                        @if($approval?->comment)
                            <div class="small mt-2">{{ $approval->comment }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <details class="border-top pt-3" @if($isReturned || $isRejected) open @endif>
        <summary class="fw-semibold">{{ __('ui.approval_activity') }}</summary>
        <div class="mt-2">
            @forelse($approvalHistories->reverse() as $history)
                <div class="border-bottom py-3">
                    <div class="fw-semibold">
                        {{ trans()->has('ui.action_labels.'.$history->action) ? __('ui.action_labels.'.$history->action) : strtoupper($history->action) }}
                    </div>
                    <div class="text-muted small">
                        {{ $history->actor?->name ?? '-' }}
                        @if($history->step)
                            &middot; {{ $history->step->step_name }}
                        @endif
                        &middot; {{ ($history->acted_at ?? $history->created_at)?->format('d/m/Y H:i') }}
                    </div>
                    @if($history->comment)
                        <div class="mt-2">{{ $history->comment }}</div>
                    @endif
                </div>
            @empty
                <div class="text-muted py-3">{{ __('ui.no_approval_history') }}</div>
            @endforelse
        </div>
    </details>

    @if($latestHistory)
        <div class="small text-muted mt-3">
            {{ __('ui.last_activity') }}: {{ ($latestHistory->acted_at ?? $latestHistory->created_at)?->format('d/m/Y H:i') }}
        </div>
    @endif
</div>
