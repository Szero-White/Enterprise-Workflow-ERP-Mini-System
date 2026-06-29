@extends('layouts.app')

@section('page_title', 'Pending Approvals')
@section('page_eyebrow', 'Xu ly cac don dang cho')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">Pending Approvals</h2>
        <p class="text-muted mb-0">Requests currently waiting for your action.</p>
    </div>
</div>

<div class="content-card p-3 mb-3">
    <form method="GET" class="row g-2">
        <div class="col-md-3"><input name="keyword" class="form-control" placeholder="Request code" value="{{ request('keyword') }}"></div>
        <div class="col-md-2"><input name="creator_id" class="form-control" placeholder="Creator ID" value="{{ request('creator_id') }}"></div>
        <div class="col-md-2"><input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div>
        <div class="col-md-2"><input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-outline-primary">Filter</button><a href="{{ route('manager.approvals.index') }}" class="btn btn-light border">Reset</a></div>
    </form>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">No.</th>
            <th>Code</th>
            <th>Form</th>
            <th>Creator</th>
            <th>Status</th>
            <th>Step</th>
            <th>Created</th>
            <th width="150">Action</th>
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
                <td><a href="{{ route('manager.approvals.show', $item) }}" class="btn btn-sm btn-primary">Review</a></td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="text-muted">No requests pending approval.</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $requests->links() }}
</div>
@endsection
