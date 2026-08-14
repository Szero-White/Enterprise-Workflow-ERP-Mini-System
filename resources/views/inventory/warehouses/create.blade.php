@extends('layouts.app')
@section('page_title', __('inventory.warehouse.create_title'))
@section('page_eyebrow', __('inventory.eyebrow'))
@section('content')
<div class="content-card erp-form-card p-3 p-lg-4"><form method="POST" action="{{ route('inventory.warehouses.store') }}">@csrf @include('inventory.warehouses._form')<div class="d-flex gap-2 mt-4"><button class="btn btn-primary">{{ __('inventory.warehouse.save') }}</button><a href="{{ route('inventory.warehouses.index') }}" class="btn btn-light border">{{ __('inventory.warehouse.cancel') }}</a></div></form></div>
@endsection
