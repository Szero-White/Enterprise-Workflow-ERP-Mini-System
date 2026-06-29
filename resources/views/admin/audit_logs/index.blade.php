@extends('layouts.app')

@section('page_title', 'Audit Logs')
@section('page_eyebrow', 'Admin / Audit Logs')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">Audit Logs</h2>
        <p class="text-muted mb-0">Review important system actions and trace changes over time.</p>
    </div>
</div>

<div class="content-card p-3 mb-3">
    <form method="GET" class="row g-2">
        <div class="col-md-3"><input name="action" class="form-control" placeholder="Action" value="{{ request('action') }}"></div>
        <div class="col-md-2"><input name="actor_id" class="form-control" placeholder="Actor ID" value="{{ request('actor_id') }}"></div>
        <div class="col-md-2"><input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div>
        <div class="col-md-2"><input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-outline-primary">Filter</button><a href="{{ route('admin.audit-logs.index') }}" class="btn btn-light border">Reset</a></div>
    </form>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">No.</th>
            <th>Time</th>
            <th>Actor</th>
            <th>Action</th>
            <th>Model</th>
            <th>IP</th>
            <th>New Values</th>
        </tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td class="text-muted fw-semibold">{{ $logs->firstItem() + $loop->index }}</td>
                <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $log->actor?->name ?? '-' }}</td>
                <td><code>{{ $log->action }}</code></td>
                <td>{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                <td>{{ $log->ip_address ?? '-' }}</td>
                <td><pre class="small mb-0">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="text-muted">No audit logs found.</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $logs->links() }}
</div>
@endsection
