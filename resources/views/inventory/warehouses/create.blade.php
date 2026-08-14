@extends('layouts.app')
@section('page_title', __('inventory.warehouse.create_title'))
@section('page_eyebrow', __('inventory.eyebrow'))
@section('content')
<x-erp.form-shell
    :title="__('inventory.warehouse.create_title')"
    :eyebrow="__('inventory.eyebrow')"
    :description="__('inventory.warehouse.index_description')"
    :action="route('inventory.warehouses.store')"
    :submit-label="__('inventory.warehouse.save')"
    :cancel-url="route('inventory.warehouses.index')"
    :aside-hint="__('inventory.warehouse.form_hint')"
>
    @include('inventory.warehouses._form')
</x-erp.form-shell>
@endsection
