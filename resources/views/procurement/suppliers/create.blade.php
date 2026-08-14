@extends('layouts.app')

@section('page_title', __('procurement.supplier.create_title'))
@section('page_eyebrow', __('procurement.eyebrow'))

@section('content')
    <x-erp.form-shell
        :title="__('procurement.supplier.create_title')"
        :eyebrow="__('procurement.eyebrow')"
        :description="__('procurement.supplier.index_description')"
        :action="route('procurement.suppliers.store')"
        :submit-label="__('procurement.supplier.save')"
        :cancel-url="route('procurement.suppliers.index')"
    >
        @include('procurement.suppliers._form')
    </x-erp.form-shell>
@endsection
