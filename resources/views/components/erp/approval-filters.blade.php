@props([
    'route',
    'filters' => [],
    'formTemplates' => collect(),
    'history' => false,
])

<div class="content-card p-3 mb-3 erp-list-filter-card">
    <form method="GET" action="{{ $route }}" class="row g-3 align-items-end" aria-label="{{ $history ? __('ui.approval_history_filter_title') : __('ui.pending_approval_filter_title') }}">
        <div class="col-xl-2 col-md-6">
            <label for="approval-keyword" class="form-label">{{ __('ui.request_code') }}</label>
            <input
                id="approval-keyword"
                name="keyword"
                class="form-control"
                value="{{ $filters['keyword'] ?? '' }}"
                placeholder="{{ __('ui.request_code_placeholder') }}"
            >
        </div>

        <div class="col-xl-2 col-md-6">
            <label for="approval-requester" class="form-label">{{ __('ui.requester') }}</label>
            <input
                id="approval-requester"
                name="requester"
                class="form-control"
                value="{{ $filters['requester'] ?? '' }}"
                placeholder="{{ __('ui.requester_search_placeholder') }}"
            >
        </div>

        <div class="col-xl-2 col-md-6">
            <label for="approval-form-template" class="form-label">{{ __('ui.request_type') }}</label>
            <select id="approval-form-template" name="form_template_id" class="form-select">
                <option value="">{{ __('ui.all_request_types') }}</option>
                @foreach($formTemplates as $formTemplate)
                    <option value="{{ $formTemplate->id }}" @selected((string) ($filters['form_template_id'] ?? '') === (string) $formTemplate->id)>
                        {{ $formTemplate->displayName() }}
                    </option>
                @endforeach
            </select>
        </div>

        @if($history)
            <div class="col-xl-2 col-md-6">
                <label for="approval-action" class="form-label">{{ __('ui.your_action') }}</label>
                <select id="approval-action" name="action" class="form-select">
                    <option value="">{{ __('ui.all_actions') }}</option>
                    @foreach(\App\Models\ApprovalHistory::DECISION_ACTIONS as $action)
                        <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>
                            {{ __('ui.action_labels.'.$action) }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="{{ $history ? 'col-xl-2' : 'col-xl-3' }} col-md-6">
            <label for="approval-from-date" class="form-label">
                {{ $history ? __('ui.action_from_date') : __('ui.submitted_from_date') }}
            </label>
            <input id="approval-from-date" type="date" name="from_date" class="form-control" value="{{ $filters['from_date'] ?? '' }}">
        </div>

        <div class="{{ $history ? 'col-xl-2' : 'col-xl-3' }} col-md-6">
            <label for="approval-to-date" class="form-label">
                {{ $history ? __('ui.action_to_date') : __('ui.submitted_to_date') }}
            </label>
            <input id="approval-to-date" type="date" name="to_date" class="form-control" value="{{ $filters['to_date'] ?? '' }}">
        </div>

        <div class="col-12 d-flex flex-wrap justify-content-end gap-2 erp-list-filter-card__actions">
            <button class="btn btn-outline-primary" type="submit">
                <i class="bi bi-funnel me-1" aria-hidden="true"></i>{{ __('ui.filter') }}
            </button>
            <a href="{{ $route }}" class="btn btn-light border">
                {{ __('ui.reset') }}
            </a>
        </div>
    </form>

    <div class="erp-list-filter-card__hint">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <span>{{ $history ? __('ui.approval_history_filter_help') : __('ui.pending_approval_filter_help') }}</span>
    </div>
</div>
