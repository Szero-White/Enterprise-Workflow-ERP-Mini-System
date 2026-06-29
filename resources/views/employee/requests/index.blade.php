@extends('layouts.app')

@section('page_title', 'My Requests')
@section('page_eyebrow', 'Theo doi cac don da tao')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <div>
        <h2 class="h4 mb-1">My Requests</h2>
        <p class="text-muted mb-0">Review status and continue returned requests.</p>
    </div>
    <a href="{{ route('employee.requests.select-template') }}" class="btn btn-primary rounded-3">
        <i class="bi bi-plus-circle me-2"></i>Create Request
    </a>
</div>

<div class="content-card p-3 mb-3">
    <form method="GET" class="row g-2">
        <div class="col-md-3">
            <input name="keyword" class="form-control" placeholder="Request code" value="{{ request('keyword') }}">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All status</option>
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
            <button class="btn btn-outline-primary">Filter</button>
            <a href="{{ route('employee.requests.index') }}" class="btn btn-light border">Reset</a>
        </div>
    </form>
</div>

<div class="content-card p-3 table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th width="70">No.</th>
            <th>Code</th>
            <th>Form</th>
            <th>Status</th>
            <th>Current Step</th>
            <th>Created</th>
            <th width="180">Action</th>
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
                        <a href="{{ route('employee.requests.show', $item) }}" class="btn btn-sm btn-outline-primary">View</a>
                        @if($item->status === \App\Models\WorkflowRequest::STATUS_RETURNED)
                            <a href="{{ route('employee.requests.edit', $item) }}" class="btn btn-sm btn-warning">Edit</a>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="text-muted">No requests found.</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $requests->links() }}
</div>
@endsection
