@extends('layouts.app')

@section('page_title', __('menu.audit_logs'))
@section('page_eyebrow', __('menu.admin').' / '.__('menu.audit_logs'))

@section('content')
<x-erp.page-header :title="__('menu.audit_logs')" :eyebrow="__('menu.admin')" :description="__('ui.audit_logs_description')" />

<div class="content-card p-3 mb-3 erp-audit-filter-card">
    <form method="GET" class="row g-3 align-items-end" aria-label="{{ __('ui.audit_filter_title') }}">
        <div class="col-xl-3 col-md-6">
            <label for="audit-action" class="form-label">{{ __('ui.action') }}</label>
            <select id="audit-action" name="action" class="form-select">
                <option value="">{{ __('ui.all_actions') }}</option>
                @foreach($actions as $action)
                    @php($actionKey = 'ui.audit_actions.'.str_replace('.', '_', $action))
                    <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>
                        {{ trans()->has($actionKey) ? __($actionKey) : ucfirst(str_replace(['.', '_'], ' ', $action)) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-xl-3 col-md-6">
            <label for="audit-actor" class="form-label">{{ __('ui.actor') }}</label>
            <select id="audit-actor" name="actor_id" class="form-select">
                <option value="">{{ __('ui.all_actors') }}</option>
                @foreach($actors as $actor)
                    <option value="{{ $actor->id }}" @selected((string) ($filters['actor_id'] ?? '') === (string) $actor->id)>
                        {{ $actor->name }} · {{ $actor->email }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-xl-2 col-md-6">
            <label for="audit-from-date" class="form-label">{{ __('ui.from_date') }}</label>
            <input id="audit-from-date" type="date" name="from_date" class="form-control" value="{{ $filters['from_date'] ?? '' }}">
        </div>

        <div class="col-xl-2 col-md-6">
            <label for="audit-to-date" class="form-label">{{ __('ui.to_date') }}</label>
            <input id="audit-to-date" type="date" name="to_date" class="form-control" value="{{ $filters['to_date'] ?? '' }}">
        </div>

        <div class="col-xl-2 d-flex gap-2">
            <button class="btn btn-outline-primary flex-fill" type="submit">{{ __('ui.filter') }}</button>
            <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-light border flex-fill">{{ __('ui.reset') }}</a>
        </div>
    </form>

    <div class="erp-audit-filter-card__hint">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <span>{{ __('ui.audit_filter_help') }}</span>
    </div>
</div>

<div class="content-card erp-audit-card">
    <div class="table-responsive erp-audit-table-wrap">
        <table class="table align-middle erp-audit-table">
            <thead class="table-light">
            <tr>
                <th class="erp-audit-col-no">{{ __('ui.no') }}</th>
                <th class="erp-audit-col-time">{{ __('ui.time') }}</th>
                <th class="erp-audit-col-actor">{{ __('ui.actor') }}</th>
                <th class="erp-audit-col-action">{{ __('ui.action') }}</th>
                <th class="erp-audit-col-target">{{ __('ui.audit_target') }}</th>
                <th class="erp-audit-col-ip">IP</th>
                <th class="erp-audit-col-change">{{ __('ui.audit_change_details') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                @php($actionKey = 'ui.audit_actions.'.str_replace('.', '_', $log->action))
                <tr>
                    <td class="text-muted fw-semibold">{{ $logs->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="erp-audit-primary">{{ $log->created_at->format('d/m/Y') }}</div>
                        <div class="erp-audit-secondary">{{ $log->created_at->format('H:i:s') }}</div>
                    </td>
                    <td>
                        <div class="erp-audit-primary">{{ $log->actor?->name ?? __('ui.system_actor') }}</div>
                        @if($log->actor?->email)
                            <div class="erp-audit-secondary text-truncate" title="{{ $log->actor->email }}">{{ $log->actor->email }}</div>
                        @endif
                    </td>
                    <td>
                        <div class="erp-audit-primary">
                            {{ $log->description ?? (trans()->has($actionKey) ? __($actionKey) : ucfirst(str_replace(['.', '_'], ' ', $log->action))) }}
                        </div>
                        <code class="erp-audit-code">{{ $log->action }}</code>
                    </td>
                    <td>
                        @if($log->auditable_type)
                            <div class="erp-audit-primary">{{ class_basename($log->auditable_type) }}</div>
                            <div class="erp-audit-secondary">#{{ $log->auditable_id ?? '-' }}</div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td><span class="erp-audit-ip">{{ $log->ip_address ?? '—' }}</span></td>
                    <td>
                        @if(! empty($log->old_values) || ! empty($log->new_values))
                            <details class="erp-audit-change-details">
                                <summary>{{ __('ui.view_changes') }}</summary>
                                <div class="erp-audit-change-panel">
                                    @if(! empty($log->old_values))
                                        <section>
                                            <div class="erp-audit-change-panel__label">{{ __('ui.previous_value') }}</div>
                                            <pre>{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </section>
                                    @endif

                                    @if(! empty($log->new_values))
                                        <section>
                                            <div class="erp-audit-change-panel__label">{{ __('ui.current_value') }}</div>
                                            <pre>{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </section>
                                    @endif
                                </div>
                            </details>
                        @else
                            <span class="text-muted small">{{ __('ui.no_change_details') }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="text-muted">{{ __('ui.no_audit_logs') }}</div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="erp-audit-pagination">
        {{ $logs->links() }}
    </div>
</div>
@endsection
