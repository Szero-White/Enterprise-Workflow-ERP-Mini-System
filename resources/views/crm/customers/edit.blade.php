@extends('layouts.app')
@section('page_title', __('crm.edit_title'))
@section('page_eyebrow', __('crm.eyebrow'))
@section('content')
<div class="content-card erp-form-card p-3 p-lg-4"><form method="POST" action="{{ route('crm.customers.update',$customer) }}">@csrf @method('PUT') @include('crm.customers._form')<div class="d-flex gap-2 mt-4"><button class="btn btn-primary">{{ __('crm.save_changes') }}</button><a href="{{ route('crm.customers.index') }}" class="btn btn-light border">{{ __('crm.back') }}</a></div></form></div>
@endsection
