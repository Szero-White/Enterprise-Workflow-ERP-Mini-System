@extends('layouts.app')
@section('page_title', __('crm.create_title'))
@section('page_eyebrow', __('crm.eyebrow'))
@section('content')
<div class="content-card erp-form-card p-3 p-lg-4"><form method="POST" action="{{ route('crm.customers.store') }}">@csrf @include('crm.customers._form')<div class="d-flex gap-2 mt-4"><button class="btn btn-primary">{{ __('crm.save') }}</button><a href="{{ route('crm.customers.index') }}" class="btn btn-light border">{{ __('crm.cancel') }}</a></div></form></div>
@endsection
