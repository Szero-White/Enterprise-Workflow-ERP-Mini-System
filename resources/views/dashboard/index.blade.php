@extends('layouts.app')

@section('content')
<h2 class="mb-4">Dashboard</h2>
<div class="row g-3 mb-4">
    @foreach($stats as $label => $value)
        <div class="col-md-4 col-lg-2">
            <div class="content-card p-3">
                <div class="text-muted small text-uppercase">{{ str_replace('_', ' ', $label) }}</div>
                <div class="fs-3 fw-bold">{{ $value }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="content-card p-3">
    <h5>Latest Requests</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
            <tr>
                <th>Code</th>
                <th>Form</th>
                <th>Creator</th>
                <th>Status</th>
                <th>Current Step</th>
                <th>Created</th>
            </tr>
            </thead>
            <tbody>
            @forelse($latestRequests as $item)
                <tr>
                    <td>{{ $item->request_code }}</td>
                    <td>{{ $item->formTemplate?->name }}</td>
                    <td>{{ $item->creator?->name }}</td>
                    <td><span class="badge bg-secondary badge-status">{{ $item->status }}</span></td>
                    <td>{{ $item->currentStep?->step_name ?? '-' }}</td>
                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Chưa có đơn.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
