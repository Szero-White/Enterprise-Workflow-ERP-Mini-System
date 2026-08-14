@extends('layouts.app')

@section('page_title', __('procurement.supplier.edit_title'))
@section('page_eyebrow', __('procurement.eyebrow'))

@section('content')
    <x-erp.form-shell
        :title="__('procurement.supplier.edit_title')"
        :eyebrow="__('procurement.eyebrow')"
        :description="$supplier->name"
        :action="route('procurement.suppliers.update', $supplier)"
        method="PUT"
        :submit-label="__('procurement.supplier.save')"
        :cancel-url="route('procurement.suppliers.index')"
    >
        @include('procurement.suppliers._form', ['supplier' => $supplier])
    </x-erp.form-shell>
@endsection
