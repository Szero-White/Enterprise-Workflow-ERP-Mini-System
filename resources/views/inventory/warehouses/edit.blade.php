@extends('layouts.app')
@section('page_title', __('inventory.warehouse.edit_title'))
@section('page_eyebrow', __('inventory.eyebrow'))
@section('content')
<x-erp.form-shell
    :title="__('inventory.warehouse.edit_title')"
    :eyebrow="__('inventory.eyebrow')"
    :description="$warehouse->code.' · '.$warehouse->name"
    :action="route('inventory.warehouses.update', $warehouse)"
    method="PUT"
    :submit-label="__('inventory.warehouse.save_changes')"
    :cancel-url="route('inventory.warehouses.index')"
    :cancel-label="__('inventory.warehouse.back')"
    :aside-hint="__('inventory.warehouse.form_hint')"
>
    @include('inventory.warehouses._form')
</x-erp.form-shell>
@endsection
