@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h2>My Requests</h2><a href="{{ route('employee.requests.select-template') }}" class="btn btn-primary">Create Request</a></div>
<div class="content-card p-3 mb-3">
<form method="GET" class="row g-2">
    <div class="col-md-3"><input name="keyword" class="form-control" placeholder="Request code" value="{{ request('keyword') }}"></div>
    <div class="col-md-2"><select name="status" class="form-select"><option value="">All status</option>@foreach(['pending','returned','approved','rejected'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ $status }}</option>@endforeach</select></div>
    <div class="col-md-2"><input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div>
    <div class="col-md-2"><input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div>
    <div class="col-md-3"><button class="btn btn-outline-primary">Filter</button><a href="{{ route('employee.requests.index') }}" class="btn btn-light">Reset</a></div>
</form>
</div>
<div class="content-card p-3 table-responsive"><table class="table align-middle">
<thead><tr><th>Code</th><th>Form</th><th>Status</th><th>Current Step</th><th>Created</th><th>Action</th></tr></thead><tbody>
@forelse($requests as $item)
<tr><td>{{ $item->request_code }}</td><td>{{ $item->formTemplate?->name }}</td><td><span class="badge bg-secondary badge-status">{{ $item->status }}</span></td><td>{{ $item->currentStep?->step_name ?? '-' }}</td><td>{{ $item->created_at->format('d/m/Y H:i') }}</td><td>
<a href="{{ route('employee.requests.show', $item) }}" class="btn btn-sm btn-info">View</a>
@if($item->status === 'returned')<a href="{{ route('employee.requests.edit', $item) }}" class="btn btn-sm btn-warning">Edit</a>@endif
</td></tr>
@empty<tr><td colspan="6" class="text-center text-muted">Chưa có đơn.</td></tr>@endforelse
</tbody></table>{{ $requests->links() }}</div>
@endsection
