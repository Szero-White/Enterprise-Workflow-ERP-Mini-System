@extends('layouts.app')
@section('page_title', __('crm.index_title'))
@section('page_eyebrow', __('crm.eyebrow'))
@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
    <form method="GET" class="erp-filter-bar"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('crm.search_placeholder') }}"><button class="btn btn-outline-secondary">{{ __('crm.search') }}</button>@if(request()->filled('q'))<a href="{{ route('crm.customers.index') }}" class="btn btn-light border">{{ __('crm.clear_filter') }}</a>@endif</form>
    <a href="{{ route('crm.customers.create') }}" class="btn btn-primary"><i class="bi bi-person-plus-fill me-2"></i>{{ __('crm.add') }}</a>
</div>
<div class="content-card overflow-hidden"><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>{{ __('crm.code_short') }}</th><th>{{ __('crm.customer') }}</th><th>{{ __('crm.contact') }}</th><th>{{ __('crm.company') }}</th><th>{{ __('crm.orders') }}</th><th>{{ __('crm.status') }}</th><th class="text-end">{{ __('crm.actions') }}</th></tr></thead><tbody>
@forelse($customers as $customer)<tr>
<td class="fw-semibold">{{ $customer->code }}</td><td><div class="fw-semibold">{{ $customer->name }}</div><div class="small text-muted text-truncate" style="max-width:280px">{{ $customer->address }}</div></td><td><div>{{ $customer->phone ?: '-' }}</div><div class="small text-muted">{{ $customer->email ?: '-' }}</div></td><td>{{ $customer->company_name ?: '-' }}</td><td>{{ $customer->sales_orders_count }}</td><td><span class="badge rounded-pill {{ $customer->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $customer->is_active ? __('crm.active') : __('crm.inactive') }}</span></td>
<td class="text-end"><a href="{{ route('crm.customers.edit',$customer) }}" class="btn btn-sm btn-outline-primary">{{ __('crm.edit') }}</a><form method="POST" action="{{ route('crm.customers.destroy',$customer) }}" class="d-inline" onsubmit="return confirm(@js(__('crm.confirm_delete')))">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">{{ __('crm.delete') }}</button></form></td>
</tr>@empty<tr><td colspan="7"><div class="erp-empty"><i class="bi bi-people"></i>{{ __('crm.empty') }}</div></td></tr>@endforelse
</tbody></table></div>@if($customers->hasPages())<div class="p-3 border-top">{{ $customers->links() }}</div>@endif</div>
@endsection
