@extends('layouts.app')

@section('page_title', __('menu.audit_logs'))
@section('page_eyebrow', __('menu.admin').' / '.__('menu.audit_logs'))

@section('content')
<x-erp.page-header :title="__('menu.audit_logs')" :eyebrow="__('menu.admin')" :description="__('ui.audit_logs_description')" />

<div class="content-card p-3 mb-3">
    <form method="GET" class="row g-2">
        <div class="col-md-3"><input name="action" class="form-control" placeholder="{{ __('ui.action') }}" value="{{ request('action') }}"></div>
        <div class="col-md-2"><input name="actor_id" class="form-control" placeholder="{{ __('ui.actor_id_placeholder') }}" value="{{ request('actor_id') }}"></div>
        <div class="col-md-2"><input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div>
        <div class="col-md-2"><input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-outline-primary">{{ __('ui.filter') }}</button><a href="{{ route('admin.audit-logs.index') }}" class="btn btn-light border">{{ __('ui.reset') }}</a></div>
    </form>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">{{ __('ui.no') }}</th>
            <th>{{ __('ui.time') }}</th>
            <th>{{ __('ui.actor') }}</th>
            <th>{{ __('ui.action') }}</th>
            <th>Model</th>
            <th>IP</th>
            <th>{{ __('ui.new_value') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td class="text-muted fw-semibold">{{ $logs->firstItem() + $loop->index }}</td>
                <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $log->actor?->name ?? '-' }}</td>
                <td>
                    <div class="fw-semibold">{{ $log->description ?? $log->action }}</div>
                    <code class="small">{{ $log->action }}</code>
                </td>
                <td>{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                <td>{{ $log->ip_address ?? '-' }}</td>
                <td><pre class="small mb-0">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></td>
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

    {{ $logs->links() }}
</div>
@endsection
